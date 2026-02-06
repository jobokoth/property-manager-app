<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use App\Models\ServiceQuote;
use App\Models\Unit;
use App\Models\Tenancy;
use App\Models\User;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Cloudinary\Api\Exception\GeneralError;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ServiceRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $user = auth()->user();
        $query = ServiceRequest::with(['property', 'unit', 'tenant', 'tenancy']);

        $unitLabel = null;
        if ($user->hasRole('tenant')) {
            $query->where('tenant_user_id', $user->id);

            // Get the unit label for the page title
            $activeTenancy = Tenancy::with('unit')
                ->where('tenant_user_id', $user->id)
                ->where('status', 'active')
                ->first();

            if ($activeTenancy) {
                $unitLabel = $activeTenancy->unit->label;
            }
        }

        $serviceRequests = $query->paginate(15);

        return view('service-requests.index', compact('serviceRequests', 'unitLabel'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $user = auth()->user();

        if ($user->hasRole('tenant')) {
            $activeTenancy = Tenancy::with('unit.property')
                ->where('tenant_user_id', $user->id)
                ->where('status', 'active')
                ->first();

            if (!$activeTenancy) {
                return redirect()->route('service-requests.index')
                    ->with('error', 'No active tenancy found for your account.');
            }

            return view('service-requests.create', compact('activeTenancy'));
        }

        $units = Unit::all();
        $tenancies = Tenancy::where('status', 'active')->get();
        $tenants = User::role('tenant')->get();
        $properties = Property::all();

        return view('service-requests.create', compact('units', 'tenancies', 'tenants', 'properties'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        if ($user->hasRole('tenant')) {
            $activeTenancy = Tenancy::with('unit')
                ->where('tenant_user_id', $user->id)
                ->where('status', 'active')
                ->first();

            if (!$activeTenancy) {
                return redirect()->route('service-requests.index')
                    ->with('error', 'No active tenancy found for your account.');
            }

            $validatedData = $request->validate([
                'category' => 'required|in:plumbing,electrical,carpentry,painting,cleaning,appliance,security,other',
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'priority' => 'required|in:low,medium,high,urgent',
                'images' => 'nullable|array|max:10',
                'images.*' => 'file|mimes:jpg,jpeg,png,webp',
                'videos' => 'nullable|array|max:1',
                'videos.*' => 'file|mimes:mp4,mov',
            ]);

            $validatedData['property_id'] = $activeTenancy->unit->property_id;
            $validatedData['unit_id'] = $activeTenancy->unit_id;
            $validatedData['tenancy_id'] = $activeTenancy->id;
            $validatedData['tenant_user_id'] = $user->id;
            $validatedData['status'] = 'open';

            $serviceRequest = DB::transaction(function () use ($validatedData, $request) {
                $serviceRequest = ServiceRequest::create($validatedData);
                $this->storeMediaUploads($serviceRequest, $request);

                return $serviceRequest;
            });

            return redirect()->route('service-requests.index')
                             ->with('success', 'Service request created successfully.');
        }

        $validatedData = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'unit_id' => 'required|exists:units,id',
            'tenancy_id' => 'required|exists:tenancies,id',
            'tenant_user_id' => 'required|exists:users,id',
            'category' => 'required|in:plumbing,electrical,carpentry,painting,cleaning,appliance,security,other',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:open,in_review,quoted,scheduled,in_progress,completed,closed',
            'images' => 'nullable|array|max:10',
            'images.*' => 'file|mimes:jpg,jpeg,png,webp',
            'videos' => 'nullable|array|max:3',
            'videos.*' => 'file|mimes:mp4,mov',
        ]);

        $serviceRequest = DB::transaction(function () use ($validatedData, $request) {
            $serviceRequest = ServiceRequest::create($validatedData);
            $this->storeMediaUploads($serviceRequest, $request);

            return $serviceRequest;
        });

        return redirect()->route('service-requests.index')
                         ->with('success', 'Service request created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ServiceRequest $serviceRequest): View
    {
        $user = auth()->user();

        $serviceRequest->load(['property', 'unit', 'tenant', 'tenancy', 'media', 'assignedVendor', 'quotes.vendor', 'invoices.vendor', 'comments.user']);

        // Get available vendors for assignment (property managers and owners)
        $availableVendors = [];
        if ($user->can('requests.assign_vendor')) {
            $availableVendors = User::role('vendor')->get();
        }

        return view('service-requests.show', compact('serviceRequest', 'availableVendors'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ServiceRequest $serviceRequest): View
    {
        $user = auth()->user();

        if ($user->hasRole('tenant')) {
            $serviceRequest->load(['property', 'unit', 'tenant', 'tenancy']);

            return view('service-requests.edit', compact('serviceRequest'));
        }

        $units = Unit::all();
        $tenancies = Tenancy::where('status', 'active')->get();
        $tenants = User::role('tenant')->get();
        $properties = Property::all();

        return view('service-requests.edit', compact('serviceRequest', 'units', 'tenancies', 'tenants', 'properties'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ServiceRequest $serviceRequest): RedirectResponse
    {
        $user = auth()->user();

        if ($user->hasRole('tenant')) {
            $validatedData = $request->validate([
                'category' => 'sometimes|required|in:plumbing,electrical,carpentry,painting,cleaning,appliance,security,other',
                'title' => 'sometimes|required|string|max:255',
                'description' => 'sometimes|required|string',
                'priority' => 'sometimes|required|in:low,medium,high,urgent',
                'images' => 'nullable|array|max:10',
                'images.*' => 'file|mimes:jpg,jpeg,png,webp',
                'videos' => 'nullable|array|max:3',
                'videos.*' => 'file|mimes:mp4,mov',
            ]);

            DB::transaction(function () use ($serviceRequest, $validatedData, $request) {
                $serviceRequest->update($validatedData);
                $this->storeMediaUploads($serviceRequest, $request);
            });

            return redirect()->route('service-requests.index')
                             ->with('success', 'Service request updated successfully.');
        }

        $validatedData = $request->validate([
            'property_id' => 'sometimes|required|exists:properties,id',
            'unit_id' => 'sometimes|required|exists:units,id',
            'tenancy_id' => 'sometimes|required|exists:tenancies,id',
            'tenant_user_id' => 'sometimes|required|exists:users,id',
            'category' => 'sometimes|required|in:plumbing,electrical,carpentry,painting,cleaning,appliance,security,other',
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'priority' => 'sometimes|required|in:low,medium,high,urgent',
            'status' => 'sometimes|required|in:open,in_review,quoted,scheduled,in_progress,completed,closed',
            'images' => 'nullable|array|max:10',
            'images.*' => 'file|mimes:jpg,jpeg,png,webp',
            'videos' => 'nullable|array|max:3',
            'videos.*' => 'file|mimes:mp4,mov',
        ]);

        DB::transaction(function () use ($serviceRequest, $validatedData, $request) {
            $serviceRequest->update($validatedData);
            $this->storeMediaUploads($serviceRequest, $request);
        });

        return redirect()->route('service-requests.index')
                         ->with('success', 'Service request updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ServiceRequest $serviceRequest): RedirectResponse
    {
        $serviceRequest->delete();

        return redirect()->route('service-requests.index')
                         ->with('success', 'Service request deleted successfully.');
    }

    private function storeMediaUploads(ServiceRequest $serviceRequest, Request $request): void
    {
        $images = $request->file('images', []);
        $videos = $request->file('videos', []);

        $files = [
            'image' => $images,
            'video' => $videos,
        ];

        $folder = "properties/{$serviceRequest->property_id}/requests/{$serviceRequest->id}/evidence";

        foreach ($files as $type => $mediaFiles) {
            foreach ($mediaFiles as $file) {
                try {
                    $upload = Cloudinary::uploadApi()->upload($file->getRealPath(), [
                        'folder' => $folder,
                        'resource_type' => $type === 'video' ? 'video' : 'image',
                    ]);

                    $serviceRequest->media()->create([
                        'type' => $type,
                        'cloudinary_public_id' => $upload['public_id'],
                        'url' => $upload['secure_url'],
                        'bytes' => $file->getSize(),
                        'format' => $file->getClientOriginalExtension(),
                        'duration' => null,
                    ]);
                } catch (GeneralError $e) {
                    // Log the error and continue without blocking the request
                    Log::error("Cloudinary upload failed: " . $e->getMessage());
                    continue;
                }
            }
        }
    }

    /**
     * Assign a vendor to the service request.
     */
    public function assignVendor(Request $request, ServiceRequest $serviceRequest): RedirectResponse
    {
        $validated = $request->validate([
            'vendor_id' => 'required|exists:users,id',
            'assignment_notes' => 'nullable|string|max:1000',
        ]);

        $vendor = User::findOrFail($validated['vendor_id']);

        // Verify the user has vendor role
        if (!$vendor->hasRole('vendor')) {
            return redirect()->back()->with('error', 'Selected user is not a vendor.');
        }

        $serviceRequest->update([
            'assigned_vendor_id' => $vendor->id,
            'assigned_at' => now(),
            'assignment_notes' => $validated['assignment_notes'] ?? null,
            'status' => 'in_review',
        ]);

        return redirect()->route('service-requests.show', $serviceRequest)
                         ->with('success', 'Vendor assigned successfully.');
    }

    /**
     * Remove vendor assignment from service request.
     */
    public function removeVendor(ServiceRequest $serviceRequest): RedirectResponse
    {
        $serviceRequest->update([
            'assigned_vendor_id' => null,
            'assigned_at' => null,
            'assignment_notes' => null,
        ]);

        return redirect()->route('service-requests.show', $serviceRequest)
                         ->with('success', 'Vendor assignment removed.');
    }

    /**
     * Approve a vendor quote.
     */
    public function approveQuote(ServiceRequest $serviceRequest, ServiceQuote $quote): RedirectResponse
    {
        // Verify the quote belongs to this service request
        if ($quote->service_request_id !== $serviceRequest->id) {
            abort(404, 'Quote not found for this service request.');
        }

        $user = auth()->user();
        $quote->update([
            'status' => 'approved',
            'reviewed_by_user_id' => $user->id,
            'reviewed_at' => now(),
        ]);

        $serviceRequest->update(['status' => 'scheduled']);

        return redirect()->route('service-requests.show', $serviceRequest)
                         ->with('success', 'Quote approved successfully.');
    }

    /**
     * Reject a vendor quote.
     */
    public function rejectQuote(Request $request, ServiceRequest $serviceRequest, ServiceQuote $quote): RedirectResponse
    {
        // Verify the quote belongs to this service request
        if ($quote->service_request_id !== $serviceRequest->id) {
            abort(404, 'Quote not found for this service request.');
        }

        $validated = $request->validate([
            'review_notes' => 'nullable|string|max:1000',
        ]);

        $user = auth()->user();
        $quote->update([
            'status' => 'rejected',
            'reviewed_by_user_id' => $user->id,
            'reviewed_at' => now(),
            'review_notes' => $validated['review_notes'] ?? null,
        ]);

        return redirect()->route('service-requests.show', $serviceRequest)
                         ->with('success', 'Quote rejected.');
    }
}
