<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_request_id',
        'service_quote_id',
        'vendor_user_id',
        'amount',
        'description',
        'invoice_number',
        'status',
        'approved_by_user_id',
        'approved_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    /**
     * Boot method to generate invoice number
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = 'INV-' . date('Ymd') . '-' . str_pad(static::count() + 1, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    /**
     * Relationship to service request
     */
    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    /**
     * Relationship to service quote
     */
    public function quote()
    {
        return $this->belongsTo(ServiceQuote::class, 'service_quote_id');
    }

    /**
     * Relationship to vendor user
     */
    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_user_id');
    }

    /**
     * Relationship to approver user
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    /**
     * Relationship to vendor payments
     */
    public function payments()
    {
        return $this->hasMany(VendorPayment::class);
    }

    /**
     * Check if invoice is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if invoice is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if invoice is paid
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }
}
