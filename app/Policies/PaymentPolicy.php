<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaymentPolicy
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
     * Determine whether the user can view any payments.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('payments.view') ||
               $user->hasRole('tenant');
    }

    /**
     * Determine whether the user can view the payment.
     */
    public function view(User $user, Payment $payment): bool
    {
        // Tenant can view their own payments
        if ($payment->payer_user_id === $user->id) {
            return true;
        }

        if (!$user->hasPermissionTo('payments.view')) {
            return false;
        }

        return $this->hasPropertyAccess($user, $payment);
    }

    /**
     * Determine whether the user can create payments.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('payments.ingest_mpesa') ||
               $user->hasPermissionTo('payments.submit_mpesa');
    }

    /**
     * Determine whether the user can update the payment.
     */
    public function update(User $user, Payment $payment): bool
    {
        if (!$user->hasPermissionTo('payments.allocate')) {
            return false;
        }

        return $this->hasPropertyAccess($user, $payment);
    }

    /**
     * Determine whether the user can delete the payment.
     */
    public function delete(User $user, Payment $payment): bool
    {
        // Only super_admin can delete payments (handled in before)
        return false;
    }

    /**
     * Determine whether the user can allocate the payment.
     */
    public function allocate(User $user, Payment $payment): bool
    {
        if (!$user->hasPermissionTo('payments.allocate')) {
            return false;
        }

        return $this->hasPropertyAccess($user, $payment);
    }

    /**
     * Check if user has access to the payment's property.
     */
    protected function hasPropertyAccess(User $user, Payment $payment): bool
    {
        $property = $payment->property;

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
