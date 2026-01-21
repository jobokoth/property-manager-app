<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'unit_id',
        'due_day',
        'grace_days',
        'late_fee_mode',
        'late_fee_value',
    ];

    protected $casts = [
        'property_id' => 'integer',
        'unit_id' => 'integer',
        'due_day' => 'integer',
        'grace_days' => 'integer',
        'late_fee_value' => 'decimal:2',
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
}
