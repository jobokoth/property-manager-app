<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'sender_user_id',
        'audience_type',
        'audience_payload',
        'subject',
        'body',
    ];

    protected $casts = [
        'audience_payload' => 'array',
    ];

    /**
     * Get the property this message belongs to.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Get the user who sent this message.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }

    /**
     * Get all deliveries for this message.
     */
    public function deliveries(): HasMany
    {
        return $this->hasMany(MessageDelivery::class);
    }

    /**
     * Scope to get messages for a specific property.
     */
    public function scopeForProperty($query, $propertyId)
    {
        return $query->where('property_id', $propertyId);
    }

    /**
     * Check if this is a group message.
     */
    public function isGroupMessage(): bool
    {
        return $this->audience_type === 'group';
    }

    /**
     * Check if this is an individual message.
     */
    public function isIndividualMessage(): bool
    {
        return $this->audience_type === 'individual';
    }
}
