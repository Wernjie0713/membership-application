<?php

namespace Database\Seeders;

use App\Models\Promotion;
use Illuminate\Database\Seeder;

class PromotionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $promotion = Promotion::updateOrCreate(
            ['name' => 'Spring Referral Drive'],
            [
                'description' => 'Sample promotion for referral milestones.',
                'start_date' => now()->subMonth()->toDateString(),
                'end_date' => now()->addMonth()->toDateString(),
                'status' => 'active',
            ]
        );

        $promotion->rewardTiers()->delete();

        $promotion->rewardTiers()->createMany([
            ['tier' => 1, 'referral_threshold' => 10, 'reward_amount' => 100, 'currency' => 'USD', 'is_recurring' => false, 'step_increment' => null],
            ['tier' => 2, 'referral_threshold' => 50, 'reward_amount' => 500, 'currency' => 'USD', 'is_recurring' => false, 'step_increment' => null],
            ['tier' => 3, 'referral_threshold' => 100, 'reward_amount' => 1000, 'currency' => 'USD', 'is_recurring' => false, 'step_increment' => null],
            ['tier' => 4, 'referral_threshold' => 110, 'reward_amount' => 150, 'currency' => 'USD', 'is_recurring' => true, 'step_increment' => 10],
        ]);
    }
}
