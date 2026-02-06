<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;

class CaretakerController extends Controller
{
    /**
     * Display a listing of caretakers.
     */
    public function index(): View
    {
        $user = auth()->user();

        $caretakers = User::role('caretaker')
            ->with('caretakerProperties')
            ->when(!$user->hasRole('super_admin'), function ($query) use ($user) {
                // Filter to caretakers of properties the user manages/owns
                $propertyIds = $this->getUserPropertyIds($user);
                $query->whereHas('caretakerProperties', function ($q) use ($propertyIds) {
                    $q->whereIn('properties.id', $propertyIds);
                });
            })
            ->paginate(10);

        return view('caretakers.index', compact('caretakers'));
    }

    /**
     * Show the form for creating a new caretaker.
     */
    public function create(): View
    {
        $user = auth()->user();
        $properties = $this->getAccessibleProperties($user);

        return view('caretakers.create', compact('properties'));
    }

    /**
     * Store a newly created caretaker.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'property_ids' => 'required|array|min:1',
            'property_ids.*' => 'exists:properties,id',
        ]);

        $fullName = trim($validated['first_name'] . ' ' . $validated['last_name']);

        $caretaker = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'name' => $fullName,
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'status' => 'active',
        ]);

        $caretaker->assignRole('caretaker');

        // Assign to properties
        foreach ($validated['property_ids'] as $propertyId) {
            $caretaker->properties()->attach($propertyId, [
                'relationship_type' => 'caretaker',
                'is_primary' => false,
            ]);
        }

        return redirect()->route('caretakers.index')
                         ->with('success', 'Caretaker created successfully.');
    }

    /**
     * Display the specified caretaker.
     */
    public function show(User $caretaker): View
    {
        if (!$caretaker->hasRole('caretaker')) {
            abort(404);
        }

        $caretaker->load(['caretakerProperties', 'caretakerTasks' => function ($query) {
            $query->latest()->take(10);
        }]);

        return view('caretakers.show', compact('caretaker'));
    }

    /**
     * Show the form for editing the specified caretaker.
     */
    public function edit(User $caretaker): View
    {
        if (!$caretaker->hasRole('caretaker')) {
            abort(404);
        }

        $user = auth()->user();
        $properties = $this->getAccessibleProperties($user);
        $assignedPropertyIds = $caretaker->caretakerProperties->pluck('id')->toArray();

        return view('caretakers.edit', compact('caretaker', 'properties', 'assignedPropertyIds'));
    }

    /**
     * Update the specified caretaker.
     */
    public function update(Request $request, User $caretaker): RedirectResponse
    {
        if (!$caretaker->hasRole('caretaker')) {
            abort(404);
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $caretaker->id,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
            'property_ids' => 'required|array|min:1',
            'property_ids.*' => 'exists:properties,id',
            'status' => 'required|in:active,inactive',
        ]);

        $fullName = trim($validated['first_name'] . ' ' . $validated['last_name']);

        $caretaker->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'name' => $fullName,
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'status' => $validated['status'],
        ]);

        if (!empty($validated['password'])) {
            $caretaker->update(['password' => Hash::make($validated['password'])]);
        }

        // Sync properties
        $caretaker->properties()->wherePivot('relationship_type', 'caretaker')->detach();
        foreach ($validated['property_ids'] as $propertyId) {
            $caretaker->properties()->attach($propertyId, [
                'relationship_type' => 'caretaker',
                'is_primary' => false,
            ]);
        }

        return redirect()->route('caretakers.index')
                         ->with('success', 'Caretaker updated successfully.');
    }

    /**
     * Remove the specified caretaker.
     */
    public function destroy(User $caretaker): RedirectResponse
    {
        if (!$caretaker->hasRole('caretaker')) {
            abort(404);
        }

        // Remove from all properties
        $caretaker->properties()->wherePivot('relationship_type', 'caretaker')->detach();

        // Optionally delete user or just deactivate
        $caretaker->update(['status' => 'inactive']);

        return redirect()->route('caretakers.index')
                         ->with('success', 'Caretaker deactivated successfully.');
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
