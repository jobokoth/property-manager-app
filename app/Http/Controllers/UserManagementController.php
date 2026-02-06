<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Property;
use App\Models\Tenancy;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    /**
     * Display a listing of tenants.
     */
    public function tenantsIndex(): View
    {
        $user = auth()->user();
        $propertyIds = $this->getUserPropertyIds($user);

        $tenants = User::role('tenant')
            ->whereHas('properties', function ($query) use ($propertyIds) {
                $query->whereIn('properties.id', $propertyIds);
            })
            ->orWhereHas('tenancies', function ($query) use ($propertyIds) {
                $query->whereHas('unit', function ($q) use ($propertyIds) {
                    $q->whereIn('property_id', $propertyIds);
                });
            })
            ->with(['tenancies' => function ($query) use ($propertyIds) {
                $query->whereHas('unit', function ($q) use ($propertyIds) {
                    $q->whereIn('property_id', $propertyIds);
                });
            }])
            ->paginate(15);

        return view('manage.tenants.index', compact('tenants'));
    }

    /**
     * Show the form for creating a new tenant.
     */
    public function tenantsCreate(): View
    {
        $user = auth()->user();
        $properties = $this->getAccessibleProperties($user);

        return view('manage.tenants.create', compact('properties'));
    }

    /**
     * Store a newly created tenant.
     */
    public function tenantsStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $fullName = trim($validated['first_name'] . ' ' . $validated['last_name']);

        $tenant = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'name' => $fullName,
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'status' => 'active',
        ]);

        $tenant->assignRole('tenant');

        return redirect()->route('manage.tenants.index')
                         ->with('success', 'Tenant created successfully. You can now create a tenancy for them.');
    }

    /**
     * Show the form for editing a tenant.
     */
    public function tenantsEdit(User $tenant): View
    {
        if (!$tenant->hasRole('tenant')) {
            abort(404);
        }

        return view('manage.tenants.edit', compact('tenant'));
    }

    /**
     * Update the specified tenant.
     */
    public function tenantsUpdate(Request $request, User $tenant): RedirectResponse
    {
        if (!$tenant->hasRole('tenant')) {
            abort(404);
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $tenant->id,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
            'status' => 'required|in:active,inactive',
        ]);

        $fullName = trim($validated['first_name'] . ' ' . $validated['last_name']);

        $tenant->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'name' => $fullName,
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'status' => $validated['status'],
        ]);

        if (!empty($validated['password'])) {
            $tenant->update(['password' => Hash::make($validated['password'])]);
        }

        return redirect()->route('manage.tenants.index')
                         ->with('success', 'Tenant updated successfully.');
    }

    /**
     * Display a listing of vendors.
     */
    public function vendorsIndex(): View
    {
        $user = auth()->user();
        $propertyIds = $this->getUserPropertyIds($user);

        $vendors = User::role('vendor')
            ->whereHas('properties', function ($query) use ($propertyIds) {
                $query->whereIn('properties.id', $propertyIds);
            })
            ->with(['properties' => function ($query) use ($propertyIds) {
                $query->whereIn('properties.id', $propertyIds);
            }])
            ->paginate(15);

        return view('manage.vendors.index', compact('vendors'));
    }

    /**
     * Show the form for creating a new vendor.
     */
    public function vendorsCreate(): View
    {
        $user = auth()->user();
        $properties = $this->getAccessibleProperties($user);

        return view('manage.vendors.create', compact('properties'));
    }

    /**
     * Store a newly created vendor.
     */
    public function vendorsStore(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'property_ids' => 'required|array|min:1',
            'property_ids.*' => 'exists:properties,id',
        ]);

        // Verify user has access to selected properties
        $allowedPropertyIds = $this->getUserPropertyIds($user);
        foreach ($validated['property_ids'] as $propertyId) {
            if (!in_array($propertyId, $allowedPropertyIds)) {
                return back()->with('error', 'You do not have access to one or more selected properties.');
            }
        }

        $fullName = trim($validated['first_name'] . ' ' . $validated['last_name']);

        $vendor = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'name' => $fullName,
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'status' => 'active',
        ]);

        $vendor->assignRole('vendor');

        // Assign to properties
        foreach ($validated['property_ids'] as $propertyId) {
            $vendor->properties()->attach($propertyId, [
                'relationship_type' => 'vendor',
                'is_primary' => false,
            ]);
        }

        return redirect()->route('manage.vendors.index')
                         ->with('success', 'Vendor created successfully.');
    }

    /**
     * Show the form for editing a vendor.
     */
    public function vendorsEdit(User $vendor): View
    {
        if (!$vendor->hasRole('vendor')) {
            abort(404);
        }

        $user = auth()->user();
        $properties = $this->getAccessibleProperties($user);
        $assignedPropertyIds = $vendor->properties()
            ->wherePivot('relationship_type', 'vendor')
            ->pluck('properties.id')
            ->toArray();

        return view('manage.vendors.edit', compact('vendor', 'properties', 'assignedPropertyIds'));
    }

    /**
     * Update the specified vendor.
     */
    public function vendorsUpdate(Request $request, User $vendor): RedirectResponse
    {
        if (!$vendor->hasRole('vendor')) {
            abort(404);
        }

        $user = auth()->user();

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $vendor->id,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
            'property_ids' => 'required|array|min:1',
            'property_ids.*' => 'exists:properties,id',
            'status' => 'required|in:active,inactive',
        ]);

        // Verify user has access to selected properties
        $allowedPropertyIds = $this->getUserPropertyIds($user);
        foreach ($validated['property_ids'] as $propertyId) {
            if (!in_array($propertyId, $allowedPropertyIds)) {
                return back()->with('error', 'You do not have access to one or more selected properties.');
            }
        }

        $fullName = trim($validated['first_name'] . ' ' . $validated['last_name']);

        $vendor->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'name' => $fullName,
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'status' => $validated['status'],
        ]);

        if (!empty($validated['password'])) {
            $vendor->update(['password' => Hash::make($validated['password'])]);
        }

        // Update property assignments (only for properties we have access to)
        $vendor->properties()->wherePivot('relationship_type', 'vendor')->detach();
        foreach ($validated['property_ids'] as $propertyId) {
            $vendor->properties()->attach($propertyId, [
                'relationship_type' => 'vendor',
                'is_primary' => false,
            ]);
        }

        return redirect()->route('manage.vendors.index')
                         ->with('success', 'Vendor updated successfully.');
    }

    /**
     * Get property IDs accessible to the user.
     */
    protected function getUserPropertyIds(User $user): array
    {
        if ($user->hasRole('super_admin')) {
            return Property::pluck('id')->toArray();
        }

        $ownedIds = $user->ownedProperties()->pluck('id')->toArray();
        $managedIds = $user->managedProperties()->pluck('properties.id')->toArray();

        return array_unique(array_merge($ownedIds, $managedIds));
    }

    /**
     * Get properties accessible to the user.
     */
    protected function getAccessibleProperties(User $user)
    {
        if ($user->hasRole('super_admin')) {
            return Property::all();
        }

        $propertyIds = $this->getUserPropertyIds($user);
        return Property::whereIn('id', $propertyIds)->get();
    }
}
