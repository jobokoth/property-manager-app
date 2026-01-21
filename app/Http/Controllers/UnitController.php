<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $user = auth()->user();
        if (!$user->can('properties.manage_units')) {
            abort(403, 'Unauthorized access');
        }

        $units = Unit::with('property')->paginate(15);

        return view('units.index', compact('units'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $user = auth()->user();
        if (!$user->can('properties.manage_units')) {
            abort(403, 'Unauthorized access');
        }

        $properties = Property::all();

        return view('units.create', compact('properties'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        if (!$user->can('properties.manage_units')) {
            abort(403, 'Unauthorized access');
        }

        $validatedData = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'label' => 'required|string|max:50',
            'floor' => 'nullable|integer',
            'bedrooms' => 'required|integer|min:0',
            'rent_amount' => 'required|numeric|min:0',
            'water_rate_mode' => 'required|in:per_unit,per_meter',
            'status' => 'required|in:available,occupied,maintenance,reserved'
        ]);

        Unit::create($validatedData);

        return redirect()->route('units.index')
                         ->with('success', 'Unit created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Unit $unit): View
    {
        $user = auth()->user();
        if (!$user->can('properties.manage_units')) {
            abort(403, 'Unauthorized access');
        }

        $unit->load(['property', 'tenancies']);

        return view('units.show', compact('unit'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Unit $unit): View
    {
        $user = auth()->user();
        if (!$user->can('properties.manage_units')) {
            abort(403, 'Unauthorized access');
        }

        $properties = Property::all();

        return view('units.edit', compact('unit', 'properties'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Unit $unit): RedirectResponse
    {
        $user = auth()->user();
        if (!$user->can('properties.manage_units')) {
            abort(403, 'Unauthorized access');
        }

        $validatedData = $request->validate([
            'property_id' => 'sometimes|required|exists:properties,id',
            'label' => 'sometimes|required|string|max:50',
            'floor' => 'nullable|integer',
            'bedrooms' => 'sometimes|required|integer|min:0',
            'rent_amount' => 'sometimes|required|numeric|min:0',
            'water_rate_mode' => 'sometimes|required|in:per_unit,per_meter',
            'status' => 'sometimes|required|in:available,occupied,maintenance,reserved'
        ]);

        $unit->update($validatedData);

        return redirect()->route('units.index')
                         ->with('success', 'Unit updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Unit $unit): RedirectResponse
    {
        $user = auth()->user();
        if (!$user->can('properties.manage_units')) {
            abort(403, 'Unauthorized access');
        }

        $unit->delete();

        return redirect()->route('units.index')
                         ->with('success', 'Unit deleted successfully.');
    }
}
