<?php

namespace App\Http\Controllers;

use App\Models\CaretakerTask;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CaretakerTaskController extends Controller
{
    /**
     * Display a listing of tasks.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();

        $tasks = CaretakerTask::with(['property', 'caretaker', 'assignedBy'])
            ->when(!$user->hasRole('super_admin'), function ($query) use ($user) {
                if ($user->hasRole('caretaker')) {
                    // Caretakers see only their own tasks
                    $query->where('caretaker_user_id', $user->id);
                } else {
                    // Managers/owners see tasks for their properties
                    $propertyIds = $this->getUserPropertyIds($user);
                    $query->whereIn('property_id', $propertyIds);
                }
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->priority, function ($query, $priority) {
                $query->where('priority', $priority);
            })
            ->latest()
            ->paginate(15);

        return view('caretaker-tasks.index', compact('tasks'));
    }

    /**
     * Show the form for creating a new task.
     */
    public function create(): View
    {
        $user = auth()->user();
        $properties = $this->getAccessibleProperties($user);

        // Get caretakers for accessible properties
        $caretakers = User::role('caretaker')
            ->whereHas('caretakerProperties', function ($query) use ($properties) {
                $query->whereIn('properties.id', $properties->pluck('id'));
            })
            ->get();

        return view('caretaker-tasks.create', compact('properties', 'caretakers'));
    }

    /**
     * Store a newly created task.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'caretaker_user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'due_date' => 'nullable|date|after_or_equal:today',
        ]);

        // Verify the caretaker is assigned to the property
        $caretaker = User::findOrFail($validated['caretaker_user_id']);
        if (!$caretaker->caretakerProperties()->where('properties.id', $validated['property_id'])->exists()) {
            return back()->with('error', 'Selected caretaker is not assigned to this property.');
        }

        CaretakerTask::create([
            ...$validated,
            'assigned_by_user_id' => auth()->id(),
            'status' => 'pending',
        ]);

        return redirect()->route('caretaker-tasks.index')
                         ->with('success', 'Task created successfully.');
    }

    /**
     * Display the specified task.
     */
    public function show(CaretakerTask $caretakerTask): View
    {
        $this->authorizeTaskAccess($caretakerTask);

        $caretakerTask->load(['property', 'caretaker', 'assignedBy']);

        return view('caretaker-tasks.show', compact('caretakerTask'));
    }

    /**
     * Show the form for editing the specified task.
     */
    public function edit(CaretakerTask $caretakerTask): View
    {
        $this->authorizeTaskAccess($caretakerTask);

        $user = auth()->user();
        $properties = $this->getAccessibleProperties($user);
        $caretakers = User::role('caretaker')
            ->whereHas('caretakerProperties', function ($query) use ($properties) {
                $query->whereIn('properties.id', $properties->pluck('id'));
            })
            ->get();

        return view('caretaker-tasks.edit', compact('caretakerTask', 'properties', 'caretakers'));
    }

    /**
     * Update the specified task.
     */
    public function update(Request $request, CaretakerTask $caretakerTask): RedirectResponse
    {
        $this->authorizeTaskAccess($caretakerTask);

        $user = auth()->user();

        // Caretakers can only update status and notes
        if ($user->hasRole('caretaker')) {
            $validated = $request->validate([
                'status' => 'required|in:pending,in_progress,completed',
                'notes' => 'nullable|string',
            ]);

            $caretakerTask->update($validated);

            if ($validated['status'] === 'completed') {
                $caretakerTask->update(['completed_at' => now()]);
            }
        } else {
            // Managers can update everything
            $validated = $request->validate([
                'property_id' => 'required|exists:properties,id',
                'caretaker_user_id' => 'required|exists:users,id',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'priority' => 'required|in:low,medium,high,urgent',
                'status' => 'required|in:pending,in_progress,completed,cancelled',
                'due_date' => 'nullable|date',
                'notes' => 'nullable|string',
            ]);

            $caretakerTask->update($validated);

            if ($validated['status'] === 'completed' && !$caretakerTask->completed_at) {
                $caretakerTask->update(['completed_at' => now()]);
            }
        }

        return redirect()->route('caretaker-tasks.show', $caretakerTask)
                         ->with('success', 'Task updated successfully.');
    }

    /**
     * Remove the specified task.
     */
    public function destroy(CaretakerTask $caretakerTask): RedirectResponse
    {
        $this->authorizeTaskAccess($caretakerTask);

        $caretakerTask->delete();

        return redirect()->route('caretaker-tasks.index')
                         ->with('success', 'Task deleted successfully.');
    }

    /**
     * Mark task as complete.
     */
    public function complete(CaretakerTask $caretakerTask): RedirectResponse
    {
        $user = auth()->user();

        // Only the assigned caretaker or manager can complete
        if ($user->hasRole('caretaker') && $caretakerTask->caretaker_user_id !== $user->id) {
            abort(403);
        }

        $caretakerTask->markAsCompleted();

        return redirect()->back()->with('success', 'Task marked as completed.');
    }

    /**
     * Show tasks for the current caretaker.
     */
    public function myTasks(): View
    {
        $user = auth()->user();

        if (!$user->hasRole('caretaker')) {
            abort(403);
        }

        $tasks = CaretakerTask::with(['property', 'assignedBy'])
            ->where('caretaker_user_id', $user->id)
            ->latest()
            ->paginate(15);

        return view('caretaker-tasks.my-tasks', compact('tasks'));
    }

    /**
     * Check if user has access to the task.
     */
    protected function authorizeTaskAccess(CaretakerTask $task): void
    {
        $user = auth()->user();

        if ($user->hasRole('super_admin')) {
            return;
        }

        if ($user->hasRole('caretaker')) {
            if ($task->caretaker_user_id !== $user->id) {
                abort(403);
            }
            return;
        }

        // For managers/owners, check property access
        $propertyIds = $this->getUserPropertyIds($user);
        if (!in_array($task->property_id, $propertyIds)) {
            abort(403);
        }
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
