<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'tenancy_id',
        'payer_user_id',
        'source',
        'amount',
        'paid_at',
        'reference',
        'status',
    ];

    protected $casts = [
        'property_id' => 'integer',
        'tenancy_id' => 'integer',
        'payer_user_id' => 'integer',
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    /**
     * Relationship to property
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Relationship to tenancy
     */
    public function tenancy()
    {
        return $this->belongsTo(Tenancy::class);
    }

    /**
     * Relationship to payer user
     */
    public function payer()
    {
        return $this->belongsTo(User::class, 'payer_user_id');
    }

    /**
     * Relationship to allocations
     */
    public function allocations()
    {
        return $this->hasMany(Allocation::class);
    }

    /**
     * Relationship to Mpesa messages
     */
    public function mpesaMessages()
    {
        return $this->hasMany(MpesaMessage::class);
    }
}
