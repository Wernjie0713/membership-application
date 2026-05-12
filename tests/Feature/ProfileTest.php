<?php

namespace Tests\Feature;

use App\Models\AddressType;
use App\Models\Member;
use App\Models\Promotion;
use App\Models\PromotionRewardTier;
use App\Models\RewardAchiever;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        AddressType::firstOrCreate(['name' => 'Residential Address'], ['status' => 'active']);
    }

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();
        $originalEmailVerifiedAt = $user->email_verified_at;

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'username' => 'testuser',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('testuser', $user->username);
        $this->assertSame($user->email, $user->fresh()->email);
        $this->assertEquals($originalEmailVerifiedAt, $user->fresh()->email_verified_at);
    }

    public function test_email_remains_unchanged_when_profile_is_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'username' => 'testuser',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertSame($user->email, $user->fresh()->email);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->member()->create([
            'email' => 'self-delete@example.com',
        ]);

        $member = Member::factory()->completed()->create([
            'user_id' => $user->id,
            'email' => $user->email,
            'status' => Member::STATUS_ACTIVE,
        ]);

        $address = $member->addresses()->create([
            'address_type_id' => AddressType::first()->id,
            'line_1' => '123 Delete Street',
            'line_2' => 'Unit 9',
            'city' => 'Singapore',
            'state' => 'Central',
            'postal_code' => '123456',
            'country' => 'Singapore',
            'is_primary' => true,
        ]);

        $member->documents()->create([
            'type' => 'profile_image',
            'disk' => 'public',
            'path' => UploadedFile::fake()->image('avatar.jpg')->store('members/profile-images', 'public'),
            'original_name' => 'avatar.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 1200,
        ]);

        $address->documents()->create([
            'type' => 'proof_of_address',
            'disk' => 'public',
            'path' => UploadedFile::fake()->image('proof.jpg')->store('members/proof-of-address', 'public'),
            'original_name' => 'proof.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 1400,
        ]);

        $promotion = Promotion::create([
            'name' => 'Delete Test Promotion',
            'description' => 'Promotion used for self-delete tests.',
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addDay()->toDateString(),
            'status' => 'active',
        ]);

        $tier = PromotionRewardTier::create([
            'promotion_id' => $promotion->id,
            'tier' => 1,
            'referral_threshold' => 10,
            'reward_amount' => 100,
            'currency' => 'USD',
            'is_recurring' => false,
            'step_increment' => null,
        ]);

        $reward = RewardAchiever::create([
            'member_id' => $member->id,
            'promotion_id' => $promotion->id,
            'promotion_reward_tier_id' => $tier->id,
            'threshold_reached' => 10,
            'referral_count' => 10,
            'reward_amount' => 100,
            'currency' => 'USD',
            'earned_at' => now(),
        ]);

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());

        $member->refresh();
        $reward->refresh();

        $this->assertSoftDeleted('members', ['id' => $member->id]);
        $this->assertSame('Deleted', $member->first_name);
        $this->assertSame('Member', $member->last_name);
        $this->assertNull($member->user_id);
        $this->assertNull($member->phone);
        $this->assertNull($member->date_of_birth);
        $this->assertSame(Member::STATUS_DEACTIVATED, $member->status);
        $this->assertStringEndsWith('@deleted.local', $member->email);
        $this->assertDatabaseMissing('addresses', ['member_id' => $member->id]);
        $this->assertDatabaseCount('documents', 0);
        $this->assertDatabaseHas('reward_achievers', ['id' => $reward->id, 'member_id' => $member->id]);
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }

    public function test_original_email_can_be_reused_after_self_delete(): void
    {
        $user = User::factory()->member()->create([
            'email' => 'reuse@example.com',
        ]);

        Member::factory()->completed()->create([
            'user_id' => $user->id,
            'email' => $user->email,
            'status' => Member::STATUS_ACTIVE,
        ]);

        $this->actingAs($user)->delete('/profile', [
            'password' => 'password',
        ])->assertRedirect('/');

        $replacement = User::factory()->member()->create([
            'email' => 'reuse@example.com',
        ]);

        $this->assertNotNull($replacement);
        $this->assertSame('reuse@example.com', $replacement->email);
    }
}
