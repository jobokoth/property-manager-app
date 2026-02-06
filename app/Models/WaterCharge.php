<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaterCharge extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenancy_id',
        'period_month',
        'amount',
        'source',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Get the tenancy this charge belongs to.
     */
    public function tenancy(): BelongsTo
    {
        return $this->belongsTo(Tenancy::class);
    }

    /**
     * Scope to get charges for a specific period.
     */
    public function scopeForPeriod($query, string $periodMonth)
    {
        return $query->where('period_month', $periodMonth);
    }

    /**
     * Scope to get charges for a specific tenancy.
     */
    public function scopeForTenancy($query, $tenancyId)
    {
        return $query->where('tenancy_id', $tenancyId);
    }

    /**
     * Get the formatted period (e.g., "January 2026").
     */
    public function getFormattedPeriodAttribute(): string
    {
        return \Carbon\Carbon::createFromFormat('Y-m', $this->period_month)->format('F Y');
    }
}
