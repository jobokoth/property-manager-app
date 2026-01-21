<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'status',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Relationship to properties through property_user pivot table
     */
    public function properties()
    {
        return $this->belongsToMany(\App\Models\Property::class, 'property_user')
                    ->withPivot('relationship_type', 'is_primary')
                    ->withTimestamps();
    }

    /**
     * Properties managed by this user (as property manager)
     */
    public function managedProperties()
    {
        return $this->belongsToMany(\App\Models\Property::class, 'property_user')
                    ->withPivot('relationship_type', 'is_primary')
                    ->withTimestamps()
                    ->wherePivot('relationship_type', 'manager');
    }

    /**
     * Properties owned by this user
     */
    public function ownedProperties()
    {
        return $this->hasMany(\App\Models\Property::class, 'owner_user_id');
    }

    /**
     * Service requests assigned to this vendor
     */
    public function assignedServiceRequests()
    {
        return $this->hasMany(\App\Models\ServiceRequest::class, 'assigned_vendor_id');
    }

    /**
     * Quotes submitted by this vendor
     */
    public function submittedQuotes()
    {
        return $this->hasMany(\App\Models\ServiceQuote::class, 'vendor_user_id');
    }

    /**
     * Invoices submitted by this vendor
     */
    public function submittedInvoices()
    {
        return $this->hasMany(\App\Models\ServiceInvoice::class, 'vendor_user_id');
    }

    /**
     * Payments received by this vendor
     */
    public function vendorPayments()
    {
        return $this->hasMany(\App\Models\VendorPayment::class, 'vendor_user_id');
    }

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
