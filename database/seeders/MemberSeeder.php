<?php

namespace Database\Seeders;

use App\Models\AddressType;
use App\Models\Member;
use App\Models\Promotion;
use App\Services\PromotionRewardService;
use Illuminate\Database\Seeder;

class MemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $residential = AddressType::firstWhere('name', 'Residential Address');
        $correspondence = AddressType::firstWhere('name', 'Correspondence Address');

        $memberA = Member::factory()->completed()->create([
            'first_name' => 'Alice',
            'last_name' => 'Tan',
            'email' => 'alice@example.com',
            'status' => 'approved',
        ]);

        $memberB = Member::factory()->completed()->create([
            'first_name' => 'Ben',
            'last_name' => 'Lee',
            'email' => 'ben@example.com',
            'status' => 'approved',
            'referrer_id' => $memberA->id,
            'created_at' => now()->subDays(20),
        ]);

        $memberC = Member::factory()->completed()->create([
            'first_name' => 'Cindy',
            'last_name' => 'Ng',
            'email' => 'cindy@example.com',
            'status' => 'approved',
            'referrer_id' => $memberB->id,
            'created_at' => now()->subDays(18),
        ]);

        $memberD = Member::factory()->completed()->create([
            'first_name' => 'Daniel',
            'last_name' => 'Goh',
            'email' => 'daniel@example.com',
            'status' => 'approved',
            'referrer_id' => $memberB->id,
            'created_at' => now()->subDays(16),
        ]);

        $memberE = Member::factory()->completed()->create([
            'first_name' => 'Eva',
            'last_name' => 'Lim',
            'email' => 'eva@example.com',
            'status' => 'approved',
            'referrer_id' => $memberD->id,
            'created_at' => now()->subDays(14),
        ]);

        foreach ([$memberA, $memberB, $memberC, $memberD, $memberE] as $member) {
            $member->addresses()->createMany([
                [
                    'address_type_id' => $residential?->id,
                    'line_1' => fake()->streetAddress(),
                    'city' => fake()->city(),
                    'state' => fake()->state(),
                    'postal_code' => fake()->postcode(),
                    'country' => 'Singapore',
                    'is_primary' => true,
                ],
                [
                    'address_type_id' => $correspondence?->id,
                    'line_1' => fake()->streetAddress(),
                    'city' => fake()->city(),
                    'state' => fake()->state(),
                    'postal_code' => fake()->postcode(),
                    'country' => 'Singapore',
                    'is_primary' => false,
                ],
            ]);
        }

        Member::factory(8)->completed()->create([
            'referrer_id' => $memberA->id,
            'status' => 'approved',
            'created_at' => now()->subDays(10),
        ]);

        Member::factory(45)->completed()->create([
            'referrer_id' => $memberB->id,
            'status' => 'approved',
            'created_at' => now()->subDays(8),
        ]);

        $promotion = Promotion::first();

        if ($promotion) {
            app(PromotionRewardService::class)->processPromotion($promotion, now());
        }
    }
}
