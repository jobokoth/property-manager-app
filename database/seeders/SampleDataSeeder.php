<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Property;
use App\Models\Unit;
use App\Models\Tenancy;
use App\Models\Payment;
use App\Models\ServiceRequest;
use Illuminate\Support\Facades\Hash;

class SampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Super Admin
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@propertymanager.com',
            'password' => Hash::make('password'),
            'phone' => '+254712345678',
        ]);
        $superAdmin->assignRole('super_admin');

        // Create Property Manager
        $propertyManager = User::create([
            'name' => 'Property Manager',
            'email' => 'manager@propertymanager.com',
            'password' => Hash::make('password'),
            'phone' => '+254712345679',
        ]);
        $propertyManager->assignRole('property_manager');

        // Create Owner
        $owner = User::create([
            'name' => 'Property Owner',
            'email' => 'owner@propertymanager.com',
            'password' => Hash::make('password'),
            'phone' => '+254712345680',
        ]);
        $owner->assignRole('owner');

        // Create Tenant
        $tenant = User::create([
            'name' => 'John Tenant',
            'email' => 'tenant@propertymanager.com',
            'password' => Hash::make('password'),
            'phone' => '+254712345681',
        ]);
        $tenant->assignRole('tenant');

        // Create Vendor
        $vendor = User::create([
            'name' => 'Fixit Services',
            'email' => 'vendor@propertymanager.com',
            'password' => Hash::make('password'),
            'phone' => '+254712345682',
        ]);
        $vendor->assignRole('vendor');

        // Create sample properties
        $property1 = Property::create([
            'name' => 'Sunshine Apartments',
            'location' => 'Nairobi, Kenya - Along Mombasa Road',
            'owner_user_id' => $owner->id,
            'status' => 'active'
        ]);

        $property2 = Property::create([
            'name' => 'Green Valley Complex',
            'location' => 'Nairobi, Kenya - Westlands Area',
            'owner_user_id' => $owner->id,
            'status' => 'active'
        ]);

        // Create sample units
        $unit1 = Unit::create([
            'property_id' => $property1->id,
            'label' => 'A101',
            'floor' => 1,
            'bedrooms' => 2,
            'rent_amount' => 35000,
            'water_rate_mode' => 'per_unit',
            'status' => 'occupied'
        ]);

        $unit2 = Unit::create([
            'property_id' => $property1->id,
            'label' => 'A102',
            'floor' => 1,
            'bedrooms' => 1,
            'rent_amount' => 25000,
            'water_rate_mode' => 'per_unit',
            'status' => 'available'
        ]);

        $unit3 = Unit::create([
            'property_id' => $property2->id,
            'label' => 'B201',
            'floor' => 2,
            'bedrooms' => 3,
            'rent_amount' => 45000,
            'water_rate_mode' => 'per_meter',
            'status' => 'occupied'
        ]);

        // Create sample tenancies
        $tenancy1 = Tenancy::create([
            'unit_id' => $unit1->id,
            'tenant_user_id' => $tenant->id,
            'start_date' => now()->subMonths(6),
            'end_date' => now()->addYear(),
            'rent_amount' => 35000,
            'deposit_amount' => 35000,
            'status' => 'active'
        ]);

        $tenancy2 = Tenancy::create([
            'unit_id' => $unit3->id,
            'tenant_user_id' => $tenant->id,
            'start_date' => now()->subMonths(3),
            'end_date' => now()->addYear(),
            'rent_amount' => 45000,
            'deposit_amount' => 45000,
            'status' => 'active'
        ]);

        // Create sample payments
        Payment::create([
            'property_id' => $property1->id,
            'tenancy_id' => $tenancy1->id,
            'payer_user_id' => $tenant->id,
            'source' => 'mpesa',
            'amount' => 35000,
            'paid_at' => now()->subMonth(),
            'reference' => 'MPESA-001',
            'status' => 'confirmed'
        ]);

        Payment::create([
            'property_id' => $property1->id,
            'tenancy_id' => $tenancy1->id,
            'payer_user_id' => $tenant->id,
            'source' => 'mpesa',
            'amount' => 35000,
            'paid_at' => now()->subDays(15),
            'reference' => 'MPESA-002',
            'status' => 'confirmed'
        ]);

        Payment::create([
            'property_id' => $property2->id,
            'tenancy_id' => $tenancy2->id,
            'payer_user_id' => $tenant->id,
            'source' => 'mpesa',
            'amount' => 45000,
            'paid_at' => now()->subMonth(),
            'reference' => 'MPESA-003',
            'status' => 'confirmed'
        ]);

        // Create sample service requests
        ServiceRequest::create([
            'property_id' => $property1->id,
            'unit_id' => $unit1->id,
            'tenancy_id' => $tenancy1->id,
            'tenant_user_id' => $tenant->id,
            'category' => 'plumbing',
            'title' => 'Leaking Faucet in Kitchen',
            'description' => 'The kitchen faucet is leaking continuously and needs repair.',
            'priority' => 'medium',
            'status' => 'open'
        ]);

        ServiceRequest::create([
            'property_id' => $property2->id,
            'unit_id' => $unit3->id,
            'tenancy_id' => $tenancy2->id,
            'tenant_user_id' => $tenant->id,
            'category' => 'electrical',
            'title' => 'Power Outlet Not Working',
            'description' => 'The power outlet in the living room is not working. Need electrician to check.',
            'priority' => 'high',
            'status' => 'in_review'
        ]);

        ServiceRequest::create([
            'property_id' => $property1->id,
            'unit_id' => $unit1->id,
            'tenancy_id' => $tenancy1->id,
            'tenant_user_id' => $tenant->id,
            'category' => 'carpentry',
            'title' => 'Door Lock Replacement',
            'description' => 'The main door lock is broken and needs replacement.',
            'priority' => 'low',
            'status' => 'scheduled'
        ]);
    }
}
