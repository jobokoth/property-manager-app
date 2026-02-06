<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\Unit;
use App\Models\WaterBill;
use App\Models\WaterCharge;
use App\Models\WaterMeter;
use App\Models\WaterReading;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class WaterController extends Controller
{
    /**
     * Display water management dashboard.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // Get properties user has access to
        $properties = Property::where('owner_user_id', $user->id)
            ->orWhereHas('users', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->with(['units.waterMeters'])
            ->get();

        // Get recent water readings
        $recentReadings = WaterReading::with('waterMeter.unit.property')
            ->whereHas('waterMeter.unit.property', function ($q) use ($user) {
                $q->where('owner_user_id', $user->id)
                    ->orWhereHas('users', function ($q2) use ($user) {
                        $q2->where('user_id', $user->id);
                    });
            })
            ->orderBy('reading_date', 'desc')
            ->take(10)
            ->get();

        return view('water.index', compact('properties', 'recentReadings'));
    }

    /**
     * Show water meters list.
     */
    public function meters(Request $request): View
    {
        $user = $request->user();

        $meters = WaterMeter::with(['unit.property', 'readings' => function ($q) {
            $q->orderBy('reading_date', 'desc')->take(1);
        }])
            ->whereHas('unit.property', function ($q) use ($user) {
                $q->where('owner_user_id', $user->id)
                    ->orWhereHas('users', function ($q2) use ($user) {
                        $q2->where('user_id', $user->id);
                    });
            })
            ->paginate(20);

        return view('water.meters', compact('meters'));
    }

    /**
     * Show create meter form.
     */
    public function createMeter(): View
    {
        $units = Unit::with('property')
            ->whereDoesntHave('waterMeter')
            ->get();

        return view('water.create-meter', compact('units'));
    }

    /**
     * Store a new water meter.
     */
    public function storeMeter(Request $request): RedirectResponse
    {
        $request->validate([
            'unit_id' => 'required|exists:units,id|unique:water_meters,unit_id',
            'meter_serial' => 'required|string|max:100|unique:water_meters,meter_serial',
        ]);

        $unit = Unit::findOrFail($request->unit_id);

        WaterMeter::create([
            'property_id' => $unit->property_id,
            'unit_id' => $request->unit_id,
            'meter_serial' => $request->meter_serial,
            'status' => 'active',
        ]);

        return redirect()->route('water.meters')
            ->with('success', 'Water meter added successfully.');
    }

    /**
     * Show water readings list.
     */
    public function readings(Request $request): View
    {
        $user = $request->user();

        $readings = WaterReading::with(['waterMeter.unit.property', 'capturedBy'])
            ->whereHas('waterMeter.unit.property', function ($q) use ($user) {
                $q->where('owner_user_id', $user->id)
                    ->orWhereHas('users', function ($q2) use ($user) {
                        $q2->where('user_id', $user->id);
                    });
            })
            ->orderBy('reading_date', 'desc')
            ->paginate(20);

        return view('water.readings', compact('readings'));
    }

    /**
     * Show create reading form.
     */
    public function createReading(): View
    {
        $meters = WaterMeter::with(['unit.property', 'readings' => function ($q) {
            $q->orderBy('reading_date', 'desc')->take(1);
        }])
            ->where('status', 'active')
            ->get();

        return view('water.create-reading', compact('meters'));
    }

    /**
     * Store a new water reading.
     */
    public function storeReading(Request $request): RedirectResponse
    {
        $request->validate([
            'water_meter_id' => 'required|exists:water_meters,id',
            'reading_date' => 'required|date|before_or_equal:today',
            'reading_value' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        // Check if reading value is greater than previous
        $previousReading = WaterReading::where('water_meter_id', $request->water_meter_id)
            ->orderBy('reading_date', 'desc')
            ->first();

        if ($previousReading && $request->reading_value < $previousReading->reading_value) {
            return back()->withErrors([
                'reading_value' => 'Reading value must be greater than or equal to the previous reading (' . $previousReading->reading_value . ').',
            ])->withInput();
        }

        WaterReading::create([
            'water_meter_id' => $request->water_meter_id,
            'reading_date' => $request->reading_date,
            'reading_value' => $request->reading_value,
            'captured_by_user_id' => $request->user()->id,
            'notes' => $request->notes,
        ]);

        return redirect()->route('water.readings')
            ->with('success', 'Water reading recorded successfully.');
    }

    /**
     * Show water charges list.
     */
    public function charges(Request $request): View
    {
        $user = $request->user();

        $charges = WaterCharge::with(['tenancy.unit.property', 'tenancy.tenant'])
            ->whereHas('tenancy.unit.property', function ($q) use ($user) {
                $q->where('owner_user_id', $user->id)
                    ->orWhereHas('users', function ($q2) use ($user) {
                        $q2->where('user_id', $user->id);
                    });
            })
            ->orderBy('period_month', 'desc')
            ->paginate(20);

        return view('water.charges', compact('charges'));
    }

    /**
     * Show create charge form.
     */
    public function createCharge(): View
    {
        $tenancies = \App\Models\Tenancy::with(['unit.property', 'tenant'])
            ->where('status', 'active')
            ->get();

        return view('water.create-charge', compact('tenancies'));
    }

    /**
     * Store a new water charge.
     */
    public function storeCharge(Request $request): RedirectResponse
    {
        $request->validate([
            'tenancy_id' => 'required|exists:tenancies,id',
            'period_month' => 'required|date_format:Y-m',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        // Check for duplicate
        $existing = WaterCharge::where('tenancy_id', $request->tenancy_id)
            ->where('period_month', $request->period_month)
            ->exists();

        if ($existing) {
            return back()->withErrors([
                'period_month' => 'A water charge already exists for this tenancy and period.',
            ])->withInput();
        }

        WaterCharge::create([
            'tenancy_id' => $request->tenancy_id,
            'period_month' => $request->period_month,
            'amount' => $request->amount,
            'source' => 'manual',
            'notes' => $request->notes,
        ]);

        // Update balance record
        $balance = \App\Models\Balance::firstOrCreate(
            [
                'tenancy_id' => $request->tenancy_id,
                'period_month' => $request->period_month,
            ],
            [
                'rent_due' => 0,
                'rent_paid' => 0,
                'water_due' => 0,
                'water_paid' => 0,
                'carried_forward' => 0,
            ]
        );

        $balance->increment('water_due', $request->amount);

        return redirect()->route('water.charges')
            ->with('success', 'Water charge added successfully.');
    }

    /**
     * Generate water bill from readings.
     */
    public function generateBill(Request $request): RedirectResponse
    {
        $request->validate([
            'water_meter_id' => 'required|exists:water_meters,id',
            'period_month' => 'required|date_format:Y-m',
            'rate_per_unit' => 'required|numeric|min:0',
        ]);

        $meter = WaterMeter::with('unit.activeTenancy')->findOrFail($request->water_meter_id);

        if (!$meter->unit->activeTenancy) {
            return back()->withErrors(['water_meter_id' => 'This unit has no active tenancy.'])->withInput();
        }

        // Get readings for the period
        $periodStart = \Carbon\Carbon::createFromFormat('Y-m', $request->period_month)->startOfMonth();
        $periodEnd = $periodStart->copy()->endOfMonth();

        $startReading = WaterReading::where('water_meter_id', $meter->id)
            ->where('reading_date', '<', $periodStart)
            ->orderBy('reading_date', 'desc')
            ->first();

        $endReading = WaterReading::where('water_meter_id', $meter->id)
            ->whereBetween('reading_date', [$periodStart, $periodEnd])
            ->orderBy('reading_date', 'desc')
            ->first();

        if (!$startReading || !$endReading) {
            return back()->withErrors(['water_meter_id' => 'Insufficient readings to generate bill.'])->withInput();
        }

        $billData = WaterBill::calculateFromReadings(
            $startReading->reading_value,
            $endReading->reading_value,
            $request->rate_per_unit
        );

        // Check for duplicate
        $existing = WaterBill::where('tenancy_id', $meter->unit->activeTenancy->id)
            ->where('period_month', $request->period_month)
            ->exists();

        if ($existing) {
            return back()->withErrors(['period_month' => 'A water bill already exists for this period.'])->withInput();
        }

        WaterBill::create([
            'tenancy_id' => $meter->unit->activeTenancy->id,
            'period_month' => $request->period_month,
            'units_consumed' => $billData['units_consumed'],
            'rate_per_unit' => $billData['rate_per_unit'],
            'amount' => $billData['amount'],
            'status' => 'pending',
        ]);

        // Update balance
        $balance = \App\Models\Balance::firstOrCreate(
            [
                'tenancy_id' => $meter->unit->activeTenancy->id,
                'period_month' => $request->period_month,
            ],
            [
                'rent_due' => 0,
                'rent_paid' => 0,
                'water_due' => 0,
                'water_paid' => 0,
                'carried_forward' => 0,
            ]
        );

        $balance->increment('water_due', $billData['amount']);

        return redirect()->route('water.charges')
            ->with('success', 'Water bill generated: ' . $billData['units_consumed'] . ' units = KES ' . number_format($billData['amount'], 2));
    }
}
