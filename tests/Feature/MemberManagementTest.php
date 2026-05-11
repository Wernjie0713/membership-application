<?php

namespace Tests\Feature;

use App\Models\AddressType;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MemberManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        AddressType::firstOrCreate(['name' => 'Residential Address'], ['status' => 'active']);
    }

    public function test_authenticated_user_can_create_member_with_address_and_files(): void
    {
        $user = User::factory()->admin()->create();
        $addressType = AddressType::first();
        $referrer = Member::factory()->completed()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('members.store'), [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
                'phone' => '12345678',
                'status' => 'approved',
                'referral_code' => $referrer->referral_code,
                'addresses' => [[
                    'address_type_id' => $addressType->id,
                    'line_1' => '123 Main Street',
                    'line_2' => 'Unit 5',
                    'city' => 'Singapore',
                    'state' => 'Central',
                    'postal_code' => '123456',
                    'country' => 'Singapore',
                    'is_primary' => 1,
                    'proof_of_address' => UploadedFile::fake()->create('proof.pdf', 120, 'application/pdf'),
                ]],
                'profile_image' => UploadedFile::fake()->image('avatar.jpg'),
            ]);

        $member = Member::where('email', 'john@example.com')->first();

        $response->assertRedirect(route('members.show', $member));
        $this->assertNotNull($member);
        $this->assertSame($referrer->id, $member->referrer_id);
        $this->assertNotNull($member->user_id);
        $this->assertTrue($member->user->isMember());
        $this->assertCount(1, $member->addresses);
        $this->assertDatabaseCount('documents', 2);
    }

    public function test_member_detail_page_shows_referral_tree(): void
    {
        $user = User::factory()->admin()->create();

        $memberA = Member::factory()->completed()->create(['first_name' => 'Alice', 'last_name' => 'Tan']);
        $memberB = Member::factory()->completed()->create(['first_name' => 'Ben', 'last_name' => 'Lee', 'referrer_id' => $memberA->id]);
        $memberC = Member::factory()->completed()->create(['first_name' => 'Cindy', 'last_name' => 'Ng', 'referrer_id' => $memberB->id]);
        $memberD = Member::factory()->completed()->create(['first_name' => 'Daniel', 'last_name' => 'Goh', 'referrer_id' => $memberB->id]);
        Member::factory()->completed()->create(['first_name' => 'Eva', 'last_name' => 'Lim', 'referrer_id' => $memberD->id]);

        $response = $this->actingAs($user)->get(route('members.show', $memberA));

        $response->assertOk();
        $response->assertSee('Ben Lee');
        $response->assertSee('Level 1');
        $response->assertSee('Cindy Ng');
        $response->assertSee('Daniel Goh');
        $response->assertSee('Level 2');
        $response->assertSee('Eva Lim');
        $response->assertSee('Level 3');
    }

    public function test_member_user_cannot_access_admin_member_routes(): void
    {
        $user = User::factory()->member()->create();

        $response = $this->actingAs($user)->get(route('members.index'));

        $response->assertForbidden();
    }

    public function test_member_can_complete_onboarding_and_get_linked_to_referrer(): void
    {
        $user = User::factory()->member()->create([
            'email' => 'member@example.com',
        ]);
        $addressType = AddressType::first();
        $referrer = Member::factory()->completed()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('member.onboarding.store'), [
                'first_name' => 'Member',
                'last_name' => 'User',
                'phone' => '12345678',
                'date_of_birth' => '1995-01-01',
                'referral_code' => $referrer->referral_code,
                'addresses' => [[
                    'address_type_id' => $addressType->id,
                    'line_1' => '45 Referral Street',
                    'line_2' => 'Unit 8',
                    'city' => 'Singapore',
                    'state' => 'Central',
                    'postal_code' => '456789',
                    'country' => 'Singapore',
                    'is_primary' => 1,
                ]],
            ]);

        $response->assertRedirect(route('member.dashboard'));

        $user->refresh();
        $member = $user->member;

        $this->assertNotNull($member);
        $this->assertSame($referrer->id, $member->referrer_id);
        $this->assertNotNull($member->referral_code);
    }

    public function test_deleting_member_archives_linked_login_account(): void
    {
        $admin = User::factory()->admin()->create();
        $member = Member::factory()->completed()->create([
            'email' => 'delete-me@example.com',
            'status' => 'approved',
        ]);

        $user = $member->user;

        $response = $this->actingAs($admin)->delete(route('members.destroy', $member));

        $response->assertRedirect(route('members.index'));
        $this->assertSoftDeleted('members', ['id' => $member->id]);
        $this->assertDatabaseMissing('users', ['email' => 'delete-me@example.com']);

        $user->refresh();

        $this->assertFalse($user->isMember());
        $this->assertStringStartsWith('deleted+member-'.$member->id.'-', $user->email);
        $this->assertStringEndsWith('@archived.local', $user->email);
    }
}
