<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use App\Models\ServiceRequestComment;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class ServiceRequestCommentController extends Controller
{
    /**
     * Store a new comment.
     */
    public function store(Request $request, ServiceRequest $serviceRequest): RedirectResponse
    {
        $user = auth()->user();

        // Check if user can add comments
        if (!$user->can('requests.add_comment')) {
            abort(403, 'You do not have permission to add comments.');
        }

        // Verify user has access to this service request
        $this->authorizeServiceRequestAccess($serviceRequest);

        $validated = $request->validate([
            'comment' => 'required|string|max:2000',
            'type' => 'required|in:note,clarification,status_update,internal',
            'is_internal' => 'boolean',
        ]);

        // Only managers can create internal comments
        $isInternal = false;
        if (isset($validated['is_internal']) && $validated['is_internal']) {
            if (!$user->can('requests.view_internal_comments')) {
                $isInternal = false;
            } else {
                $isInternal = true;
            }
        }

        // If type is 'internal', mark as internal
        if ($validated['type'] === 'internal') {
            $isInternal = true;
        }

        ServiceRequestComment::create([
            'service_request_id' => $serviceRequest->id,
            'user_id' => $user->id,
            'comment' => $validated['comment'],
            'type' => $validated['type'],
            'is_internal' => $isInternal,
        ]);

        return redirect()->back()->with('success', 'Comment added successfully.');
    }

    /**
     * Delete a comment.
     */
    public function destroy(ServiceRequest $serviceRequest, ServiceRequestComment $comment): RedirectResponse
    {
        $user = auth()->user();

        // Verify comment belongs to this service request
        if ($comment->service_request_id !== $serviceRequest->id) {
            abort(404);
        }

        // Only the comment author or managers can delete
        if ($comment->user_id !== $user->id && !$user->hasRole(['super_admin', 'property_manager', 'owner'])) {
            abort(403, 'You can only delete your own comments.');
        }

        $comment->delete();

        return redirect()->back()->with('success', 'Comment deleted successfully.');
    }

    /**
     * Check if user has access to the service request.
     */
    protected function authorizeServiceRequestAccess(ServiceRequest $serviceRequest): void
    {
        $user = auth()->user();

        if ($user->hasRole('super_admin')) {
            return;
        }

        // Tenant can access their own requests
        if ($user->hasRole('tenant') && $serviceRequest->tenant_user_id === $user->id) {
            return;
        }

        // Vendor can access assigned requests
        if ($user->hasRole('vendor') && $serviceRequest->assigned_vendor_id === $user->id) {
            return;
        }

        // Caretaker can access requests for their properties
        if ($user->hasRole('caretaker')) {
            $propertyIds = $user->caretakerProperties()->pluck('properties.id')->toArray();
            if (in_array($serviceRequest->property_id, $propertyIds)) {
                return;
            }
        }

        // Property manager or owner
        if ($user->hasRole(['property_manager', 'owner'])) {
            $ownedIds = $user->ownedProperties()->pluck('id')->toArray();
            $managedIds = $user->managedProperties()->pluck('properties.id')->toArray();
            $propertyIds = array_unique(array_merge($ownedIds, $managedIds));

            if (in_array($serviceRequest->property_id, $propertyIds)) {
                return;
            }
        }

        abort(403, 'You do not have access to this service request.');
    }
}
