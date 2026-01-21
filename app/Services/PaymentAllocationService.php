<?php

namespace App\Services;

use App\Models\Allocation;
use App\Models\Balance;
use App\Models\MpesaMessage;
use App\Models\Payment;
use App\Models\Tenancy;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

class PaymentAllocationService
{
    /**
     * Takes parsed M-Pesa data and allocates it as a payment against a tenancy's outstanding balances.
     *
     * Allocation Order:
     * 1. Rent arrears (oldest month first)
     * 2. Current month rent
     * 3. Water arrears (oldest month first)
     * 4. Current month water
     * 5. Advance (credit for future periods)
     *
     * @param int $tenancyId
     * @param array $parsedData
     * @return array
     */
    public function allocatePayment(int $tenancyId, array $parsedData): array
    {
        try {
            return DB::transaction(function () use ($tenancyId, $parsedData) {
                $tenancy = Tenancy::with('tenant', 'unit.property')->findOrFail($tenancyId);

                // 1. Check for duplicate transaction ID
                $existingMessage = MpesaMessage::where('trans_id', $parsedData['trans_id'])->first();
                if ($existingMessage) {
                    throw new Exception("Duplicate transaction. This payment (ID: {$parsedData['trans_id']}) has already been recorded.");
                }

                // 2. Create the MpesaMessage record
                $mpesaMessage = MpesaMessage::create([
                    'property_id' => $tenancy->unit->property_id,
                    'tenant_user_id' => $tenancy->tenant_user_id,
                    'raw_text' => $parsedData['raw_text'],
                    'sender_msisdn' => $tenancy->tenant->phone,
                    'amount' => $parsedData['amount'],
                    'trans_id' => $parsedData['trans_id'],
                    'trans_time' => $parsedData['trans_time'],
                    'status' => 'processed',
                    'uploaded_by_user_id' => auth()->id(),
                ]);

                // 3. Create the Payment record
                $payment = Payment::create([
                    'property_id' => $tenancy->unit->property_id,
                    'tenancy_id' => $tenancy->id,
                    'payer_user_id' => $tenancy->tenant_user_id,
                    'source' => 'mpesa_upload',
                    'amount' => $parsedData['amount'],
                    'paid_at' => $parsedData['trans_time'],
                    'reference' => $parsedData['trans_id'],
                    'status' => 'confirmed',
                ]);

                $amountToAllocate = $parsedData['amount'];
                $currentMonth = Carbon::now()->format('Y-m-01');
                $allocationSummary = [];

                // 4. Ensure current month balance exists
                $currentBalance = Balance::firstOrCreate(
                    ['tenancy_id' => $tenancy->id, 'period_month' => $currentMonth],
                    ['rent_due' => $tenancy->rent_amount, 'water_due' => 0, 'rent_paid' => 0, 'water_paid' => 0, 'carried_forward' => 0]
                );

                // 5. Get all balances with outstanding rent arrears (oldest first, excluding current month)
                $rentArrearsBalances = Balance::where('tenancy_id', $tenancy->id)
                    ->where('period_month', '<', $currentMonth)
                    ->whereRaw('rent_due > rent_paid')
                    ->orderBy('period_month', 'asc')
                    ->get();

                // 6. Allocate to rent arrears (oldest first)
                foreach ($rentArrearsBalances as $balance) {
                    if ($amountToAllocate <= 0) break;

                    $rentOwed = $balance->rent_due - $balance->rent_paid;
                    if ($rentOwed > 0) {
                        $rentPayment = min($amountToAllocate, $rentOwed);
                        $balance->rent_paid += $rentPayment;
                        $balance->save();
                        $amountToAllocate -= $rentPayment;

                        Allocation::create([
                            'payment_id' => $payment->id,
                            'allocation_type' => 'rent_arrears',
                            'amount' => $rentPayment,
                            'period_month' => $balance->period_month,
                            'notes' => 'Rent arrears for ' . Carbon::parse($balance->period_month)->format('F Y'),
                        ]);

                        $allocationSummary[] = "Rent arrears ({$balance->period_month}): KES " . number_format($rentPayment, 2);
                    }
                }

                // 7. Allocate to current month rent
                if ($amountToAllocate > 0) {
                    $currentRentOwed = $currentBalance->rent_due - $currentBalance->rent_paid;
                    if ($currentRentOwed > 0) {
                        $rentPayment = min($amountToAllocate, $currentRentOwed);
                        $currentBalance->rent_paid += $rentPayment;
                        $amountToAllocate -= $rentPayment;

                        Allocation::create([
                            'payment_id' => $payment->id,
                            'allocation_type' => 'rent',
                            'amount' => $rentPayment,
                            'period_month' => $currentMonth,
                        ]);

                        $allocationSummary[] = "Current rent: KES " . number_format($rentPayment, 2);
                    }
                }

                // 8. Get all balances with outstanding water arrears (oldest first, excluding current month)
                $waterArrearsBalances = Balance::where('tenancy_id', $tenancy->id)
                    ->where('period_month', '<', $currentMonth)
                    ->whereRaw('water_due > water_paid')
                    ->orderBy('period_month', 'asc')
                    ->get();

                // 9. Allocate to water arrears (oldest first)
                foreach ($waterArrearsBalances as $balance) {
                    if ($amountToAllocate <= 0) break;

                    $waterOwed = $balance->water_due - $balance->water_paid;
                    if ($waterOwed > 0) {
                        $waterPayment = min($amountToAllocate, $waterOwed);
                        $balance->water_paid += $waterPayment;
                        $balance->save();
                        $amountToAllocate -= $waterPayment;

                        Allocation::create([
                            'payment_id' => $payment->id,
                            'allocation_type' => 'water_arrears',
                            'amount' => $waterPayment,
                            'period_month' => $balance->period_month,
                            'notes' => 'Water arrears for ' . Carbon::parse($balance->period_month)->format('F Y'),
                        ]);

                        $allocationSummary[] = "Water arrears ({$balance->period_month}): KES " . number_format($waterPayment, 2);
                    }
                }

                // 10. Allocate to current month water
                if ($amountToAllocate > 0) {
                    $currentWaterOwed = $currentBalance->water_due - $currentBalance->water_paid;
                    if ($currentWaterOwed > 0) {
                        $waterPayment = min($amountToAllocate, $currentWaterOwed);
                        $currentBalance->water_paid += $waterPayment;
                        $amountToAllocate -= $waterPayment;

                        Allocation::create([
                            'payment_id' => $payment->id,
                            'allocation_type' => 'water',
                            'amount' => $waterPayment,
                            'period_month' => $currentMonth,
                        ]);

                        $allocationSummary[] = "Current water: KES " . number_format($waterPayment, 2);
                    }
                }

                // 11. Handle any remaining amount as advance payment
                if ($amountToAllocate > 0) {
                    // Create advance allocation - this will be credit toward future payments
                    $nextMonth = Carbon::now()->addMonth()->format('Y-m-01');

                    // Create or update the next month's balance with the advance
                    $nextBalance = Balance::firstOrCreate(
                        ['tenancy_id' => $tenancy->id, 'period_month' => $nextMonth],
                        ['rent_due' => $tenancy->rent_amount, 'water_due' => 0, 'rent_paid' => 0, 'water_paid' => 0, 'carried_forward' => 0]
                    );

                    // Apply the advance to next month's rent
                    $nextBalance->rent_paid += $amountToAllocate;
                    $nextBalance->save();

                    Allocation::create([
                        'payment_id' => $payment->id,
                        'allocation_type' => 'advance',
                        'amount' => $amountToAllocate,
                        'period_month' => $nextMonth,
                        'notes' => 'Advance payment applied to ' . Carbon::parse($nextMonth)->format('F Y'),
                    ]);

                    $allocationSummary[] = "Advance payment: KES " . number_format($amountToAllocate, 2);
                    $amountToAllocate = 0;
                }

                // Save current balance
                $currentBalance->save();

                $summaryText = implode(', ', $allocationSummary);
                return [
                    'success' => true,
                    'message' => 'Payment of KES ' . number_format($parsedData['amount'], 2) . ' allocated successfully. ' . $summaryText,
                    'payment_id' => $payment->id,
                    'allocations' => $allocationSummary,
                ];

            });
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Manual payment allocation without M-Pesa message
     *
     * @param int $tenancyId
     * @param float $amount
     * @param string $reference
     * @param string $source
     * @return array
     */
    public function allocateManualPayment(int $tenancyId, float $amount, string $reference = '', string $source = 'manual'): array
    {
        try {
            return DB::transaction(function () use ($tenancyId, $amount, $reference, $source) {
                $tenancy = Tenancy::with('tenant', 'unit.property')->findOrFail($tenancyId);

                // Create the Payment record
                $payment = Payment::create([
                    'property_id' => $tenancy->unit->property_id,
                    'tenancy_id' => $tenancy->id,
                    'payer_user_id' => $tenancy->tenant_user_id,
                    'source' => $source,
                    'amount' => $amount,
                    'paid_at' => now(),
                    'reference' => $reference,
                    'status' => 'confirmed',
                ]);

                $amountToAllocate = $amount;
                $currentMonth = Carbon::now()->format('Y-m-01');
                $allocationSummary = [];

                // Ensure current month balance exists
                $currentBalance = Balance::firstOrCreate(
                    ['tenancy_id' => $tenancy->id, 'period_month' => $currentMonth],
                    ['rent_due' => $tenancy->rent_amount, 'water_due' => 0, 'rent_paid' => 0, 'water_paid' => 0, 'carried_forward' => 0]
                );

                // Allocate to rent arrears first
                $rentArrearsBalances = Balance::where('tenancy_id', $tenancy->id)
                    ->where('period_month', '<', $currentMonth)
                    ->whereRaw('rent_due > rent_paid')
                    ->orderBy('period_month', 'asc')
                    ->get();

                foreach ($rentArrearsBalances as $balance) {
                    if ($amountToAllocate <= 0) break;

                    $rentOwed = $balance->rent_due - $balance->rent_paid;
                    if ($rentOwed > 0) {
                        $rentPayment = min($amountToAllocate, $rentOwed);
                        $balance->rent_paid += $rentPayment;
                        $balance->save();
                        $amountToAllocate -= $rentPayment;

                        Allocation::create([
                            'payment_id' => $payment->id,
                            'allocation_type' => 'rent_arrears',
                            'amount' => $rentPayment,
                            'period_month' => $balance->period_month,
                        ]);
                    }
                }

                // Allocate to current rent
                if ($amountToAllocate > 0) {
                    $currentRentOwed = $currentBalance->rent_due - $currentBalance->rent_paid;
                    if ($currentRentOwed > 0) {
                        $rentPayment = min($amountToAllocate, $currentRentOwed);
                        $currentBalance->rent_paid += $rentPayment;
                        $amountToAllocate -= $rentPayment;

                        Allocation::create([
                            'payment_id' => $payment->id,
                            'allocation_type' => 'rent',
                            'amount' => $rentPayment,
                            'period_month' => $currentMonth,
                        ]);
                    }
                }

                // Allocate to water arrears
                $waterArrearsBalances = Balance::where('tenancy_id', $tenancy->id)
                    ->where('period_month', '<', $currentMonth)
                    ->whereRaw('water_due > water_paid')
                    ->orderBy('period_month', 'asc')
                    ->get();

                foreach ($waterArrearsBalances as $balance) {
                    if ($amountToAllocate <= 0) break;

                    $waterOwed = $balance->water_due - $balance->water_paid;
                    if ($waterOwed > 0) {
                        $waterPayment = min($amountToAllocate, $waterOwed);
                        $balance->water_paid += $waterPayment;
                        $balance->save();
                        $amountToAllocate -= $waterPayment;

                        Allocation::create([
                            'payment_id' => $payment->id,
                            'allocation_type' => 'water_arrears',
                            'amount' => $waterPayment,
                            'period_month' => $balance->period_month,
                        ]);
                    }
                }

                // Allocate to current water
                if ($amountToAllocate > 0) {
                    $currentWaterOwed = $currentBalance->water_due - $currentBalance->water_paid;
                    if ($currentWaterOwed > 0) {
                        $waterPayment = min($amountToAllocate, $currentWaterOwed);
                        $currentBalance->water_paid += $waterPayment;
                        $amountToAllocate -= $waterPayment;

                        Allocation::create([
                            'payment_id' => $payment->id,
                            'allocation_type' => 'water',
                            'amount' => $waterPayment,
                            'period_month' => $currentMonth,
                        ]);
                    }
                }

                // Handle advance
                if ($amountToAllocate > 0) {
                    $nextMonth = Carbon::now()->addMonth()->format('Y-m-01');
                    $nextBalance = Balance::firstOrCreate(
                        ['tenancy_id' => $tenancy->id, 'period_month' => $nextMonth],
                        ['rent_due' => $tenancy->rent_amount, 'water_due' => 0, 'rent_paid' => 0, 'water_paid' => 0, 'carried_forward' => 0]
                    );

                    $nextBalance->rent_paid += $amountToAllocate;
                    $nextBalance->save();

                    Allocation::create([
                        'payment_id' => $payment->id,
                        'allocation_type' => 'advance',
                        'amount' => $amountToAllocate,
                        'period_month' => $nextMonth,
                    ]);
                }

                $currentBalance->save();

                return [
                    'success' => true,
                    'message' => 'Payment of KES ' . number_format($amount, 2) . ' allocated successfully.',
                    'payment_id' => $payment->id,
                ];
            });
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
