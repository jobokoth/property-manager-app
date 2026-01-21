<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_invoice_id',
        'vendor_user_id',
        'amount',
        'payment_method',
        'reference',
        'status',
        'paid_by_user_id',
        'paid_at',
        'confirmed_at',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'confirmed_at' => 'datetime',
    ];

    /**
     * Relationship to service invoice
     */
    public function invoice()
    {
        return $this->belongsTo(ServiceInvoice::class, 'service_invoice_id');
    }

    /**
     * Relationship to vendor user
     */
    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_user_id');
    }

    /**
     * Relationship to payer user
     */
    public function paidBy()
    {
        return $this->belongsTo(User::class, 'paid_by_user_id');
    }

    /**
     * Check if payment is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if payment is paid
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Check if payment is confirmed by vendor
     */
    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }
}
