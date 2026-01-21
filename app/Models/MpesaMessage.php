<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MpesaMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'tenant_user_id',
        'raw_text',
        'sender_msisdn',
        'amount',
        'trans_id',
        'trans_time',
        'parsed_json',
        'status',
        'uploaded_by_user_id',
    ];

    protected $casts = [
        'property_id' => 'integer',
        'tenant_user_id' => 'integer',
        'amount' => 'decimal:2',
        'trans_time' => 'datetime',
        'parsed_json' => 'array',
        'uploaded_by_user_id' => 'integer',
    ];

    /**
     * Relationship to property
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Relationship to tenant user
     */
    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_user_id');
    }

    /**
     * Relationship to user who uploaded the message
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }

    /**
     * Relationship to payments
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
