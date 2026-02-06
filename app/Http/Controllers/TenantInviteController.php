<?php

namespace App\Http\Controllers;

use App\Models\TenantInvite;
use App\Models\Property;
use App\Models\Unit;
use App\Services\TenantInviteService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class TenantInviteController extends Controller
{
    protected TenantInviteService $inviteService;

    public function __construct(TenantInviteService $inviteService)
    {
        $this->inviteService = $inviteService;
    }

    /**
     * Display a listing of invites.
     */
    public function index(): View
    {
        $user = auth()->user();

        $invites = TenantInvite::with(['property', 'unit', 'invitedBy'])
            ->when(!$user->hasRole('super_admin'), function ($query) use ($user) {
                $propertyIds = $this->getUserPropertyIds($user);
                $query->whereIn('property_id', $propertyIds);
            })
            ->latest()
            ->paginate(15);

        return view('tenant-invites.index', compact('invites'));
    }

    /**
     * Show the form for creating a new invite.
     */
    public function create(): View
    {
        $user = auth()->user();
        $properties = $this->getAccessibleProperties($user);

        // Get available units (not currently occupied)
        $units = Unit::whereIn('property_id', $properties->pluck('id'))
            ->where('status', '!=', 'occupied')
            ->with('property')
            ->get();

        return view('tenant-invites.create', compact('properties', 'units'));
    }

    /**
     * Store a newly created invite.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'unit_id' => 'required|exists:units,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'rent_amount' => 'required|numeric|min:0',
            'deposit_amount' => 'nullable|numeric|min:0',
            'start_date' => 'required|date|after_or_equal:today',
        ]);

        $validated['name'] = trim($validated['first_name'] . ' ' . $validated['last_name']);

        // Verify unit belongs to property
        $unit = Unit::findOrFail($validated['unit_id']);
        if ($unit->property_id != $validated['property_id']) {
            return back()->with('error', 'Selected unit does not belong to the selected property.');
        }

        // Check if there's already a pending invite for this email and unit
        $existingInvite = TenantInvite::where('email', $validated['email'])
            ->where('unit_id', $validated['unit_id'])
            ->where('status', 'pending')
            ->first();

        if ($existingInvite) {
            return back()->with('error', 'An invite for this email and unit is already pending.');
        }

        $invite = $this->inviteService->createInvite($validated, auth()->user());
        $this->inviteService->sendInviteNotification($invite);

        return redirect()->route('tenant-invites.index')
                         ->with('success', 'Invitation sent successfully.');
    }

    /**
     * Display the specified invite.
     */
    public function show(TenantInvite $tenantInvite): View
    {
        $this->authorizeInviteAccess($tenantInvite);

        $tenantInvite->load(['property', 'unit', 'invitedBy']);

        return view('tenant-invites.show', compact('tenantInvite'));
    }

    /**
     * Resend the invite notification.
     */
    public function resend(TenantInvite $tenantInvite): RedirectResponse
    {
        $this->authorizeInviteAccess($tenantInvite);

        if ($tenantInvite->status !== 'pending') {
            return back()->with('error', 'Only pending invites can be resent.');
        }

        $this->inviteService->resendInvite($tenantInvite);

        return back()->with('success', 'Invitation resent successfully.');
    }

    /**
     * Cancel the invite.
     */
    public function cancel(TenantInvite $tenantInvite): RedirectResponse
    {
        $this->authorizeInviteAccess($tenantInvite);

        if ($tenantInvite->status !== 'pending') {
            return back()->with('error', 'Only pending invites can be cancelled.');
        }

        $this->inviteService->cancelInvite($tenantInvite);

        return redirect()->route('tenant-invites.index')
                         ->with('success', 'Invitation cancelled successfully.');
    }

    /**
     * Show the accept invite form (public, uses signed URL).
     */
    public function acceptForm(TenantInvite $invite): View|RedirectResponse
    {
        if (!$invite->isValid()) {
            return redirect()->route('login')
                             ->with('error', 'This invitation is no longer valid.');
        }

        return view('tenant-invites.accept', compact('invite'));
    }

    /**
     * Process the accept invite form.
     */
    public function accept(Request $request, TenantInvite $invite): RedirectResponse
    {
        if (!$invite->isValid()) {
            return redirect()->route('login')
                             ->with('error', 'This invitation is no longer valid.');
        }

        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $this->inviteService->acceptInvite($invite, $validated['password']);

        Auth::login($user);

        return redirect()->route('dashboard')
                         ->with('success', 'Welcome! Your account has been created and your tenancy is now active.');
    }

    /**
     * Check if user has access to the invite.
     */
    protected function authorizeInviteAccess(TenantInvite $invite): void
    {
        $user = auth()->user();

        if ($user->hasRole('super_admin')) {
            return;
        }

        $propertyIds = $this->getUserPropertyIds($user);
        if (!in_array($invite->property_id, $propertyIds)) {
            abort(403);
        }
    }

    /**
     * Get property IDs accessible to the user.
     */
    protected function getUserPropertyIds($user): array
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
    protected function getAccessibleProperties($user)
    {
        if ($user->hasRole('super_admin')) {
            return Property::all();
        }

        $propertyIds = $this->getUserPropertyIds($user);
        return Property::whereIn('id', $propertyIds)->get();
    }
}
