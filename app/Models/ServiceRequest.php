<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'unit_id',
        'tenancy_id',
        'tenant_user_id',
        'category',
        'title',
        'description',
        'priority',
        'status',
        'assigned_vendor_id',
        'assigned_at',
        'assignment_notes',
    ];

    protected $casts = [
        'property_id' => 'integer',
        'unit_id' => 'integer',
        'tenancy_id' => 'integer',
        'tenant_user_id' => 'integer',
        'assigned_vendor_id' => 'integer',
        'assigned_at' => 'datetime',
    ];

    /**
     * Relationship to property
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Relationship to unit
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Relationship to tenancy
     */
    public function tenancy()
    {
        return $this->belongsTo(Tenancy::class);
    }

    /**
     * Relationship to tenant user
     */
    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_user_id');
    }

    /**
     * Relationship to service request media
     */
    public function media()
    {
        return $this->hasMany(ServiceRequestMedia::class);
    }

    /**
     * Relationship to assigned vendor
     */
    public function assignedVendor()
    {
        return $this->belongsTo(User::class, 'assigned_vendor_id');
    }

    /**
     * Relationship to quotes
     */
    public function quotes()
    {
        return $this->hasMany(ServiceQuote::class);
    }

    /**
     * Relationship to invoices
     */
    public function invoices()
    {
        return $this->hasMany(ServiceInvoice::class);
    }

    /**
     * Get the latest approved quote
     */
    public function approvedQuote()
    {
        return $this->hasOne(ServiceQuote::class)->where('status', 'approved')->latest();
    }

    /**
     * Check if a vendor is assigned
     */
    public function hasVendorAssigned(): bool
    {
        return !is_null($this->assigned_vendor_id);
    }
}
