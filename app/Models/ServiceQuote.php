<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceQuote extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_request_id',
        'vendor_user_id',
        'amount',
        'description',
        'estimated_days',
        'status',
        'reviewed_by_user_id',
        'reviewed_at',
        'review_notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'estimated_days' => 'integer',
        'reviewed_at' => 'datetime',
    ];

    /**
     * Relationship to service request
     */
    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    /**
     * Relationship to vendor user
     */
    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_user_id');
    }

    /**
     * Relationship to reviewer user
     */
    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    /**
     * Relationship to invoice (if quote was converted to invoice)
     */
    public function invoice()
    {
        return $this->hasOne(ServiceInvoice::class);
    }

    /**
     * Check if quote is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if quote is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if quote is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}
