<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaterMeter extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'unit_id',
        'meter_serial',
        'status',
    ];

    protected $casts = [
        'property_id' => 'integer',
        'unit_id' => 'integer',
    ];

    /**
     * Relationship to property
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Relationship to unit (optional)
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Relationship to water readings
     */
    public function readings()
    {
        return $this->hasMany(WaterReading::class);
    }
}
