<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\MessageDelivery;
use App\Models\Property;
use App\Models\Tenancy;
use App\Models\User;
use App\Notifications\TenantNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MessageController extends Controller
{
    /**
     * Display a listing of sent messages (outbox).
     */
    public function index(): View
    {
        $user = auth()->user();
        $propertyIds = $this->getUserPropertyIds($user);

        $messages = Message::where('sender_user_id', $user->id)
            ->whereIn('property_id', $propertyIds)
            ->with(['property', 'deliveries'])
            ->withCount(['deliveries', 'deliveries as read_count' => function ($query) {
                $query->where('status', 'read');
            }])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('messages.index', compact('messages'));
    }

    /**
     * Show the form for composing a new message.
     */
    public function create(): View
    {
        $user = auth()->user();
        $propertyIds = $this->getUserPropertyIds($user);

        $properties = Property::whereIn('id', $propertyIds)
            ->with(['tenancies' => function ($query) {
                $query->where('tenancies.status', 'active')->with('tenant');
            }, 'caretakers', 'vendors'])
            ->get();

        return view('messages.create', compact('properties'));
    }

    /**
     * Store a newly created message.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'property_id' => 'required|exists:properties,id',
            'audience_type' => 'required|in:individual,group',
            'recipients' => 'required_if:audience_type,individual|array',
            'recipients.*' => 'exists:users,id',
            'groups' => 'required_if:audience_type,group|array',
            'groups.*' => 'in:tenants,caretakers,vendors',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'send_email' => 'nullable|boolean',
        ]);

        $user = auth()->user();
        $propertyIds = $this->getUserPropertyIds($user);

        // Verify the user has access to this property
        if (!in_array($request->property_id, $propertyIds)) {
            abort(403, 'You do not have access to this property.');
        }

        // Resolve recipients based on audience type
        $recipientIds = $request->audience_type === 'individual'
            ? $request->recipients
            : $this->resolveGroupRecipients($request->property_id, $request->groups);

        if (empty($recipientIds)) {
            return back()->withInput()->with('error', 'No recipients found for the selected criteria.');
        }

        // Build audience payload
        $audiencePayload = $request->audience_type === 'individual'
            ? ['user_ids' => $recipientIds]
            : ['groups' => $request->groups];

        DB::transaction(function () use ($request, $user, $recipientIds, $audiencePayload) {
            // Create the message
            $message = Message::create([
                'property_id' => $request->property_id,
                'sender_user_id' => $user->id,
                'audience_type' => $request->audience_type,
                'audience_payload' => $audiencePayload,
                'subject' => $request->subject,
                'body' => $request->body,
            ]);

            // Create delivery records for each recipient
            foreach ($recipientIds as $recipientId) {
                MessageDelivery::create([
                    'message_id' => $message->id,
                    'recipient_user_id' => $recipientId,
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);
            }

            // Send email notifications if requested
            if ($request->boolean('send_email')) {
                $recipients = User::whereIn('id', $recipientIds)->get();
                foreach ($recipients as $recipient) {
                    $recipient->notify(new TenantNotification($message));
                }
            }
        });

        return redirect()->route('messages.index')->with('success', 'Message sent successfully to ' . count($recipientIds) . ' recipient(s).');
    }

    /**
     * Display the specified message with delivery status.
     */
    public function show(Message $message): View
    {
        $user = auth()->user();
        $propertyIds = $this->getUserPropertyIds($user);

        // Verify access
        if ($message->sender_user_id !== $user->id || !in_array($message->property_id, $propertyIds)) {
            abort(403, 'You do not have access to this message.');
        }

        $message->load(['property', 'deliveries.recipient']);

        $deliveryStats = [
            'total' => $message->deliveries->count(),
            'read' => $message->deliveries->where('status', 'read')->count(),
            'unread' => $message->deliveries->where('status', '!=', 'read')->count(),
        ];

        return view('messages.show', compact('message', 'deliveryStats'));
    }

    /**
     * Get property IDs accessible to the user based on their role.
     */
    private function getUserPropertyIds(User $user): array
    {
        if ($user->hasRole('super_admin')) {
            return Property::pluck('id')->toArray();
        }

        if ($user->hasRole('owner')) {
            return $user->ownedProperties()->pluck('id')->toArray();
        }

        if ($user->hasRole('property_manager')) {
            return $user->managedProperties()->pluck('properties.id')->toArray();
        }

        if ($user->hasRole('caretaker')) {
            return $user->caretakerProperties()->pluck('properties.id')->toArray();
        }

        return [];
    }

    /**
     * Resolve group recipients into user IDs.
     */
    private function resolveGroupRecipients(int $propertyId, array $groups): array
    {
        $property = Property::with(['tenancies' => function ($query) {
            $query->where('tenancies.status', 'active')->with('tenant');
        }, 'caretakers', 'vendors'])->findOrFail($propertyId);

        $recipientIds = [];

        if (in_array('tenants', $groups)) {
            foreach ($property->tenancies as $tenancy) {
                if ($tenancy->tenant) {
                    $recipientIds[] = $tenancy->tenant->id;
                }
            }
        }

        if (in_array('caretakers', $groups)) {
            $recipientIds = array_merge($recipientIds, $property->caretakers->pluck('id')->toArray());
        }

        if (in_array('vendors', $groups)) {
            $recipientIds = array_merge($recipientIds, $property->vendors->pluck('id')->toArray());
        }

        return array_unique($recipientIds);
    }
}
