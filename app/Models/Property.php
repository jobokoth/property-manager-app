<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location',
        'owner_user_id',
        'status',
    ];

    protected $casts = [
        'owner_user_id' => 'integer',
    ];

    /**
     * Relationship to the owner user
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    /**
     * Relationship to units
     */
    public function units()
    {
        return $this->hasMany(Unit::class);
    }

    /**
     * Relationship to tenancies through units
     */
    public function tenancies()
    {
        return $this->hasManyThrough(Tenancy::class, Unit::class);
    }

    /**
     * Relationship to rent rules
     */
    public function rentRules()
    {
        return $this->hasMany(RentRule::class);
    }

    /**
     * Relationship to Mpesa messages
     */
    public function mpesaMessages()
    {
        return $this->hasMany(MpesaMessage::class);
    }

    /**
     * Relationship to payments
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Relationship to service requests
     */
    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class);
    }

    /**
     * Relationship to water meters
     */
    public function waterMeters()
    {
        return $this->hasMany(WaterMeter::class);
    }

    /**
     * Relationship to notification templates
     */
    public function notificationTemplates()
    {
        return $this->hasMany(NotificationTemplate::class);
    }

    /**
     * Relationship to users through property_user pivot table
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'property_user')
                    ->withPivot('relationship_type', 'is_primary')
                    ->withTimestamps();
    }

    /**
     * Relationship to property managers
     */
    public function managers()
    {
        return $this->belongsToMany(User::class, 'property_user')
                    ->withPivot('relationship_type', 'is_primary')
                    ->withTimestamps()
                    ->wherePivot('relationship_type', 'manager');
    }

    /**
     * Relationship to vendors assigned to this property
     */
    public function vendors()
    {
        return $this->belongsToMany(User::class, 'property_user')
                    ->withPivot('relationship_type', 'is_primary')
                    ->withTimestamps()
                    ->wherePivot('relationship_type', 'vendor');
    }

    /**
     * Relationship to caretakers
     */
    public function caretakers()
    {
        return $this->belongsToMany(User::class, 'property_user')
                    ->withPivot('relationship_type', 'is_primary')
                    ->withTimestamps()
                    ->wherePivot('relationship_type', 'caretaker');
    }
}
