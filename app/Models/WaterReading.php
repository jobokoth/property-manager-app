<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaterReading extends Model
{
    use HasFactory;

    protected $fillable = [
        'water_meter_id',
        'reading_date',
        'reading_value',
        'captured_by_user_id',
        'notes',
    ];

    protected $casts = [
        'water_meter_id' => 'integer',
        'reading_date' => 'date',
        'reading_value' => 'integer',
        'captured_by_user_id' => 'integer',
    ];

    /**
     * Relationship to water meter
     */
    public function waterMeter()
    {
        return $this->belongsTo(WaterMeter::class);
    }

    /**
     * Relationship to user who captured the reading
     */
    public function capturedBy()
    {
        return $this->belongsTo(User::class, 'captured_by_user_id');
    }
}
