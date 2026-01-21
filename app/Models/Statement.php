<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Statement extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenancy_id',
        'period_month',
        'generated_at',
        'totals_json',
        'pdf_url',
        'status',
    ];

    protected $casts = [
        'tenancy_id' => 'integer',
        'generated_at' => 'datetime',
        'totals_json' => 'array',
    ];

    /**
     * Relationship to tenancy
     */
    public function tenancy()
    {
        return $this->belongsTo(Tenancy::class);
    }
}
