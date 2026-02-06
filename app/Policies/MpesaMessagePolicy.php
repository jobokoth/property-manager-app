<?php

namespace App\Policies;

use App\Models\MpesaMessage;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MpesaMessagePolicy
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
     * Determine whether the user can view any Mpesa messages.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('payments.view') ||
               $user->hasPermissionTo('payments.submit_mpesa');
    }

    /**
     * Determine whether the user can view the Mpesa message.
     */
    public function view(User $user, MpesaMessage $mpesaMessage): bool
    {
        // Tenant can view their own messages
        if ($mpesaMessage->tenant_user_id === $user->id) {
            return true;
        }

        if (!$user->hasPermissionTo('payments.view')) {
            return false;
        }

        return $this->hasPropertyAccess($user, $mpesaMessage);
    }

    /**
     * Determine whether the user can create Mpesa messages.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('payments.submit_mpesa') ||
               $user->hasPermissionTo('payments.ingest_mpesa');
    }

    /**
     * Determine whether the user can approve the Mpesa message.
     */
    public function approve(User $user, MpesaMessage $mpesaMessage): bool
    {
        if (!$user->hasPermissionTo('payments.ingest_mpesa')) {
            return false;
        }

        return $this->hasPropertyAccess($user, $mpesaMessage);
    }

    /**
     * Determine whether the user can reject the Mpesa message.
     */
    public function reject(User $user, MpesaMessage $mpesaMessage): bool
    {
        if (!$user->hasPermissionTo('payments.ingest_mpesa')) {
            return false;
        }

        return $this->hasPropertyAccess($user, $mpesaMessage);
    }

    /**
     * Check if user has access to the message's property.
     */
    protected function hasPropertyAccess(User $user, MpesaMessage $mpesaMessage): bool
    {
        $property = $mpesaMessage->property;

        if (!$property) {
            return false;
        }

        // Owner has access
        if ($property->owner_user_id === $user->id) {
            return true;
        }

        // Check property_user pivot table
        return $property->users()->where('user_id', $user->id)->exists();
    }
}
