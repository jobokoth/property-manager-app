<?php

namespace App\Policies;

use App\Models\Property;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PropertyPolicy
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
     * Determine whether the user can view any properties.
     */
    public function viewAny(User $user): bool
    {
        // Owners can view properties
        if ($user->hasRole('owner') && $user->hasPermissionTo('properties.view')) {
            return true;
        }

        // Other users with appropriate permissions
        return $user->hasPermissionTo('properties.view');
    }

    /**
     * Determine whether the user can view the property.
     */
    public function view(User $user, Property $property): bool
    {
        if (!$user->hasPermissionTo('properties.view')) {
            return false;
        }

        return $this->hasPropertyAccess($user, $property);
    }

    /**
     * Determine whether the user can create properties.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('properties.create');
    }

    /**
     * Determine whether the user can update the property.
     */
    public function update(User $user, Property $property): bool
    {
        if (!$user->hasPermissionTo('properties.update')) {
            return false;
        }

        return $this->hasPropertyAccess($user, $property);
    }

    /**
     * Determine whether the user can delete the property.
     */
    public function delete(User $user, Property $property): bool
    {
        if (!$user->hasPermissionTo('properties.delete')) {
            return false;
        }

        // Only owner can delete
        return $property->owner_user_id === $user->id;
    }

    /**
     * Determine whether the user can manage units for the property.
     */
    public function manageUnits(User $user, Property $property): bool
    {
        if (!$user->hasPermissionTo('properties.manage_units')) {
            return false;
        }

        return $this->hasPropertyAccess($user, $property);
    }

    /**
     * Determine whether the user can manage tenants for the property.
     */
    public function manageTenants(User $user, Property $property): bool
    {
        if (!$user->hasPermissionTo('properties.manage_tenants')) {
            return false;
        }

        return $this->hasPropertyAccess($user, $property);
    }

    /**
     * Check if user has access to the property (owner, manager, caretaker, or vendor).
     */
    protected function hasPropertyAccess(User $user, Property $property): bool
    {
        // Owner has access
        if ($property->owner_user_id === $user->id) {
            return true;
        }

        // Check property_user pivot table (includes managers, caretakers, and vendors)
        return $property->users()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can manage caretakers for the property.
     */
    public function manageCaretakers(User $user, Property $property): bool
    {
        if (!$user->hasPermissionTo('properties.manage_caretakers')) {
            return false;
        }

        // Only owners and managers can manage caretakers, not caretakers themselves
        if ($property->owner_user_id === $user->id) {
            return true;
        }

        return $property->managers()->where('user_id', $user->id)->exists();
    }
}
