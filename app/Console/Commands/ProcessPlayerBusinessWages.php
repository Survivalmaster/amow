<?php

namespace App\Console\Commands;

use App\Models\PlayerBusinessMember;
use App\Support\CharacterActivity;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProcessPlayerBusinessWages extends Command
{
    protected $signature = 'amow:process-player-business-wages';

    protected $description = 'Pay active player business members from their business bank.';

    public function handle(): int
    {
        $members = PlayerBusinessMember::query()
            ->with(['business', 'character', 'role'])
            ->where('status', PlayerBusinessMember::STATUS_ACTIVE)
            ->whereHas('role', fn ($query) => $query->where('hourly_wage', '>', 0))
            ->get();

        $payments = 0;

        foreach ($members as $member) {
            $lastPaidAt = $member->last_paid_at ?? $member->joined_at ?? $member->created_at;
            $hoursDue = max(0, (int) $lastPaidAt->diffInHours(now()));
            $hourlyWage = (int) $member->role?->hourly_wage;
            $amountDue = $hoursDue * $hourlyWage;

            if ($hoursDue < 1 || $amountDue < 1 || ! $member->business || ! $member->character) {
                continue;
            }

            DB::transaction(function () use ($member, $amountDue, $hoursDue, &$payments) {
                $business = $member->business()->lockForUpdate()->first();
                $character = $member->character()->lockForUpdate()->first();

                if (! $business || ! $character || $business->bank_credits < $amountDue) {
                    return;
                }

                $business->decrement('bank_credits', $amountDue);
                $character->increment('plastic_credits', $amountDue);
                $member->forceFill(['last_paid_at' => now()])->save();

                $business->logs()->create([
                    'actor_character_id' => $character->id,
                    'type' => 'wage_paid',
                    'amount' => -$amountDue,
                    'description' => "Paid {$character->name} {$amountDue} credits for {$hoursDue} hour(s).",
                ]);

                CharacterActivity::recordTransaction(
                    $character,
                    'business_wage',
                    $amountDue,
                    "Received {$amountDue} credits from {$business->name}."
                );

                $payments++;
            });
        }

        $this->info("Processed {$payments} business wage payments.");

        return self::SUCCESS;
    }
}
