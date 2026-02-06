<?php

namespace App\Policies;

use App\Models\Unit;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UnitPolicy
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
     * Determine whether the user can view any units.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('properties.view');
    }

    /**
     * Determine whether the user can view the unit.
     */
    public function view(User $user, Unit $unit): bool
    {
        if (!$user->hasPermissionTo('properties.view')) {
            return false;
        }

        return $this->hasPropertyAccess($user, $unit);
    }

    /**
     * Determine whether the user can create units.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('properties.manage_units');
    }

    /**
     * Determine whether the user can update the unit.
     */
    public function update(User $user, Unit $unit): bool
    {
        if (!$user->hasPermissionTo('properties.manage_units')) {
            return false;
        }

        return $this->hasPropertyAccess($user, $unit);
    }

    /**
     * Determine whether the user can delete the unit.
     */
    public function delete(User $user, Unit $unit): bool
    {
        if (!$user->hasPermissionTo('properties.manage_units')) {
            return false;
        }

        return $this->hasPropertyAccess($user, $unit);
    }

    /**
     * Check if user has access to the unit's property.
     */
    protected function hasPropertyAccess(User $user, Unit $unit): bool
    {
        $property = $unit->property;

        // Owner has access
        if ($property->owner_user_id === $user->id) {
            return true;
        }

        // Check property_user pivot table
        return $property->users()->where('user_id', $user->id)->exists();
    }
}
