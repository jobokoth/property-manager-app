<?php

namespace App\Services;

use App\Models\TenantInvite;
use App\Models\User;
use App\Models\Tenancy;
use App\Notifications\TenantInviteNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TenantInviteService
{
    /**
     * Create a new tenant invite.
     */
    public function createInvite(array $data, User $invitedBy): TenantInvite
    {
        return TenantInvite::create([
            'property_id' => $data['property_id'],
            'unit_id' => $data['unit_id'],
            'invited_by_user_id' => $invitedBy->id,
            'email' => $data['email'],
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'rent_amount' => $data['rent_amount'],
            'deposit_amount' => $data['deposit_amount'] ?? null,
            'start_date' => $data['start_date'],
            'status' => 'pending',
            'expires_at' => now()->addDays(7),
        ]);
    }

    /**
     * Send the invite notification.
     */
    public function sendInviteNotification(TenantInvite $invite): void
    {
        // Create a temporary notifiable for the email
        $notifiable = new \Illuminate\Notifications\AnonymousNotifiable();
        $notifiable->route('mail', $invite->email);

        $notifiable->notify(new TenantInviteNotification($invite));
    }

    /**
     * Resend an existing invite.
     */
    public function resendInvite(TenantInvite $invite): TenantInvite
    {
        // Extend expiration
        $invite->update([
            'expires_at' => now()->addDays(7),
            'status' => 'pending',
        ]);

        $this->sendInviteNotification($invite);

        return $invite;
    }

    /**
     * Accept an invite and create the tenant account and tenancy.
     */
    public function acceptInvite(TenantInvite $invite, string $password): User
    {
        return DB::transaction(function () use ($invite, $password) {
            // Check if user already exists
            $user = User::where('email', $invite->email)->first();

            if (!$user) {
                [$firstName, $lastName] = $this->splitName($invite->name);
                $fullName = trim($invite->name);

                // Create new user
                $user = User::create([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'name' => $fullName,
                    'email' => $invite->email,
                    'phone' => $invite->phone,
                    'password' => Hash::make($password),
                    'status' => 'active',
                ]);

                $user->assignRole('tenant');
            }

            // Create tenancy
            Tenancy::create([
                'unit_id' => $invite->unit_id,
                'tenant_user_id' => $user->id,
                'start_date' => $invite->start_date,
                'rent_amount' => $invite->rent_amount,
                'deposit_amount' => $invite->deposit_amount ?? 0,
                'status' => 'active',
            ]);

            // Mark invite as accepted
            $invite->markAsAccepted();

            return $user;
        });
    }

    /**
     * Cancel an invite.
     */
    public function cancelInvite(TenantInvite $invite): void
    {
        $invite->cancel();
    }

    /**
     * Check and mark expired invites.
     */
    public function expireOldInvites(): int
    {
        return TenantInvite::where('status', 'pending')
            ->where('expires_at', '<', now())
            ->update(['status' => 'expired']);
    }

    /**
     * Get invite by token.
     */
    public function getInviteByToken(string $token): ?TenantInvite
    {
        return TenantInvite::where('token', $token)->first();
    }

    /**
     * Split a full name into first and last.
     *
     * @return array{0: string|null, 1: string|null}
     */
    protected function splitName(?string $name): array
    {
        $name = trim((string) $name);
        if ($name === '') {
            return [null, null];
        }

        $parts = preg_split('/\s+/', $name, 2);
        return [$parts[0] ?? null, $parts[1] ?? null];
    }
}
