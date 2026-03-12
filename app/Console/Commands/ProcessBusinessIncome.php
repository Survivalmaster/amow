<?php

namespace App\Console\Commands;

use App\Models\Character;
use App\Support\CharacterActivity;
use Illuminate\Console\Command;

class ProcessBusinessIncome extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'amow:process-business-income';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Grant daily passive income to business owners.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $eligible = Character::query()
            ->where('is_business_owner', true)
            ->whereHas('licences', fn ($query) => $query->where('slug', 'business-owner'))
            ->get();

        $paid = 0;

        foreach ($eligible as $character) {
            if ($character->last_business_payout_at?->isToday()) {
                continue;
            }

            $amount = 50;
            $character->increment('plastic_credits', $amount);
            $character->forceFill(['last_business_payout_at' => now()])->save();
            CharacterActivity::recordTransaction($character, 'passive_income', $amount, 'Business owner daily payout.');
            $paid++;
        }

        $this->info("Processed {$paid} business payouts.");
    }
}
