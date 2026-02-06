<?php

namespace App\Policies;

use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ServiceRequestPolicy
{
    use HandlesAuthorization;

    /**
     * Perform pre-authorization checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any service requests.
     */
    public function viewAny(User $user): bool
    {
        // Tenants can view their own service requests
        if ($user->hasRole('tenant') && $user->hasPermissionTo('requests.view')) {
            return true;
        }

        // Other users with appropriate permissions
        return $user->hasPermissionTo('requests.view') ||
               $user->hasPermissionTo('requests.create') ||
               $user->hasPermissionTo('vendors.view_jobs');
    }

    /**
     * Determine whether the user can view the service request.
     */
    public function view(User $user, ServiceRequest $serviceRequest): bool
    {
        // Tenant can view their own requests
        if ($serviceRequest->tenant_user_id === $user->id) {
            return true;
        }

        // Assigned vendor can view
        if ($serviceRequest->assigned_vendor_id === $user->id) {
            return true;
        }

        // Caretaker can view requests for their properties
        if ($user->hasRole('caretaker') && $user->hasPermissionTo('requests.view')) {
            return $this->hasCaretakerPropertyAccess($user, $serviceRequest);
        }

        if (!$user->hasPermissionTo('requests.view')) {
            return false;
        }

        return $this->hasPropertyAccess($user, $serviceRequest);
    }

    /**
     * Determine whether the user can create service requests.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('requests.create');
    }

    /**
     * Determine whether the user can update the service request.
     */
    public function update(User $user, ServiceRequest $serviceRequest): bool
    {
        // Assigned vendor can update status
        if ($serviceRequest->assigned_vendor_id === $user->id &&
            $user->hasPermissionTo('vendors.schedule_work')) {
            return true;
        }

        if (!$user->hasPermissionTo('requests.update_status')) {
            return false;
        }

        return $this->hasPropertyAccess($user, $serviceRequest);
    }

    /**
     * Determine whether the user can delete the service request.
     */
    public function delete(User $user, ServiceRequest $serviceRequest): bool
    {
        if (!$user->hasPermissionTo('requests.update_status')) {
            return false;
        }

        return $this->hasPropertyAccess($user, $serviceRequest);
    }

    /**
     * Determine whether the user can assign a vendor.
     */
    public function assignVendor(User $user, ServiceRequest $serviceRequest): bool
    {
        if (!$user->hasPermissionTo('requests.assign_vendor')) {
            return false;
        }

        return $this->hasPropertyAccess($user, $serviceRequest);
    }

    /**
     * Determine whether the user can submit a quote.
     */
    public function submitQuote(User $user, ServiceRequest $serviceRequest): bool
    {
        return $serviceRequest->assigned_vendor_id === $user->id &&
               $user->hasPermissionTo('vendors.submit_quote');
    }

    /**
     * Determine whether the user can add a comment to the service request.
     */
    public function addComment(User $user, ServiceRequest $serviceRequest): bool
    {
        if (!$user->hasPermissionTo('requests.add_comment')) {
            return false;
        }

        // Tenant can comment on their own requests
        if ($serviceRequest->tenant_user_id === $user->id) {
            return true;
        }

        // Assigned vendor can comment
        if ($serviceRequest->assigned_vendor_id === $user->id) {
            return true;
        }

        // Caretaker can comment on property requests
        if ($user->hasRole('caretaker')) {
            return $this->hasCaretakerPropertyAccess($user, $serviceRequest);
        }

        return $this->hasPropertyAccess($user, $serviceRequest);
    }

    /**
     * Check if user has access to the service request's property.
     */
    protected function hasPropertyAccess(User $user, ServiceRequest $serviceRequest): bool
    {
        $property = $serviceRequest->property;

        if (!$property) {
            return false;
        }

        // Owner has access
        if ($property->owner_user_id === $user->id) {
            return true;
        }

        // Check property_user pivot table (managers)
        return $property->managers()->where('user_id', $user->id)->exists();
    }

    /**
     * Check if caretaker has access to the service request's property.
     */
    protected function hasCaretakerPropertyAccess(User $user, ServiceRequest $serviceRequest): bool
    {
        $property = $serviceRequest->property;

        if (!$property) {
            return false;
        }

        return $property->caretakers()->where('user_id', $user->id)->exists();
    }
}
