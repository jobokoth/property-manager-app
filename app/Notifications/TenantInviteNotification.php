<?php

namespace App\Notifications;

use App\Models\TenantInvite;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TenantInviteNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected TenantInvite $invite;

    /**
     * Create a new notification instance.
     */
    public function __construct(TenantInvite $invite)
    {
        $this->invite = $invite;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $acceptUrl = $this->invite->getAcceptUrl();

        return (new MailMessage)
            ->subject('You\'ve Been Invited to Join ' . $this->invite->property->name)
            ->greeting('Hello ' . $this->invite->name . '!')
            ->line('You have been invited to become a tenant at ' . $this->invite->property->name . '.')
            ->line('**Unit:** ' . $this->invite->unit->label)
            ->line('**Monthly Rent:** KES ' . number_format($this->invite->rent_amount, 2))
            ->line('**Start Date:** ' . $this->invite->start_date->format('F j, Y'))
            ->line('This invitation will expire on ' . $this->invite->expires_at->format('F j, Y') . '.')
            ->action('Accept Invitation', $acceptUrl)
            ->line('If you did not expect this invitation, you can safely ignore this email.')
            ->salutation('Best regards, ' . config('app.name'));
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'invite_id' => $this->invite->id,
            'property_name' => $this->invite->property->name,
            'unit_label' => $this->invite->unit->label,
        ];
    }
}
