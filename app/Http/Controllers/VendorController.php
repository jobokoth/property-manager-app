<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use App\Models\ServiceQuote;
use App\Models\ServiceInvoice;
use App\Models\VendorPayment;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VendorController extends Controller
{
    /**
     * Display the vendor dashboard with assigned service requests.
     */
    public function dashboard(): View
    {
        $user = auth()->user();

        if (!$user->hasRole('vendor')) {
            abort(403, 'Access restricted to vendors only.');
        }

        $assignedRequests = ServiceRequest::with(['property', 'unit', 'tenant', 'quotes', 'invoices'])
            ->where('assigned_vendor_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $stats = [
            'total_assigned' => ServiceRequest::where('assigned_vendor_id', $user->id)->count(),
            'pending_quotes' => ServiceQuote::where('vendor_user_id', $user->id)
                ->where('status', 'pending')
                ->count(),
            'approved_quotes' => ServiceQuote::where('vendor_user_id', $user->id)
                ->where('status', 'approved')
                ->count(),
            'pending_invoices' => ServiceInvoice::where('vendor_user_id', $user->id)
                ->where('status', 'pending')
                ->count(),
            'pending_payments' => VendorPayment::where('vendor_user_id', $user->id)
                ->where('status', 'paid')
                ->where('confirmed_at', null)
                ->count(),
        ];

        return view('vendor.dashboard', compact('assignedRequests', 'stats'));
    }

    /**
     * Submit a quote for a service request.
     */
    public function submitQuote(Request $request, ServiceRequest $serviceRequest): RedirectResponse
    {
        $user = auth()->user();

        if (!$user->hasRole('vendor')) {
            abort(403, 'Access restricted to vendors only.');
        }

        // Verify the service request is assigned to this vendor
        if ($serviceRequest->assigned_vendor_id !== $user->id) {
            abort(403, 'This service request is not assigned to you.');
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|min:10',
            'estimated_days' => 'nullable|integer|min:1',
        ]);

        ServiceQuote::create([
            'service_request_id' => $serviceRequest->id,
            'vendor_user_id' => $user->id,
            'amount' => $validated['amount'],
            'description' => $validated['description'],
            'estimated_days' => $validated['estimated_days'] ?? null,
            'status' => 'pending',
        ]);

        $serviceRequest->update(['status' => 'quoted']);

        return redirect()->route('service-requests.show', $serviceRequest)
            ->with('success', 'Quote submitted successfully. Awaiting approval.');
    }

    /**
     * Submit an invoice for a completed service request.
     */
    public function submitInvoice(Request $request, ServiceRequest $serviceRequest): RedirectResponse
    {
        $user = auth()->user();

        if (!$user->hasRole('vendor')) {
            abort(403, 'Access restricted to vendors only.');
        }

        // Verify the service request is assigned to this vendor
        if ($serviceRequest->assigned_vendor_id !== $user->id) {
            abort(403, 'This service request is not assigned to you.');
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|min:10',
            'quote_id' => 'nullable|exists:service_quotes,id',
        ]);

        // If a quote_id is provided, verify it belongs to this vendor and is approved
        $quoteId = null;
        if (!empty($validated['quote_id'])) {
            $quote = ServiceQuote::where('id', $validated['quote_id'])
                ->where('vendor_user_id', $user->id)
                ->where('status', 'approved')
                ->first();

            if ($quote) {
                $quoteId = $quote->id;
            }
        }

        ServiceInvoice::create([
            'service_request_id' => $serviceRequest->id,
            'service_quote_id' => $quoteId,
            'vendor_user_id' => $user->id,
            'amount' => $validated['amount'],
            'description' => $validated['description'],
            'status' => 'pending',
        ]);

        $serviceRequest->update(['status' => 'completed']);

        return redirect()->route('service-requests.show', $serviceRequest)
            ->with('success', 'Invoice submitted successfully.');
    }

    /**
     * Confirm receipt of payment.
     */
    public function confirmPayment(VendorPayment $payment): RedirectResponse
    {
        $user = auth()->user();

        if (!$user->hasRole('vendor')) {
            abort(403, 'Access restricted to vendors only.');
        }

        // Verify the payment is for this vendor
        if ($payment->vendor_user_id !== $user->id) {
            abort(403, 'This payment is not for you.');
        }

        // Verify payment is in 'paid' status (waiting for confirmation)
        if ($payment->status !== 'paid') {
            return redirect()->back()->with('error', 'This payment cannot be confirmed.');
        }

        $payment->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);

        return redirect()->route('vendor.dashboard')
            ->with('success', 'Payment confirmed successfully.');
    }

    /**
     * Show vendor's quotes.
     */
    public function quotes(): View
    {
        $user = auth()->user();

        if (!$user->hasRole('vendor')) {
            abort(403, 'Access restricted to vendors only.');
        }

        $quotes = ServiceQuote::with(['serviceRequest.property', 'serviceRequest.unit'])
            ->where('vendor_user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('vendor.quotes', compact('quotes'));
    }

    /**
     * Show vendor's invoices.
     */
    public function invoices(): View
    {
        $user = auth()->user();

        if (!$user->hasRole('vendor')) {
            abort(403, 'Access restricted to vendors only.');
        }

        $invoices = ServiceInvoice::with(['serviceRequest.property', 'serviceRequest.unit', 'payments'])
            ->where('vendor_user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('vendor.invoices', compact('invoices'));
    }

    /**
     * Show vendor's payments.
     */
    public function payments(): View
    {
        $user = auth()->user();

        if (!$user->hasRole('vendor')) {
            abort(403, 'Access restricted to vendors only.');
        }

        $payments = VendorPayment::with(['invoice.serviceRequest.property'])
            ->where('vendor_user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('vendor.payments', compact('payments'));
    }
}
