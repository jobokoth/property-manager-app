<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PropertyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $properties = Property::with('owner')->paginate(10);

        return view('properties.index', compact('properties'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $user = auth()->user();

        // Get available property managers
        $propertyManagers = User::role('property_manager')->where('status', 'active')->get();

        // For super_admin, also get owners list
        $owners = [];
        if ($user->hasRole('super_admin')) {
            $owners = User::role('owner')->where('status', 'active')->get();
        }

        return view('properties.create', compact('propertyManagers', 'owners'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string',
            'owner_user_id' => 'nullable|exists:users,id',
            'property_manager_id' => 'nullable|exists:users,id',
            'status' => 'required|in:active,inactive,maintenance'
        ]);

        // If owner is creating, they are automatically the owner
        if ($user->hasRole('owner') && !$user->hasRole('super_admin')) {
            $validatedData['owner_user_id'] = $user->id;
        }

        $propertyManagerId = $validatedData['property_manager_id'] ?? null;
        unset($validatedData['property_manager_id']);

        $property = Property::create($validatedData);

        // Assign property manager if selected
        if ($propertyManagerId) {
            $manager = User::find($propertyManagerId);
            if ($manager && $manager->hasRole('property_manager')) {
                $property->managers()->attach($manager->id, [
                    'relationship_type' => 'manager',
                    'is_primary' => true,
                ]);
            }
        }

        return redirect()->route('properties.index')
                         ->with('success', 'Property created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Property $property): View
    {
        $user = auth()->user();

        $property->load(['owner', 'units', 'tenancies', 'managers', 'vendors', 'caretakers']);

        // Get available property managers for assignment (if owner)
        $availableManagers = [];
        $availableVendors = [];
        if ($user->hasRole(['owner', 'super_admin']) || $property->owner_user_id === $user->id) {
            $availableManagers = User::role('property_manager')
                ->whereNotIn('id', $property->managers->pluck('id'))
                ->get();
            $availableVendors = User::role('vendor')
                ->whereNotIn('id', $property->vendors->pluck('id'))
                ->get();
        }

        return view('properties.show', compact('property', 'availableManagers', 'availableVendors'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Property $property): View
    {
        $user = auth()->user();

        // Get available property managers
        $propertyManagers = User::role('property_manager')->where('status', 'active')->get();

        // Get current assigned manager (primary)
        $currentManagerId = $property->managers()->wherePivot('is_primary', true)->first()?->id;

        // For super_admin, also get owners list
        $owners = [];
        if ($user->hasRole('super_admin')) {
            $owners = User::role('owner')->where('status', 'active')->get();
        }

        return view('properties.edit', compact('property', 'propertyManagers', 'currentManagerId', 'owners'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Property $property): RedirectResponse
    {
        $user = auth()->user();

        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'location' => 'sometimes|required|string',
            'owner_user_id' => 'nullable|exists:users,id',
            'property_manager_id' => 'nullable|exists:users,id',
            'status' => 'sometimes|required|in:active,inactive,maintenance'
        ]);

        // Non-super_admin owners cannot change the owner
        if (!$user->hasRole('super_admin')) {
            unset($validatedData['owner_user_id']);
        }

        $propertyManagerId = $validatedData['property_manager_id'] ?? null;
        unset($validatedData['property_manager_id']);

        $property->update($validatedData);

        // Update primary property manager if changed
        if ($request->has('property_manager_id')) {
            // Remove current primary manager
            $property->managers()->wherePivot('is_primary', true)->detach();

            // Assign new primary manager if selected
            if ($propertyManagerId) {
                $manager = User::find($propertyManagerId);
                if ($manager && $manager->hasRole('property_manager')) {
                    $property->managers()->attach($manager->id, [
                        'relationship_type' => 'manager',
                        'is_primary' => true,
                    ]);
                }
            }
        }

        return redirect()->route('properties.index')
                         ->with('success', 'Property updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Property $property): RedirectResponse
    {
        $property->delete();

        return redirect()->route('properties.index')
                         ->with('success', 'Property deleted successfully.');
    }

    /**
     * Assign a manager to the property.
     */
    public function assignManager(Request $request, Property $property): RedirectResponse
    {
        $user = auth()->user();

        // Only owners or super admins can assign managers
        if (!$user->hasRole('super_admin') && $property->owner_user_id !== $user->id) {
            abort(403, 'Only the property owner can assign managers.');
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'is_primary' => 'boolean',
        ]);

        $manager = User::findOrFail($validated['user_id']);

        // Verify the user has property_manager role
        if (!$manager->hasRole('property_manager')) {
            return redirect()->back()->with('error', 'Selected user is not a property manager.');
        }

        // Check if already assigned
        if ($property->managers()->where('user_id', $manager->id)->exists()) {
            return redirect()->back()->with('error', 'This manager is already assigned to this property.');
        }

        $property->managers()->attach($manager->id, [
            'relationship_type' => 'manager',
            'is_primary' => $validated['is_primary'] ?? false,
        ]);

        return redirect()->route('properties.show', $property)
                         ->with('success', 'Property manager assigned successfully.');
    }

    /**
     * Remove a manager from the property.
     */
    public function removeManager(Property $property, User $manager): RedirectResponse
    {
        $user = auth()->user();

        // Only owners or super admins can remove managers
        if (!$user->hasRole('super_admin') && $property->owner_user_id !== $user->id) {
            abort(403, 'Only the property owner can remove managers.');
        }

        $property->managers()->detach($manager->id);

        return redirect()->route('properties.show', $property)
                         ->with('success', 'Property manager removed successfully.');
    }

    /**
     * Assign a vendor to the property.
     */
    public function assignVendor(Request $request, Property $property): RedirectResponse
    {
        $user = auth()->user();

        // Only owners, property managers, or super admins can assign vendors
        if (!$user->hasRole('super_admin') && $property->owner_user_id !== $user->id && !$property->managers->contains($user)) {
            abort(403, 'You do not have permission to assign vendors to this property.');
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $vendor = User::findOrFail($validated['user_id']);

        // Verify the user has vendor role
        if (!$vendor->hasRole('vendor')) {
            return redirect()->back()->with('error', 'Selected user is not a vendor.');
        }

        // Check if already assigned
        if ($property->vendors()->where('user_id', $vendor->id)->exists()) {
            return redirect()->back()->with('error', 'This vendor is already assigned to this property.');
        }

        $property->vendors()->attach($vendor->id, [
            'relationship_type' => 'vendor',
            'is_primary' => false,
        ]);

        return redirect()->route('properties.show', $property)
                         ->with('success', 'Vendor assigned to property successfully.');
    }

    /**
     * Remove a vendor from the property.
     */
    public function removeVendor(Property $property, User $vendor): RedirectResponse
    {
        $user = auth()->user();

        // Only owners, property managers, or super admins can remove vendors
        if (!$user->hasRole('super_admin') && $property->owner_user_id !== $user->id && !$property->managers->contains($user)) {
            abort(403, 'You do not have permission to remove vendors from this property.');
        }

        $property->vendors()->detach($vendor->id);

        return redirect()->route('properties.show', $property)
                         ->with('success', 'Vendor removed from property successfully.');
    }
}
