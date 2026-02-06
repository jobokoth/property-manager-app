<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_request_id',
        'vendor_user_id',
        'scheduled_start',
        'scheduled_end',
        'notes',
        'status',
    ];

    protected $casts = [
        'scheduled_start' => 'datetime',
        'scheduled_end' => 'datetime',
    ];

    /**
     * Get the service request this schedule belongs to.
     */
    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    /**
     * Get the vendor user.
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_user_id');
    }

    /**
     * Scope to get upcoming schedules.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_start', '>=', now())
                     ->whereIn('status', ['scheduled']);
    }

    /**
     * Scope to get schedules for a vendor.
     */
    public function scopeForVendor($query, $vendorId)
    {
        return $query->where('vendor_user_id', $vendorId);
    }

    /**
     * Scope to get active schedules (not cancelled or completed).
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['scheduled', 'in_progress']);
    }

    /**
     * Mark as in progress.
     */
    public function markAsInProgress(): void
    {
        $this->update(['status' => 'in_progress']);
    }

    /**
     * Mark as completed.
     */
    public function markAsCompleted(): void
    {
        $this->update(['status' => 'completed']);
    }

    /**
     * Mark as cancelled.
     */
    public function markAsCancelled(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    /**
     * Get formatted schedule time.
     */
    public function getFormattedScheduleAttribute(): string
    {
        $start = $this->scheduled_start->format('M d, Y h:i A');
        if ($this->scheduled_end) {
            $end = $this->scheduled_end->format('h:i A');
            return "{$start} - {$end}";
        }
        return $start;
    }
}
