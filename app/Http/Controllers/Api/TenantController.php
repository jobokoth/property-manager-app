<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Balance;
use App\Models\MpesaMessage;
use App\Models\ServiceRequest;
use App\Models\Statement;
use App\Models\Tenancy;
use App\Services\MpesaMessageParserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    /**
     * Get tenant dashboard data.
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();

        $activeTenancy = Tenancy::with('unit.property')
            ->where('tenant_user_id', $user->id)
            ->where('status', 'active')
            ->first();

        $serviceRequestsCount = ServiceRequest::where('tenant_user_id', $user->id)->count();
        $mpesaMessagesCount = MpesaMessage::where('tenant_user_id', $user->id)->count();
        $statementsCount = $activeTenancy
            ? Statement::where('tenancy_id', $activeTenancy->id)->count()
            : 0;

        $recentServiceRequests = ServiceRequest::where('tenant_user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        return response()->json([
            'tenancy' => $activeTenancy,
            'stats' => [
                'service_requests' => $serviceRequestsCount,
                'mpesa_messages' => $mpesaMessagesCount,
                'statements' => $statementsCount,
            ],
            'recent_service_requests' => $recentServiceRequests,
        ]);
    }

    /**
     * Get active tenancy details.
     */
    public function tenancy(Request $request): JsonResponse
    {
        $tenancy = Tenancy::with(['unit.property', 'balances' => function ($query) {
            $query->orderBy('period_month', 'desc')->take(12);
        }])
            ->where('tenant_user_id', $request->user()->id)
            ->where('status', 'active')
            ->first();

        if (!$tenancy) {
            return response()->json(['message' => 'No active tenancy found'], 404);
        }

        return response()->json(['tenancy' => $tenancy]);
    }

    /**
     * Get tenant balances.
     */
    public function balances(Request $request): JsonResponse
    {
        $tenancy = Tenancy::where('tenant_user_id', $request->user()->id)
            ->where('status', 'active')
            ->first();

        if (!$tenancy) {
            return response()->json(['message' => 'No active tenancy found'], 404);
        }

        $balances = Balance::where('tenancy_id', $tenancy->id)
            ->orderBy('period_month', 'desc')
            ->paginate(12);

        return response()->json($balances);
    }

    /**
     * Get tenant statements.
     */
    public function statements(Request $request): JsonResponse
    {
        $tenancy = Tenancy::where('tenant_user_id', $request->user()->id)
            ->where('status', 'active')
            ->first();

        if (!$tenancy) {
            return response()->json(['message' => 'No active tenancy found'], 404);
        }

        $statements = Statement::where('tenancy_id', $tenancy->id)
            ->orderBy('period_month', 'desc')
            ->paginate(12);

        return response()->json($statements);
    }

    /**
     * Show a specific statement.
     */
    public function showStatement(Request $request, Statement $statement): JsonResponse
    {
        // Verify ownership
        $tenancy = Tenancy::where('tenant_user_id', $request->user()->id)
            ->where('id', $statement->tenancy_id)
            ->first();

        if (!$tenancy) {
            return response()->json(['message' => 'Statement not found'], 404);
        }

        return response()->json(['statement' => $statement]);
    }

    /**
     * Upload Mpesa message.
     */
    public function uploadMpesaMessage(Request $request): JsonResponse
    {
        $request->validate([
            'raw_text' => 'required|string',
        ]);

        $tenancy = Tenancy::with('unit.property')
            ->where('tenant_user_id', $request->user()->id)
            ->where('status', 'active')
            ->first();

        if (!$tenancy) {
            return response()->json(['message' => 'No active tenancy found'], 404);
        }

        $parser = new MpesaMessageParserService();
        $parsed = $parser->parse($request->raw_text);

        $mpesaMessage = MpesaMessage::create([
            'property_id' => $tenancy->unit->property_id,
            'tenant_user_id' => $request->user()->id,
            'raw_text' => $request->raw_text,
            'sender_msisdn' => $request->user()->phone,
            'amount' => $parsed['amount'],
            'trans_id' => $parsed['trans_id'],
            'trans_time' => $parsed['trans_time'],
            'parsed_json' => $parsed,
            'status' => $parsed['trans_id'] ? 'parsed' : 'new',
            'uploaded_by_user_id' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Mpesa message uploaded successfully',
            'mpesa_message' => $mpesaMessage,
        ], 201);
    }

    /**
     * Get tenant's Mpesa messages.
     */
    public function mpesaMessages(Request $request): JsonResponse
    {
        $messages = MpesaMessage::where('tenant_user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($messages);
    }

    /**
     * Get tenant's service requests.
     */
    public function serviceRequests(Request $request): JsonResponse
    {
        $requests = ServiceRequest::with('media')
            ->where('tenant_user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($requests);
    }

    /**
     * Create a service request.
     */
    public function createServiceRequest(Request $request): JsonResponse
    {
        $request->validate([
            'category' => 'required|string|in:plumbing,electrical,carpentry,painting,cleaning,other',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|string|in:low,medium,high,urgent',
        ]);

        $tenancy = Tenancy::with('unit.property')
            ->where('tenant_user_id', $request->user()->id)
            ->where('status', 'active')
            ->first();

        if (!$tenancy) {
            return response()->json(['message' => 'No active tenancy found'], 404);
        }

        $serviceRequest = ServiceRequest::create([
            'property_id' => $tenancy->unit->property_id,
            'unit_id' => $tenancy->unit_id,
            'tenancy_id' => $tenancy->id,
            'tenant_user_id' => $request->user()->id,
            'category' => $request->category,
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $request->priority,
            'status' => 'open',
        ]);

        return response()->json([
            'message' => 'Service request created successfully',
            'service_request' => $serviceRequest,
        ], 201);
    }

    /**
     * Show a specific service request.
     */
    public function showServiceRequest(Request $request, ServiceRequest $serviceRequest): JsonResponse
    {
        // Verify ownership
        if ($serviceRequest->tenant_user_id !== $request->user()->id) {
            return response()->json(['message' => 'Service request not found'], 404);
        }

        return response()->json([
            'service_request' => $serviceRequest->load('media', 'quotes', 'schedules'),
        ]);
    }
}
