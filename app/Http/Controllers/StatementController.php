<?php

namespace App\Http\Controllers;

use App\Models\Allocation;
use App\Models\Balance;
use App\Models\Payment;
use App\Models\Statement;
use App\Models\Tenancy;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class StatementController extends Controller
{
    /**
     * Display a listing of statements for a tenant.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // Get date range filters
        $from = $request->get('from', now()->subMonths(11)->format('Y-m'));
        $to = $request->get('to', now()->format('Y-m'));

        if ($user->hasRole('tenant')) {
            // Tenant sees their own statements
            $tenancy = Tenancy::where('tenant_user_id', $user->id)
                ->where('status', 'active')
                ->first();

            if (!$tenancy) {
                return view('statements.index', ['statements' => collect()]);
            }

            $statements = Statement::where('tenancy_id', $tenancy->id)
                ->whereBetween('period_month', [$from, $to])
                ->orderBy('period_month', 'desc')
                ->paginate(12);

            return view('statements.index', compact('statements', 'tenancy'));
        }

        // Manager/Owner sees all statements for their properties
        $statements = Statement::with('tenancy.unit.property')
            ->whereHas('tenancy.unit.property', function ($query) use ($user) {
                $query->where('owner_user_id', $user->id)
                    ->orWhereHas('users', function ($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
            })
            ->whereBetween('period_month', [$from, $to])
            ->orderBy('period_month', 'desc')
            ->paginate(20);

        return view('statements.index', compact('statements'));
    }

    /**
     * Display the specified statement.
     */
    public function show(Request $request, Statement $statement): View
    {
        $user = $request->user();

        // Check access
        if ($user->hasRole('tenant')) {
            if ($statement->tenancy->tenant_user_id !== $user->id) {
                abort(403);
            }
        } else {
            $property = $statement->tenancy->unit->property;
            if ($property->owner_user_id !== $user->id &&
                !$property->users()->where('user_id', $user->id)->exists() &&
                !$user->hasRole('super_admin')) {
                abort(403);
            }
        }

        $statement->load('tenancy.unit.property', 'tenancy.tenant');

        return view('statements.show', compact('statement'));
    }

    /**
     * Generate a statement for a tenancy.
     */
    public function generate(Request $request): RedirectResponse
    {
        $request->validate([
            'tenancy_id' => 'required|exists:tenancies,id',
            'period_month' => 'required|date_format:Y-m',
        ]);

        $tenancy = Tenancy::with('unit.property')->findOrFail($request->tenancy_id);

        // Check permission
        $user = $request->user();
        $property = $tenancy->unit->property;

        if (!$user->hasRole('super_admin') &&
            $property->owner_user_id !== $user->id &&
            !$property->users()->where('user_id', $user->id)->exists()) {
            abort(403);
        }

        $periodMonth = $request->period_month;

        // Check if statement already exists
        $existingStatement = Statement::where('tenancy_id', $tenancy->id)
            ->where('period_month', $periodMonth)
            ->first();

        if ($existingStatement) {
            return redirect()->route('statements.show', $existingStatement)
                ->with('info', 'Statement already exists for this period.');
        }

        // Calculate statement data
        $statementData = $this->calculateStatementData($tenancy, $periodMonth);

        // Create statement
        $statement = Statement::create([
            'tenancy_id' => $tenancy->id,
            'period_month' => $periodMonth,
            'generated_at' => now(),
            'totals_json' => $statementData,
            'status' => 'generated',
        ]);

        return redirect()->route('statements.show', $statement)
            ->with('success', 'Statement generated successfully.');
    }

    /**
     * Calculate statement data for a tenancy and period.
     */
    protected function calculateStatementData(Tenancy $tenancy, string $periodMonth): array
    {
        $period = Carbon::createFromFormat('Y-m', $periodMonth);
        $previousMonth = $period->copy()->subMonth()->format('Y-m');

        // Get previous balance
        $previousBalance = Balance::where('tenancy_id', $tenancy->id)
            ->where('period_month', $previousMonth)
            ->first();

        $openingBalance = 0;
        if ($previousBalance) {
            $openingBalance = ($previousBalance->rent_due - $previousBalance->rent_paid)
                + ($previousBalance->water_due - $previousBalance->water_paid);
        }

        // Get current balance
        $currentBalance = Balance::where('tenancy_id', $tenancy->id)
            ->where('period_month', $periodMonth)
            ->first();

        $rentDue = $currentBalance?->rent_due ?? $tenancy->rent_amount;
        $rentPaid = $currentBalance?->rent_paid ?? 0;
        $waterDue = $currentBalance?->water_due ?? 0;
        $waterPaid = $currentBalance?->water_paid ?? 0;

        // Get payments for this period
        $payments = Payment::where('tenancy_id', $tenancy->id)
            ->whereYear('paid_at', $period->year)
            ->whereMonth('paid_at', $period->month)
            ->with('allocations')
            ->get();

        $paymentsList = $payments->map(function ($payment) {
            return [
                'date' => $payment->paid_at->format('Y-m-d'),
                'reference' => $payment->reference,
                'amount' => $payment->amount,
                'source' => $payment->source,
                'allocations' => $payment->allocations->map(function ($alloc) {
                    return [
                        'type' => $alloc->allocation_type,
                        'amount' => $alloc->amount,
                        'period' => $alloc->period_month,
                    ];
                })->toArray(),
            ];
        })->toArray();

        $closingBalance = ($rentDue - $rentPaid) + ($waterDue - $waterPaid);

        return [
            'period' => $periodMonth,
            'tenant_name' => $tenancy->tenant->name,
            'unit' => $tenancy->unit->label ?? $tenancy->unit->number,
            'property' => $tenancy->unit->property->name,
            'opening_balance' => $openingBalance,
            'rent_due' => $rentDue,
            'rent_paid' => $rentPaid,
            'water_due' => $waterDue,
            'water_paid' => $waterPaid,
            'total_due' => $rentDue + $waterDue + $openingBalance,
            'total_paid' => $rentPaid + $waterPaid,
            'closing_balance' => $closingBalance,
            'payments' => $paymentsList,
            'generated_at' => now()->toIso8601String(),
        ];
    }
}
