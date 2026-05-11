<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Promotion;
use App\Models\User;
use App\Services\PromotionRewardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RewardReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_reward_report_page_renders_and_shows_processed_rewards(): void
    {
        $user = User::factory()->admin()->create();
        $promotion = Promotion::create([
            'name' => 'Quarterly Referral Drive',
            'status' => 'active',
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonth(),
        ]);

        $promotion->rewardTiers()->createMany([
            ['tier' => 1, 'referral_threshold' => 10, 'reward_amount' => 100, 'currency' => 'USD', 'is_recurring' => false, 'step_increment' => null],
            ['tier' => 4, 'referral_threshold' => 110, 'reward_amount' => 150, 'currency' => 'USD', 'is_recurring' => true, 'step_increment' => 10],
        ]);

        $member = Member::factory()->completed()->create([
            'first_name' => 'Alice',
            'last_name' => 'Tan',
            'status' => 'approved',
        ]);
        Member::factory(12)->completed()->create([
            'referrer_id' => $member->id,
            'status' => 'approved',
            'created_at' => now()->subDays(5),
        ]);

        app(PromotionRewardService::class)->processPromotion($promotion, now());

        $response = $this->actingAs($user)->get(route('rewards.index'));

        $response->assertOk();
        $response->assertSee('Alice Tan');
        $response->assertSee('Quarterly Referral Drive');
        $response->assertSee('100.00');
    }

    public function test_reward_processing_ignores_incomplete_member_profiles(): void
    {
        $promotion = Promotion::create([
            'name' => 'Incomplete Referral Drive',
            'status' => 'active',
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonth(),
        ]);

        $promotion->rewardTiers()->create([
            'tier' => 1,
            'referral_threshold' => 1,
            'reward_amount' => 100,
            'currency' => 'USD',
            'is_recurring' => false,
        ]);

        $member = Member::factory()->completed()->create();
        Member::factory()->create([
            'referrer_id' => $member->id,
            'created_at' => now()->subDay(),
        ]);

        $created = app(PromotionRewardService::class)->processPromotion($promotion, now());

        $this->assertSame(0, $created);
        $this->assertDatabaseCount('reward_achievers', 0);
    }

    public function test_reward_processing_ignores_non_approved_referred_members(): void
    {
        $promotion = Promotion::create([
            'name' => 'Approval Referral Drive',
            'status' => 'active',
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonth(),
        ]);

        $promotion->rewardTiers()->create([
            'tier' => 1,
            'referral_threshold' => 1,
            'reward_amount' => 100,
            'currency' => 'USD',
            'is_recurring' => false,
        ]);

        $member = Member::factory()->completed()->create(['status' => 'approved']);
        Member::factory()->completed()->create([
            'referrer_id' => $member->id,
            'status' => 'pending',
            'created_at' => now()->subDay(),
        ]);

        $created = app(PromotionRewardService::class)->processPromotion($promotion, now());

        $this->assertSame(0, $created);
        $this->assertDatabaseCount('reward_achievers', 0);
    }

    public function test_member_user_cannot_access_reward_report(): void
    {
        $user = User::factory()->member()->create();

        $response = $this->actingAs($user)->get(route('rewards.index'));

        $response->assertForbidden();
    }
}
