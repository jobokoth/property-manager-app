<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'label',
        'floor',
        'bedrooms',
        'rent_amount',
        'water_rate_mode',
        'status',
    ];

    protected $casts = [
        'property_id' => 'integer',
        'floor' => 'integer',
        'bedrooms' => 'integer',
        'rent_amount' => 'decimal:2',
    ];

    /**
     * Relationship to property
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Relationship to tenancies
     */
    public function tenancies()
    {
        return $this->hasMany(Tenancy::class);
    }

    /**
     * Relationship to service requests
     */
    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class);
    }

    /**
     * Relationship to water meters
     */
    public function waterMeters()
    {
        return $this->hasMany(WaterMeter::class);
    }

    /**
     * Get the active tenancy for this unit
     */
    public function activeTenancy()
    {
        return $this->hasOne(Tenancy::class)->where('status', 'active');
    }
}
