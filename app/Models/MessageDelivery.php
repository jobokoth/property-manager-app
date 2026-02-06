<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageDelivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'message_id',
        'recipient_user_id',
        'status',
        'sent_at',
        'read_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    /**
     * Get the message this delivery belongs to.
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    /**
     * Get the recipient user.
     */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    /**
     * Mark as sent.
     */
    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    /**
     * Mark as read.
     */
    public function markAsRead(): void
    {
        $this->update([
            'status' => 'read',
            'read_at' => now(),
        ]);
    }

    /**
     * Scope to get unread deliveries for a user.
     */
    public function scopeUnreadForUser($query, $userId)
    {
        return $query->where('recipient_user_id', $userId)
                     ->where('status', '!=', 'read');
    }

    /**
     * Scope to get deliveries for a user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('recipient_user_id', $userId);
    }
}
