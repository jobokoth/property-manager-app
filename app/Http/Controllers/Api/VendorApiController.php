<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceInvoice;
use App\Models\ServiceQuote;
use App\Models\ServiceRequest;
use App\Models\ServiceSchedule;
use App\Models\VendorPayment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorApiController extends Controller
{
    /**
     * Get vendor dashboard data.
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();

        $assignedJobs = ServiceRequest::where('vendor_user_id', $user->id)
            ->whereIn('status', ['quoted', 'scheduled', 'in_progress'])
            ->count();

        $pendingQuotes = ServiceRequest::where('vendor_user_id', $user->id)
            ->where('status', 'in_review')
            ->count();

        $completedJobs = ServiceRequest::where('vendor_user_id', $user->id)
            ->whereIn('status', ['completed', 'closed'])
            ->count();

        $pendingPayments = VendorPayment::whereHas('invoice', function ($q) use ($user) {
            $q->where('vendor_user_id', $user->id);
        })->whereNull('confirmed_by_vendor_at')->count();

        return response()->json([
            'stats' => [
                'assigned_jobs' => $assignedJobs,
                'pending_quotes' => $pendingQuotes,
                'completed_jobs' => $completedJobs,
                'pending_payments' => $pendingPayments,
            ],
        ]);
    }

    /**
     * Get vendor's assigned jobs.
     */
    public function jobs(Request $request): JsonResponse
    {
        $jobs = ServiceRequest::with(['property', 'unit'])
            ->where('vendor_user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($jobs);
    }

    /**
     * Show a specific job.
     */
    public function showJob(Request $request, ServiceRequest $serviceRequest): JsonResponse
    {
        if ($serviceRequest->vendor_user_id !== $request->user()->id) {
            return response()->json(['message' => 'Job not found'], 404);
        }

        return response()->json([
            'job' => $serviceRequest->load(['property', 'unit', 'media', 'quotes', 'schedules', 'invoices']),
        ]);
    }

    /**
     * Submit a quote.
     */
    public function submitQuote(Request $request, ServiceRequest $serviceRequest): JsonResponse
    {
        if ($serviceRequest->vendor_user_id !== $request->user()->id) {
            return response()->json(['message' => 'Job not found'], 404);
        }

        $request->validate([
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string',
        ]);

        $quote = ServiceQuote::create([
            'service_request_id' => $serviceRequest->id,
            'vendor_user_id' => $request->user()->id,
            'amount' => $request->amount,
            'description' => $request->description,
            'status' => 'submitted',
        ]);

        $serviceRequest->update(['status' => 'quoted']);

        return response()->json([
            'message' => 'Quote submitted successfully',
            'quote' => $quote,
        ], 201);
    }

    /**
     * Submit a schedule.
     */
    public function submitSchedule(Request $request, ServiceRequest $serviceRequest): JsonResponse
    {
        if ($serviceRequest->vendor_user_id !== $request->user()->id) {
            return response()->json(['message' => 'Job not found'], 404);
        }

        $request->validate([
            'scheduled_start' => 'required|date|after:now',
            'scheduled_end' => 'nullable|date|after:scheduled_start',
            'notes' => 'nullable|string',
        ]);

        $schedule = ServiceSchedule::create([
            'service_request_id' => $serviceRequest->id,
            'vendor_user_id' => $request->user()->id,
            'scheduled_start' => $request->scheduled_start,
            'scheduled_end' => $request->scheduled_end,
            'notes' => $request->notes,
            'status' => 'scheduled',
        ]);

        $serviceRequest->update(['status' => 'scheduled']);

        return response()->json([
            'message' => 'Schedule submitted successfully',
            'schedule' => $schedule,
        ], 201);
    }

    /**
     * Submit an invoice.
     */
    public function submitInvoice(Request $request, ServiceRequest $serviceRequest): JsonResponse
    {
        if ($serviceRequest->vendor_user_id !== $request->user()->id) {
            return response()->json(['message' => 'Job not found'], 404);
        }

        $request->validate([
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        // Generate invoice number
        $invoiceNo = 'INV-' . date('Ymd') . '-' . str_pad(ServiceInvoice::count() + 1, 4, '0', STR_PAD_LEFT);

        $invoice = ServiceInvoice::create([
            'service_request_id' => $serviceRequest->id,
            'vendor_user_id' => $request->user()->id,
            'amount' => $request->amount,
            'invoice_no' => $invoiceNo,
            'notes' => $request->notes,
            'status' => 'submitted',
        ]);

        return response()->json([
            'message' => 'Invoice submitted successfully',
            'invoice' => $invoice,
        ], 201);
    }

    /**
     * Get vendor's invoices.
     */
    public function invoices(Request $request): JsonResponse
    {
        $invoices = ServiceInvoice::with('serviceRequest')
            ->where('vendor_user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($invoices);
    }

    /**
     * Get vendor's payments.
     */
    public function payments(Request $request): JsonResponse
    {
        $payments = VendorPayment::with('invoice.serviceRequest')
            ->whereHas('invoice', function ($q) use ($request) {
                $q->where('vendor_user_id', $request->user()->id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($payments);
    }

    /**
     * Confirm payment received.
     */
    public function confirmPayment(Request $request, VendorPayment $vendorPayment): JsonResponse
    {
        // Verify ownership
        if ($vendorPayment->invoice->vendor_user_id !== $request->user()->id) {
            return response()->json(['message' => 'Payment not found'], 404);
        }

        $vendorPayment->update([
            'confirmed_by_vendor_at' => now(),
        ]);

        return response()->json([
            'message' => 'Payment confirmed successfully',
            'payment' => $vendorPayment,
        ]);
    }
}
