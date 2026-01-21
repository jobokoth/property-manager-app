<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Tenancy;
use App\Models\User;
use App\Models\Property;
use App\Services\MpesaMessageParserService;
use App\Services\PaymentAllocationService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PaymentController extends Controller
{
    protected MpesaMessageParserService $parserService;
    protected PaymentAllocationService $allocationService;

    public function __construct(
        MpesaMessageParserService $parserService,
        PaymentAllocationService $allocationService
    ) {
        $this->parserService = $parserService;
        $this->allocationService = $allocationService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $user = auth()->user();
        if (!$user->can('payments.view')) {
            abort(403, 'Unauthorized access');
        }

        $payments = Payment::with(['property', 'tenancy.tenant', 'payer'])->paginate(15);

        return view('payments.index', compact('payments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $user = auth()->user();
        if (!$user->can('payments.ingest_mpesa')) {
            abort(403, 'Unauthorized access');
        }

        $tenancies = Tenancy::where('status', 'active')->with('tenant', 'unit')->get();
        $payers = User::all();
        $properties = Property::all();

        return view('payments.create', compact('tenancies', 'payers', 'properties'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        if (!$user->can('payments.ingest_mpesa')) {
            abort(403, 'Unauthorized access');
        }

        $validatedData = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'tenancy_id' => 'nullable|exists:tenancies,id',
            'payer_user_id' => 'nullable|exists:users,id',
            'source' => 'required|in:mpesa,manual',
            'amount' => 'required|numeric|min:0.01',
            'paid_at' => 'required|date',
            'reference' => 'nullable|string|max:255',
            'status' => 'required|in:pending,confirmed,cancelled'
        ]);

        Payment::create($validatedData);

        return redirect()->route('payments.index')
                         ->with('success', 'Payment recorded successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Payment $payment): View
    {
        $user = auth()->user();
        if (!$user->can('payments.view')) {
            abort(403, 'Unauthorized access');
        }

        $payment->load(['property', 'tenancy.tenant', 'payer', 'allocations']);

        return view('payments.show', compact('payment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Payment $payment): View
    {
        $user = auth()->user();
        if (!$user->can('payments.allocate')) {
            abort(403, 'Unauthorized access');
        }

        $tenancies = Tenancy::where('status', 'active')->with('tenant', 'unit')->get();
        $payers = User::all();
        $properties = Property::all();

        return view('payments.edit', compact('payment', 'tenancies', 'payers', 'properties'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Payment $payment): RedirectResponse
    {
        $user = auth()->user();
        if (!$user->can('payments.allocate')) {
            abort(403, 'Unauthorized access');
        }

        $validatedData = $request->validate([
            'property_id' => 'sometimes|required|exists:properties,id',
            'tenancy_id' => 'nullable|exists:tenancies,id',
            'payer_user_id' => 'nullable|exists:users,id',
            'source' => 'sometimes|required|in:mpesa,manual',
            'amount' => 'sometimes|required|numeric|min:0.01',
            'paid_at' => 'sometimes|required|date',
            'reference' => 'nullable|string|max:255',
            'status' => 'sometimes|required|in:pending,confirmed,cancelled'
        ]);

        $payment->update($validatedData);

        return redirect()->route('payments.index')
                         ->with('success', 'Payment updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment): RedirectResponse
    {
        $user = auth()->user();
        if (!$user->can('payments.allocate')) {
            abort(403, 'Unauthorized access');
        }

        $payment->delete();

        return redirect()->route('payments.index')
                         ->with('success', 'Payment deleted successfully.');
    }

    /**
     * Show the M-Pesa upload form.
     */
    public function showMpesaUpload(): View
    {
        $user = auth()->user();
        if (!$user->can('payments.ingest_mpesa')) {
            abort(403, 'Unauthorized access');
        }

        $tenancies = Tenancy::where('status', 'active')
            ->with(['tenant', 'unit.property'])
            ->get();

        return view('payments.mpesa-upload', compact('tenancies'));
    }

    /**
     * Process M-Pesa message upload and allocate payment.
     */
    public function processMpesaUpload(Request $request): RedirectResponse
    {
        $user = auth()->user();
        if (!$user->can('payments.ingest_mpesa')) {
            abort(403, 'Unauthorized access');
        }

        $validated = $request->validate([
            'tenancy_id' => 'required|exists:tenancies,id',
            'mpesa_message' => 'required|string|min:20',
        ]);

        // Parse the M-Pesa message
        $parsedData = $this->parserService->parse($validated['mpesa_message']);

        if (!$parsedData) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Could not parse the M-Pesa message. Please check the format and try again.');
        }

        // Allocate the payment
        $result = $this->allocationService->allocatePayment(
            $validated['tenancy_id'],
            $parsedData
        );

        if ($result['success']) {
            return redirect()->route('payments.index')
                ->with('success', $result['message']);
        }

        return redirect()->back()
            ->withInput()
            ->with('error', $result['message']);
    }
}
