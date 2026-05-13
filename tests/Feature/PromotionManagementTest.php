<?php

namespace Tests\Feature;

use App\Models\Promotion;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromotionManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_user_cannot_access_admin_promotion_routes(): void
    {
        $user = User::factory()->member()->create();

        $response = $this->actingAs($user)->get(route('promotions.index'));

        $response->assertForbidden();
    }

    public function test_admin_can_sort_promotions_from_list_view(): void
    {
        $admin = User::factory()->admin()->create();

        Promotion::create([
            'name' => 'Zulu Campaign',
            'description' => 'Late alphabet',
            'status' => 'draft',
            'start_date' => now()->addDays(10)->toDateString(),
            'end_date' => now()->addDays(20)->toDateString(),
        ]);

        Promotion::create([
            'name' => 'Alpha Launch',
            'description' => 'Early alphabet',
            'status' => 'active',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(5)->toDateString(),
        ]);

        $response = $this->actingAs($admin)->get(route('promotions.index', ['sort' => 'name_asc']));

        $response->assertOk();
        $response->assertSeeInOrder(['Alpha Launch', 'Zulu Campaign']);
    }

    public function test_admin_can_filter_promotions_by_status(): void
    {
        $admin = User::factory()->admin()->create();

        Promotion::create([
            'name' => 'Draft Promotion',
            'description' => 'Planning',
            'status' => 'draft',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(7)->toDateString(),
        ]);

        Promotion::create([
            'name' => 'Active Promotion',
            'description' => 'Running',
            'status' => 'active',
            'start_date' => now()->subDays(2)->toDateString(),
            'end_date' => now()->addDays(10)->toDateString(),
        ]);

        $response = $this->actingAs($admin)->get(route('promotions.index', ['status' => 'active']));

        $response->assertOk();
        $response->assertSee('Active Promotion');
        $response->assertDontSee('Draft Promotion');
    }

    public function test_admin_can_change_promotion_status_from_list_action(): void
    {
        $admin = User::factory()->admin()->create();

        $promotion = Promotion::create([
            'name' => 'Seasonal Drive',
            'description' => 'Initial draft',
            'status' => 'draft',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(30)->toDateString(),
        ]);

        $response = $this->actingAs($admin)->patch(route('promotions.status.update', ['promotion' => $promotion, 'sort' => 'latest']), [
            'status' => 'active',
        ]);

        $response->assertRedirect(route('promotions.index', ['sort' => 'latest']));
        $this->assertDatabaseHas('promotions', [
            'id' => $promotion->id,
            'status' => 'active',
        ]);
    }

    public function test_admin_cannot_delete_promotion_with_configured_tiers(): void
    {
        $admin = User::factory()->admin()->create();

        $promotion = Promotion::create([
            'name' => 'Tiered Promotion',
            'description' => 'Has configured tiers',
            'status' => 'draft',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(30)->toDateString(),
        ]);

        $promotion->rewardTiers()->create([
            'tier' => 1,
            'referral_threshold' => 10,
            'reward_amount' => 100,
            'currency' => 'USD',
            'is_recurring' => false,
            'step_increment' => null,
        ]);

        $response = $this->actingAs($admin)->delete(route('promotions.destroy', $promotion));

        $response->assertRedirect(route('promotions.edit', $promotion));
        $this->assertDatabaseHas('promotions', ['id' => $promotion->id]);
    }

    public function test_admin_cannot_delete_promotion_with_reward_history(): void
    {
        $admin = User::factory()->admin()->create();

        $promotion = Promotion::create([
            'name' => 'Historic Promotion',
            'description' => 'Has reward history',
            'status' => 'inactive',
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->subDay()->toDateString(),
        ]);

        $tier = $promotion->rewardTiers()->create([
            'tier' => 1,
            'referral_threshold' => 10,
            'reward_amount' => 100,
            'currency' => 'USD',
            'is_recurring' => false,
            'step_increment' => null,
        ]);

        $member = Member::factory()->completed()->create();

        $member->rewardAchievers()->create([
            'promotion_id' => $promotion->id,
            'promotion_reward_tier_id' => $tier->id,
            'threshold_reached' => 10,
            'referral_count' => 10,
            'reward_amount' => 100,
            'currency' => 'USD',
            'earned_at' => now()->subDays(2),
        ]);

        $response = $this->actingAs($admin)->delete(route('promotions.destroy', $promotion));

        $response->assertRedirect(route('promotions.edit', $promotion));
        $this->assertDatabaseHas('promotions', ['id' => $promotion->id]);
    }
}
