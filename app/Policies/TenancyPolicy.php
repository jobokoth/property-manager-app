<?php

namespace App\Policies;

use App\Models\Tenancy;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TenancyPolicy
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
     * Determine whether the user can view any tenancies.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('properties.view') ||
               $user->hasRole('tenant');
    }

    /**
     * Determine whether the user can view the tenancy.
     */
    public function view(User $user, Tenancy $tenancy): bool
    {
        // Tenant can view their own tenancy
        if ($tenancy->tenant_user_id === $user->id) {
            return true;
        }

        if (!$user->hasPermissionTo('properties.view')) {
            return false;
        }

        return $this->hasPropertyAccess($user, $tenancy);
    }

    /**
     * Determine whether the user can create tenancies.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('properties.manage_tenants');
    }

    /**
     * Determine whether the user can update the tenancy.
     */
    public function update(User $user, Tenancy $tenancy): bool
    {
        if (!$user->hasPermissionTo('properties.manage_tenants')) {
            return false;
        }

        return $this->hasPropertyAccess($user, $tenancy);
    }

    /**
     * Determine whether the user can delete the tenancy.
     */
    public function delete(User $user, Tenancy $tenancy): bool
    {
        if (!$user->hasPermissionTo('properties.manage_tenants')) {
            return false;
        }

        return $this->hasPropertyAccess($user, $tenancy);
    }

    /**
     * Check if user has access to the tenancy's property.
     */
    protected function hasPropertyAccess(User $user, Tenancy $tenancy): bool
    {
        $property = $tenancy->unit->property;

        // Owner has access
        if ($property->owner_user_id === $user->id) {
            return true;
        }

        // Check property_user pivot table
        return $property->users()->where('user_id', $user->id)->exists();
    }
}
