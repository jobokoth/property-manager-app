<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaterBill extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenancy_id',
        'period_month',
        'units_consumed',
        'rate_per_unit',
        'amount',
        'status',
    ];

    protected $casts = [
        'units_consumed' => 'decimal:2',
        'rate_per_unit' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    /**
     * Get the tenancy this bill belongs to.
     */
    public function tenancy(): BelongsTo
    {
        return $this->belongsTo(Tenancy::class);
    }

    /**
     * Scope to get bills for a specific period.
     */
    public function scopeForPeriod($query, string $periodMonth)
    {
        return $query->where('period_month', $periodMonth);
    }

    /**
     * Scope to get pending bills.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get unpaid bills (pending or partial).
     */
    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', ['pending', 'partial']);
    }

    /**
     * Get the formatted period (e.g., "January 2026").
     */
    public function getFormattedPeriodAttribute(): string
    {
        return \Carbon\Carbon::createFromFormat('Y-m', $this->period_month)->format('F Y');
    }

    /**
     * Calculate bill from readings.
     */
    public static function calculateFromReadings(float $previousReading, float $currentReading, float $ratePerUnit): array
    {
        $unitsConsumed = max(0, $currentReading - $previousReading);
        $amount = $unitsConsumed * $ratePerUnit;

        return [
            'units_consumed' => $unitsConsumed,
            'rate_per_unit' => $ratePerUnit,
            'amount' => $amount,
        ];
    }
}
