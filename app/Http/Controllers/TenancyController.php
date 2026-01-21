<?php

namespace App\Http\Controllers;

use App\Models\Tenancy;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TenancyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $user = auth()->user();
        if (!$user->can('properties.manage_tenants')) {
            abort(403, 'Unauthorized access');
        }

        $tenancies = Tenancy::with(['unit', 'tenant'])->paginate(15);

        return view('tenancies.index', compact('tenancies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $user = auth()->user();
        if (!$user->can('properties.manage_tenants')) {
            abort(403, 'Unauthorized access');
        }

        $units = Unit::where('status', 'available')->get();
        $tenants = User::role('tenant')->get();

        return view('tenancies.create', compact('units', 'tenants'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        if (!$user->can('properties.manage_tenants')) {
            abort(403, 'Unauthorized access');
        }

        $validatedData = $request->validate([
            'unit_id' => 'required|exists:units,id|unique:tenancies,unit_id,NULL,id,status,active',
            'tenant_user_id' => 'required|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'rent_amount' => 'required|numeric|min:0',
            'deposit_amount' => 'required|numeric|min:0',
            'status' => 'required|in:active,terminated,expired'
        ]);

        // Update unit status to occupied
        $unit = Unit::findOrFail($validatedData['unit_id']);
        $unit->update(['status' => 'occupied']);

        Tenancy::create($validatedData);

        return redirect()->route('tenancies.index')
                         ->with('success', 'Tenancy created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Tenancy $tenancy): View
    {
        $user = auth()->user();
        if (!$user->can('properties.manage_tenants')) {
            abort(403, 'Unauthorized access');
        }

        $tenancy->load(['unit.property', 'tenant']);

        return view('tenancies.show', compact('tenancy'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tenancy $tenancy): View
    {
        $user = auth()->user();
        if (!$user->can('properties.manage_tenants')) {
            abort(403, 'Unauthorized access');
        }

        $units = Unit::where('status', '!=', 'occupied')
                     ->orWhere('id', $tenancy->unit_id)
                     ->get();
        $tenants = User::role('tenant')->get();

        return view('tenancies.edit', compact('tenancy', 'units', 'tenants'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tenancy $tenancy): RedirectResponse
    {
        $user = auth()->user();
        if (!$user->can('properties.manage_tenants')) {
            abort(403, 'Unauthorized access');
        }

        $validatedData = $request->validate([
            'unit_id' => 'sometimes|required|exists:units,id',
            'tenant_user_id' => 'sometimes|required|exists:users,id',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'rent_amount' => 'sometimes|required|numeric|min:0',
            'deposit_amount' => 'sometimes|required|numeric|min:0',
            'status' => 'sometimes|required|in:active,terminated,expired'
        ]);

        // Handle unit status change if unit is changed
        if (isset($validatedData['unit_id']) && $validatedData['unit_id'] !== $tenancy->unit_id) {
            // Mark old unit as available
            $oldUnit = $tenancy->unit;
            $oldUnit->update(['status' => 'available']);

            // Mark new unit as occupied
            $newUnit = Unit::findOrFail($validatedData['unit_id']);
            $newUnit->update(['status' => 'occupied']);
        }

        $tenancy->update($validatedData);

        return redirect()->route('tenancies.index')
                         ->with('success', 'Tenancy updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tenancy $tenancy): RedirectResponse
    {
        $user = auth()->user();
        if (!$user->can('properties.manage_tenants')) {
            abort(403, 'Unauthorized access');
        }

        // Mark unit as available when tenancy is terminated
        $unit = $tenancy->unit;
        $unit->update(['status' => 'available']);

        $tenancy->delete();

        return redirect()->route('tenancies.index')
                         ->with('success', 'Tenancy terminated successfully.');
    }
}
