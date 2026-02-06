<?php

namespace App\Console\Commands;

use App\Models\Balance;
use App\Models\NotificationTemplate;
use App\Models\RentRule;
use App\Models\Tenancy;
use App\Models\Message;
use App\Models\MessageDelivery;
use App\Notifications\TenantNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendRentDueNotifications extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'notifications:rent-due
                            {--days-before=5 : Days before due date to send reminder}
                            {--dry-run : Run without actually sending notifications}';

    /**
     * The console command description.
     */
    protected $description = 'Send rent due reminder notifications to tenants';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $daysBefore = (int) $this->option('days-before');
        $dryRun = $this->option('dry-run');

        $this->info("Sending rent due notifications ({$daysBefore} days before due date)...");

        if ($dryRun) {
            $this->warn('Running in dry-run mode - no notifications will be sent.');
        }

        $currentMonth = Carbon::now()->format('Y-m');
        $today = Carbon::now();

        // Get active tenancies with their rent rules
        $tenancies = Tenancy::with(['unit.property.rentRules', 'tenant'])
            ->where('status', 'active')
            ->get();

        $sentCount = 0;

        foreach ($tenancies as $tenancy) {
            $property = $tenancy->unit->property;

            // Get rent rule for this property/unit
            $rentRule = $property->rentRules->first();

            if (!$rentRule) {
                // Default: due on the 5th
                $dueDay = 5;
            } else {
                $dueDay = $rentRule->due_day;
            }

            // Calculate the due date for current month
            $dueDate = Carbon::createFromFormat('Y-m-d', $today->format('Y-m') . '-' . str_pad($dueDay, 2, '0', STR_PAD_LEFT));

            // Check if we should send notification today
            $reminderDate = $dueDate->copy()->subDays($daysBefore);

            if (!$today->isSameDay($reminderDate)) {
                continue;
            }

            // Check if rent is already paid for this month
            $balance = Balance::where('tenancy_id', $tenancy->id)
                ->where('period_month', $currentMonth)
                ->first();

            if ($balance && $balance->rent_paid >= $balance->rent_due) {
                // Already paid
                continue;
            }

            // Get notification template
            $template = NotificationTemplate::where('key', 'rent_due')
                ->where('is_active', true)
                ->first();

            $subject = $template?->subject ?? 'Rent Due Reminder';
            $body = $template?->body ?? 'Your rent of KES {amount} is due on {due_date}. Please make payment to avoid late fees.';

            // Replace placeholders
            $rentAmount = $tenancy->rent_amount;
            $body = str_replace(
                ['{amount}', '{due_date}', '{tenant_name}', '{unit}', '{property}'],
                [
                    number_format($rentAmount, 2),
                    $dueDate->format('F j, Y'),
                    $tenancy->tenant->name,
                    $tenancy->unit->label ?? $tenancy->unit->number,
                    $property->name,
                ],
                $body
            );

            if ($dryRun) {
                $this->line("Would notify: {$tenancy->tenant->name} ({$tenancy->tenant->email}) - {$subject}");
            } else {
                // Create in-app notification via Message system
                $message = Message::create([
                    'property_id' => $property->id,
                    'sender_user_id' => $property->owner_user_id ?? 1, // System or owner
                    'audience_type' => 'individual',
                    'audience_payload' => ['user_ids' => [$tenancy->tenant_user_id]],
                    'subject' => $subject,
                    'body' => $body,
                ]);

                MessageDelivery::create([
                    'message_id' => $message->id,
                    'recipient_user_id' => $tenancy->tenant_user_id,
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);

                // Also send email notification
                try {
                    $tenancy->tenant->notify(new TenantNotification($message));
                } catch (\Exception $e) {
                    $this->warn("Email failed for {$tenancy->tenant->email}: " . $e->getMessage());
                }

                $this->line("Notified: {$tenancy->tenant->name} ({$tenancy->tenant->email})");
            }

            $sentCount++;
        }

        $this->info("Completed. {$sentCount} notification(s) " . ($dryRun ? 'would be ' : '') . 'sent.');

        return Command::SUCCESS;
    }
}
