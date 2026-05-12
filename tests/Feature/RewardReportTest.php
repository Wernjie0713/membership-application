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
            'status' => Member::STATUS_ACTIVE,
        ]);
        Member::factory(12)->completed()->create([
            'referrer_id' => $member->id,
            'status' => Member::STATUS_ACTIVE,
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

    public function test_reward_processing_ignores_deactivated_referred_members(): void
    {
        $promotion = Promotion::create([
            'name' => 'Deactivated Referral Drive',
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

        $member = Member::factory()->completed()->create(['status' => Member::STATUS_ACTIVE]);
        Member::factory()->completed()->create([
            'referrer_id' => $member->id,
            'status' => Member::STATUS_DEACTIVATED,
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

    public function test_reward_report_can_filter_by_promotion(): void
    {
        $user = User::factory()->admin()->create();

        $member = Member::factory()->completed()->create([
            'first_name' => 'Chris',
            'last_name' => 'Ng',
            'status' => Member::STATUS_ACTIVE,
        ]);

        $primaryPromotion = Promotion::create([
            'name' => 'Primary Promotion',
            'status' => 'active',
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonth(),
        ]);

        $secondaryPromotion = Promotion::create([
            'name' => 'Secondary Promotion',
            'status' => 'active',
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonth(),
        ]);

        $primaryTier = $primaryPromotion->rewardTiers()->create([
            'tier' => 1,
            'referral_threshold' => 10,
            'reward_amount' => 100,
            'currency' => 'USD',
            'is_recurring' => false,
            'step_increment' => null,
        ]);

        $secondaryTier = $secondaryPromotion->rewardTiers()->create([
            'tier' => 1,
            'referral_threshold' => 5,
            'reward_amount' => 50,
            'currency' => 'USD',
            'is_recurring' => false,
            'step_increment' => null,
        ]);

        $member->rewardAchievers()->create([
            'promotion_id' => $primaryPromotion->id,
            'promotion_reward_tier_id' => $primaryTier->id,
            'threshold_reached' => 10,
            'referral_count' => 10,
            'reward_amount' => 100,
            'currency' => 'USD',
            'earned_at' => now()->subDay(),
        ]);

        $member->rewardAchievers()->create([
            'promotion_id' => $secondaryPromotion->id,
            'promotion_reward_tier_id' => $secondaryTier->id,
            'threshold_reached' => 5,
            'referral_count' => 5,
            'reward_amount' => 50,
            'currency' => 'USD',
            'earned_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('rewards.index', [
            'promotion_id' => $primaryPromotion->id,
        ]));

        $response->assertOk();
        $response->assertSee('Primary Promotion');
        $response->assertSee('USD 100.00');
        $response->assertDontSee('USD 50.00');
    }

    public function test_reward_report_can_search_by_member_name(): void
    {
        $user = User::factory()->admin()->create();

        $promotion = Promotion::create([
            'name' => 'Searchable Campaign',
            'status' => 'active',
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonth(),
        ]);

        $tier = $promotion->rewardTiers()->create([
            'tier' => 1,
            'referral_threshold' => 10,
            'reward_amount' => 100,
            'currency' => 'USD',
            'is_recurring' => false,
            'step_increment' => null,
        ]);

        $matchingMember = Member::factory()->completed()->create([
            'first_name' => 'Nadia',
            'last_name' => 'Tan',
            'status' => Member::STATUS_ACTIVE,
        ]);

        $otherMember = Member::factory()->completed()->create([
            'first_name' => 'Oscar',
            'last_name' => 'Lim',
            'status' => Member::STATUS_ACTIVE,
        ]);

        $matchingMember->rewardAchievers()->create([
            'promotion_id' => $promotion->id,
            'promotion_reward_tier_id' => $tier->id,
            'threshold_reached' => 10,
            'referral_count' => 10,
            'reward_amount' => 100,
            'currency' => 'USD',
            'earned_at' => now()->subDay(),
        ]);

        $otherMember->rewardAchievers()->create([
            'promotion_id' => $promotion->id,
            'promotion_reward_tier_id' => $tier->id,
            'threshold_reached' => 12,
            'referral_count' => 12,
            'reward_amount' => 120,
            'currency' => 'USD',
            'earned_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('rewards.index', [
            'search' => 'Nadia',
        ]));

        $response->assertOk();
        $response->assertSee('Nadia Tan');
        $response->assertDontSee('USD 120.00');
    }
}
