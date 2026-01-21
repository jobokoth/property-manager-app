<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequestMedia extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_request_id',
        'type',
        'cloudinary_public_id',
        'url',
        'bytes',
        'format',
        'duration',
    ];

    protected $casts = [
        'service_request_id' => 'integer',
        'bytes' => 'integer',
        'duration' => 'integer',
    ];

    /**
     * Relationship to service request
     */
    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }
}
