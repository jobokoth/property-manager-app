<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Allocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'allocation_type',
        'amount',
        'period_month',
        'notes',
    ];

    protected $casts = [
        'payment_id' => 'integer',
        'amount' => 'decimal:2',
    ];

    /**
     * Relationship to payment
     */
    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
