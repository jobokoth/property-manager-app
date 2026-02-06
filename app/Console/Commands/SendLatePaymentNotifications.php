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

class SendLatePaymentNotifications extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'notifications:late-payments
                            {--dry-run : Run without actually sending notifications}';

    /**
     * The console command description.
     */
    protected $description = 'Send late payment notifications to tenants who have not paid rent';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        $this->info('Sending late payment notifications...');

        if ($dryRun) {
            $this->warn('Running in dry-run mode - no notifications will be sent.');
        }

        $currentMonth = Carbon::now()->format('Y-m');
        $today = Carbon::now();

        // Get active tenancies with their rent rules
        $tenancies = Tenancy::with(['unit.property.rentRules', 'tenant', 'balances'])
            ->where('status', 'active')
            ->get();

        $sentCount = 0;

        foreach ($tenancies as $tenancy) {
            $property = $tenancy->unit->property;

            // Get rent rule for this property/unit
            $rentRule = $property->rentRules->first();

            if (!$rentRule) {
                // Default: due on the 5th, 3 days grace
                $dueDay = 5;
                $graceDays = 3;
                $lateFeeMode = 'fixed';
                $lateFeeValue = 500;
            } else {
                $dueDay = $rentRule->due_day;
                $graceDays = $rentRule->grace_days ?? 3;
                $lateFeeMode = $rentRule->late_fee_mode ?? 'fixed';
                $lateFeeValue = $rentRule->late_fee_value ?? 500;
            }

            // Calculate the late date (due date + grace period)
            $dueDate = Carbon::createFromFormat('Y-m-d', $today->format('Y-m') . '-' . str_pad($dueDay, 2, '0', STR_PAD_LEFT));
            $lateDate = $dueDate->copy()->addDays($graceDays);

            // Only send on the day after grace period ends
            if (!$today->isSameDay($lateDate->addDay())) {
                continue;
            }

            // Check current balance
            $balance = Balance::where('tenancy_id', $tenancy->id)
                ->where('period_month', $currentMonth)
                ->first();

            // If no balance record or fully paid, skip
            if (!$balance) {
                continue;
            }

            $unpaidRent = $balance->rent_due - $balance->rent_paid;

            if ($unpaidRent <= 0) {
                // Already paid
                continue;
            }

            // Calculate late fee
            if ($lateFeeMode === 'percent') {
                $lateFee = ($unpaidRent * $lateFeeValue) / 100;
            } else {
                $lateFee = $lateFeeValue;
            }

            // Get notification template
            $template = NotificationTemplate::where('key', 'late_payment')
                ->where('is_active', true)
                ->first();

            $subject = $template?->subject ?? 'Late Payment Notice';
            $body = $template?->body ?? 'Your rent payment of KES {amount} is overdue. A late fee of KES {late_fee} has been applied. Total due: KES {total}. Please pay immediately to avoid further action.';

            // Replace placeholders
            $body = str_replace(
                ['{amount}', '{late_fee}', '{total}', '{tenant_name}', '{unit}', '{property}'],
                [
                    number_format($unpaidRent, 2),
                    number_format($lateFee, 2),
                    number_format($unpaidRent + $lateFee, 2),
                    $tenancy->tenant->name,
                    $tenancy->unit->label ?? $tenancy->unit->number,
                    $property->name,
                ],
                $body
            );

            if ($dryRun) {
                $this->line("Would notify: {$tenancy->tenant->name} ({$tenancy->tenant->email}) - {$subject}");
                $this->line("  Unpaid: KES " . number_format($unpaidRent, 2) . ", Late Fee: KES " . number_format($lateFee, 2));
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
