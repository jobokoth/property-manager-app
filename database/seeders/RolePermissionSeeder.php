<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions for Properties module
        Permission::firstOrCreate(['name' => 'properties.view']);
        Permission::firstOrCreate(['name' => 'properties.create']);
        Permission::firstOrCreate(['name' => 'properties.update']);
        Permission::firstOrCreate(['name' => 'properties.delete']);
        Permission::firstOrCreate(['name' => 'properties.manage_units']);
        Permission::firstOrCreate(['name' => 'properties.manage_tenants']);
        Permission::firstOrCreate(['name' => 'properties.manage_caretakers']);

        // Create permissions for Payments module
        Permission::firstOrCreate(['name' => 'payments.view']);
        Permission::firstOrCreate(['name' => 'payments.submit_mpesa']);
        Permission::firstOrCreate(['name' => 'payments.ingest_mpesa']);
        Permission::firstOrCreate(['name' => 'payments.allocate']);
        Permission::firstOrCreate(['name' => 'statements.generate']);

        // Create permissions for Service Requests module
        Permission::firstOrCreate(['name' => 'requests.create']);
        Permission::firstOrCreate(['name' => 'requests.view']);
        Permission::firstOrCreate(['name' => 'requests.assign_vendor']);
        Permission::firstOrCreate(['name' => 'requests.update_status']);

        // Create permissions for Vendors module
        Permission::firstOrCreate(['name' => 'vendors.view_jobs']);
        Permission::firstOrCreate(['name' => 'vendors.submit_quote']);
        Permission::firstOrCreate(['name' => 'vendors.schedule_work']);
        Permission::firstOrCreate(['name' => 'vendors.submit_invoice']);
        Permission::firstOrCreate(['name' => 'vendors.confirm_payment']);

        // Create permissions for Notifications module
        Permission::firstOrCreate(['name' => 'notifications.send_individual']);
        Permission::firstOrCreate(['name' => 'notifications.send_group']);
        Permission::firstOrCreate(['name' => 'notifications.manage_templates']);

        // Create permissions for Water module
        Permission::firstOrCreate(['name' => 'water.readings.create']);
        Permission::firstOrCreate(['name' => 'water.readings.view']);
        Permission::firstOrCreate(['name' => 'water.billing.configure']);

        // Create permissions for Admin module
        Permission::firstOrCreate(['name' => 'admin.users.view']);
        Permission::firstOrCreate(['name' => 'admin.users.create']);
        Permission::firstOrCreate(['name' => 'admin.users.update']);
        Permission::firstOrCreate(['name' => 'admin.users.delete']);
        Permission::firstOrCreate(['name' => 'admin.users.toggle_status']);
        Permission::firstOrCreate(['name' => 'admin.roles.assign']);

        // Create roles
        $tenantRole = Role::firstOrCreate(['name' => 'tenant']);
        $propertyManagerRole = Role::firstOrCreate(['name' => 'property_manager']);
        $ownerRole = Role::firstOrCreate(['name' => 'owner']);
        $vendorRole = Role::firstOrCreate(['name' => 'vendor']);
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin']);

        // Assign permissions to Tenant role
        $tenantRole->syncPermissions([
            'requests.create',
            'requests.view',
            'payments.submit_mpesa',
            'statements.generate'
        ]);

        // Assign permissions to Property Manager role
        // Note: Property managers cannot create properties, only owners can
        $propertyManagerRole->syncPermissions([
            'properties.view',
            'properties.update',
            'properties.manage_units',
            'properties.manage_tenants',
            'properties.manage_caretakers',
            'payments.view',
            'payments.ingest_mpesa',
            'payments.allocate',
            'requests.view',
            'requests.assign_vendor',
            'requests.update_status',
            'notifications.send_individual',
            'notifications.send_group',
            'water.readings.create',
            'water.readings.view'
        ]);

        // Assign permissions to Owner role
        $ownerRole->syncPermissions([
            'properties.view',
            'properties.create',
            'properties.update',
            'properties.manage_units',
            'properties.manage_tenants',
            'properties.manage_caretakers',
            'payments.view',
            'payments.ingest_mpesa',
            'payments.allocate',
            'statements.generate',
            'requests.view',
            'requests.assign_vendor',
            'requests.update_status',
            'notifications.send_individual',
            'notifications.send_group',
            'water.readings.create',
            'water.readings.view',
            'water.billing.configure'
        ]);

        // Assign permissions to Vendor role
        $vendorRole->syncPermissions([
            'vendors.view_jobs',
            'vendors.submit_quote',
            'vendors.schedule_work',
            'vendors.submit_invoice',
            'vendors.confirm_payment'
        ]);

        // Assign all permissions to Super Admin role
        $superAdminRole->syncPermissions(Permission::all());
    }
}
