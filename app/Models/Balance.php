<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Balance extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenancy_id',
        'period_month',
        'rent_due',
        'rent_paid',
        'water_due',
        'water_paid',
        'carried_forward',
    ];

    protected $casts = [
        'tenancy_id' => 'integer',
        'rent_due' => 'decimal:2',
        'rent_paid' => 'decimal:2',
        'water_due' => 'decimal:2',
        'water_paid' => 'decimal:2',
        'carried_forward' => 'decimal:2',
    ];

    /**
     * Relationship to tenancy
     */
    public function tenancy()
    {
        return $this->belongsTo(Tenancy::class);
    }

    /**
     * Calculate the total balance (positive means owed, negative means credit)
     */
    public function getTotalBalanceAttribute()
    {
        $totalOwed = ($this->rent_due + $this->water_due) - ($this->rent_paid + $this->water_paid);
        return $totalOwed + $this->carried_forward;
    }
}
