<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenancy extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_id',
        'tenant_user_id',
        'start_date',
        'end_date',
        'rent_amount',
        'deposit_amount',
        'status',
    ];

    protected $casts = [
        'unit_id' => 'integer',
        'tenant_user_id' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'rent_amount' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
    ];

    /**
     * Relationship to unit
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Relationship to tenant user
     */
    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_user_id');
    }

    /**
     * Relationship to property through unit
     */
    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    /**
     * Relationship to payments
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Relationship to balances
     */
    public function balances()
    {
        return $this->hasMany(Balance::class);
    }

    /**
     * Relationship to service requests
     */
    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class);
    }

    /**
     * Relationship to statements
     */
    public function statements()
    {
        return $this->hasMany(Statement::class);
    }

    /**
     * Check if tenancy is currently active
     */
    public function isActive()
    {
        return $this->status === 'active' && (!$this->end_date || $this->end_date >= now());
    }
}
