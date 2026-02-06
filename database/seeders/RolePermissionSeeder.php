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
        Permission::firstOrCreate(['name' => 'requests.add_comment']);
        Permission::firstOrCreate(['name' => 'requests.view_internal_comments']);

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

        // Create permissions for Caretaker Tasks module
        Permission::firstOrCreate(['name' => 'caretaker_tasks.view']);
        Permission::firstOrCreate(['name' => 'caretaker_tasks.create']);
        Permission::firstOrCreate(['name' => 'caretaker_tasks.update']);
        Permission::firstOrCreate(['name' => 'caretaker_tasks.delete']);
        Permission::firstOrCreate(['name' => 'caretaker_tasks.complete']);

        // Create permissions for Tenant Invites module
        Permission::firstOrCreate(['name' => 'invites.create']);
        Permission::firstOrCreate(['name' => 'invites.view']);
        Permission::firstOrCreate(['name' => 'invites.cancel']);

        // Create permissions for Account Management
        Permission::firstOrCreate(['name' => 'tenants.create']);
        Permission::firstOrCreate(['name' => 'tenants.update']);
        Permission::firstOrCreate(['name' => 'vendors.create']);
        Permission::firstOrCreate(['name' => 'vendors.update']);

        // Create permissions for Messaging
        Permission::firstOrCreate(['name' => 'messages.send_to_all_roles']);
        Permission::firstOrCreate(['name' => 'messages.send_to_tenants']);
        Permission::firstOrCreate(['name' => 'messages.send_to_pm']);

        // Create roles
        $tenantRole = Role::firstOrCreate(['name' => 'tenant']);
        $propertyManagerRole = Role::firstOrCreate(['name' => 'property_manager']);
        $ownerRole = Role::firstOrCreate(['name' => 'owner']);
        $vendorRole = Role::firstOrCreate(['name' => 'vendor']);
        $caretakerRole = Role::firstOrCreate(['name' => 'caretaker']);
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin']);

        // Assign permissions to Tenant role
        $tenantRole->syncPermissions([
            'requests.create',
            'requests.view',
            'requests.add_comment',
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
            'requests.add_comment',
            'requests.view_internal_comments',
            'notifications.send_individual',
            'notifications.send_group',
            'water.readings.create',
            'water.readings.view',
            // Caretaker task management
            'caretaker_tasks.view',
            'caretaker_tasks.create',
            'caretaker_tasks.update',
            'caretaker_tasks.delete',
            // Tenant invites
            'invites.create',
            'invites.view',
            'invites.cancel',
            // Account management
            'tenants.create',
            'tenants.update',
            'vendors.create',
            'vendors.update',
            // Messaging
            'messages.send_to_all_roles',
        ]);

        // Assign permissions to Owner role
        // Same as Property Manager but scoped to owned properties only
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
            'requests.add_comment',
            'requests.view_internal_comments',
            'notifications.send_individual',
            'notifications.send_group',
            'water.readings.create',
            'water.readings.view',
            'water.billing.configure',
            // Caretaker task management
            'caretaker_tasks.view',
            'caretaker_tasks.create',
            'caretaker_tasks.update',
            'caretaker_tasks.delete',
            // Tenant invites
            'invites.create',
            'invites.view',
            'invites.cancel',
            // Account management
            'tenants.create',
            'tenants.update',
            'vendors.create',
            'vendors.update',
            // Messaging
            'messages.send_to_all_roles',
        ]);

        // Assign permissions to Vendor role
        $vendorRole->syncPermissions([
            'vendors.view_jobs',
            'vendors.submit_quote',
            'vendors.schedule_work',
            'vendors.submit_invoice',
            'vendors.confirm_payment',
            'requests.view',  // Can view assigned service requests
            'requests.add_comment',
            'messages.send_to_pm',
        ]);

        // Assign permissions to Caretaker role
        $caretakerRole->syncPermissions([
            'requests.view',
            'requests.add_comment',
            'caretaker_tasks.view',
            'caretaker_tasks.update',
            'caretaker_tasks.complete',
            'messages.send_to_tenants',
            'messages.send_to_pm',
            'notifications.send_individual',
        ]);

        // Assign all permissions to Super Admin role
        $superAdminRole->syncPermissions(Permission::all());
    }
}
