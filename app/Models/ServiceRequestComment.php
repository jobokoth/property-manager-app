<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequestComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_request_id',
        'user_id',
        'comment',
        'type',
        'is_internal',
    ];

    protected $casts = [
        'service_request_id' => 'integer',
        'user_id' => 'integer',
        'is_internal' => 'boolean',
    ];

    /**
     * Relationship to service request
     */
    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    /**
     * Relationship to user who made the comment
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for internal comments
     */
    public function scopeInternal($query)
    {
        return $query->where('is_internal', true);
    }

    /**
     * Scope for public comments
     */
    public function scopePublic($query)
    {
        return $query->where('is_internal', false);
    }

    /**
     * Get the type badge class
     */
    public function getTypeBadgeClass(): string
    {
        return match($this->type) {
            'clarification' => 'bg-info',
            'status_update' => 'bg-warning',
            'internal' => 'bg-secondary',
            default => 'bg-primary',
        };
    }
}
