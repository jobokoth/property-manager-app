<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TenantInvite extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'unit_id',
        'invited_by_user_id',
        'email',
        'name',
        'phone',
        'token',
        'rent_amount',
        'deposit_amount',
        'start_date',
        'status',
        'expires_at',
        'accepted_at',
    ];

    protected $casts = [
        'property_id' => 'integer',
        'unit_id' => 'integer',
        'invited_by_user_id' => 'integer',
        'rent_amount' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'start_date' => 'date',
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invite) {
            if (empty($invite->token)) {
                $invite->token = Str::random(64);
            }
            if (empty($invite->expires_at)) {
                $invite->expires_at = now()->addDays(7);
            }
        });
    }

    /**
     * Relationship to property
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Relationship to unit
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Relationship to user who sent the invite
     */
    public function invitedBy()
    {
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }

    /**
     * Check if invite has expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if invite is still valid
     */
    public function isValid(): bool
    {
        return $this->status === 'pending' && !$this->isExpired();
    }

    /**
     * Mark invite as accepted
     */
    public function markAsAccepted(): void
    {
        $this->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);
    }

    /**
     * Mark invite as cancelled
     */
    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    /**
     * Scope for pending invites
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for valid invites (pending and not expired)
     */
    public function scopeValid($query)
    {
        return $query->where('status', 'pending')
                     ->where('expires_at', '>', now());
    }

    /**
     * Get the signed URL for this invite
     */
    public function getAcceptUrl(): string
    {
        return url()->signedRoute('tenant-invites.accept', ['invite' => $this]);
    }
}
