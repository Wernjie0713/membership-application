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
        AddressType::firstOrCreate(['name' => 'Correspondence Address'], ['status' => 'active']);
    }

    public function test_authenticated_user_can_create_member_with_address_and_files(): void
    {
        $user = User::factory()->admin()->create();
        $addressType = AddressType::first();
        $referrer = Member::factory()->completed()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('members.store'), [
                'username' => 'johndoe',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
                'phone' => '12345678',
                'status' => Member::STATUS_ACTIVE,
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

    public function test_member_can_store_multiple_addresses_with_unique_types(): void
    {
        $user = User::factory()->member()->create([
            'email' => 'multi-address@example.com',
        ]);

        $types = AddressType::orderBy('name')->get();

        $response = $this
            ->actingAs($user)
            ->post(route('member.onboarding.store'), [
                'first_name' => 'Multi',
                'last_name' => 'Address',
                'phone' => '12345678',
                'date_of_birth' => '1995-01-01',
                'addresses' => [
                    [
                        'address_type_id' => $types[0]->id,
                        'line_1' => '123 Primary Street',
                        'line_2' => '',
                        'city' => 'Singapore',
                        'state' => 'Central',
                        'postal_code' => '123456',
                        'country' => 'Singapore',
                        'is_primary' => 1,
                    ],
                    [
                        'address_type_id' => $types[1]->id,
                        'line_1' => '99 Mailing Road',
                        'line_2' => '',
                        'city' => 'Johor Bahru',
                        'state' => 'Johor',
                        'postal_code' => '81110',
                        'country' => 'Malaysia',
                        'is_primary' => 0,
                    ],
                ],
            ]);

        $response->assertRedirect(route('member.dashboard'));

        $user->refresh();

        $this->assertCount(2, $user->member->addresses);
        $this->assertSame(1, $user->member->addresses()->where('is_primary', true)->count());
    }

    public function test_member_saved_addresses_remain_visible_after_reload(): void
    {
        $user = User::factory()->member()->create([
            'email' => 'reload-address@example.com',
        ]);

        $member = Member::factory()->completed()->create([
            'user_id' => $user->id,
            'email' => $user->email,
            'status' => Member::STATUS_ACTIVE,
        ]);

        $types = AddressType::orderBy('name')->get()->values();

        $member->addresses()->createMany([
            [
                'address_type_id' => $types[0]->id,
                'line_1' => '11 Jalan Pulai Perdana 3',
                'line_2' => 'Taman Sri Pulai Perdana',
                'city' => 'Johor Bahru',
                'state' => 'Johor',
                'postal_code' => '81110',
                'country' => 'Malaysia',
                'is_primary' => true,
            ],
            [
                'address_type_id' => $types[1]->id,
                'line_1' => '99 Mailing Road',
                'line_2' => 'Suite 8',
                'city' => 'Singapore',
                'state' => 'Central',
                'postal_code' => '048580',
                'country' => 'Singapore',
                'is_primary' => false,
            ],
        ]);

        $response = $this->actingAs($user)->get(route('member.profile.edit'));

        $response->assertOk();
        $response->assertSee('11 Jalan Pulai Perdana 3');
        $response->assertSee('99 Mailing Road');
        $response->assertSee('Johor Bahru');
        $response->assertSee('Singapore');
    }

    public function test_deleting_member_archives_linked_login_account(): void
    {
        $admin = User::factory()->admin()->create();
        $member = Member::factory()->completed()->create([
            'email' => 'delete-me@example.com',
            'status' => Member::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($admin)->delete(route('members.destroy', $member));

        $response->assertRedirect(route('members.index'));
        $this->assertSoftDeleted('members', ['id' => $member->id]);

        $user = $member->fresh()->user;

        $this->assertSame('delete-me@example.com', $user->email);
        $this->assertNotNull($user->deactivated_at);
        $this->assertSame(Member::STATUS_DEACTIVATED, $member->fresh()->status);
    }

    public function test_admin_can_sort_members_from_list_view(): void
    {
        $admin = User::factory()->admin()->create();

        Member::factory()->completed()->create([
            'first_name' => 'Zara',
            'last_name' => 'Tan',
            'status' => Member::STATUS_ACTIVE,
        ]);

        Member::factory()->completed()->create([
            'first_name' => 'Aaron',
            'last_name' => 'Lee',
            'status' => Member::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($admin)->get(route('members.index', ['sort' => 'name_asc']));

        $response->assertOk();
        $response->assertSeeInOrder(['Aaron Lee', 'Zara Tan']);
    }

    public function test_admin_can_change_member_status_from_list_action(): void
    {
        $admin = User::factory()->admin()->create();
        $member = Member::factory()->completed()->create([
            'status' => Member::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($admin)->patch(route('members.status.update', ['member' => $member, 'sort' => 'latest']), [
            'status' => Member::STATUS_DEACTIVATED,
        ]);

        $response->assertRedirect(route('members.index', ['sort' => 'latest']));
        $this->assertSoftDeleted('members', [
            'id' => $member->id,
            'status' => Member::STATUS_DEACTIVATED,
        ]);
    }

    public function test_admin_can_reactivate_a_deactivated_member_from_list_action(): void
    {
        $admin = User::factory()->admin()->create();
        $member = Member::factory()->completed()->create([
            'status' => Member::STATUS_ACTIVE,
        ]);

        $member->user->update([
            'deactivated_at' => now(),
        ]);

        $member->update([
            'status' => Member::STATUS_DEACTIVATED,
        ]);

        $member->delete();

        $response = $this->actingAs($admin)->patch(route('members.status.update', ['member' => $member, 'sort' => 'latest']), [
            'status' => Member::STATUS_ACTIVE,
        ]);

        $response->assertRedirect(route('members.index', ['sort' => 'latest']));

        $member->refresh();

        $this->assertNull($member->deleted_at);
        $this->assertSame(Member::STATUS_ACTIVE, $member->status);
        $this->assertNull($member->user->fresh()->deactivated_at);
    }

    public function test_deactivated_members_still_show_in_admin_member_list(): void
    {
        $admin = User::factory()->admin()->create();

        $activeMember = Member::factory()->completed()->create([
            'first_name' => 'Active',
            'last_name' => 'Member',
            'status' => Member::STATUS_ACTIVE,
        ]);

        $deactivatedMember = Member::factory()->completed()->create([
            'first_name' => 'Deactivated',
            'last_name' => 'Member',
            'status' => Member::STATUS_DEACTIVATED,
        ]);

        $deactivatedMember->user->update([
            'deactivated_at' => now(),
        ]);

        $deactivatedMember->delete();

        $response = $this->actingAs($admin)->get(route('members.index'));

        $response->assertOk();
        $response->assertSee($activeMember->full_name);
        $response->assertSee($deactivatedMember->full_name);
        $response->assertSee('Deactivated');
    }

    public function test_authenticated_user_can_update_member_profile_image(): void
    {
        $admin = User::factory()->admin()->create();
        $member = Member::factory()->completed()->create([
            'first_name' => 'Profile',
            'last_name' => 'Image',
            'status' => Member::STATUS_ACTIVE,
        ]);

        $address = $member->addresses()->create([
            'address_type_id' => AddressType::first()->id,
            'line_1' => '123 Update Street',
            'line_2' => 'Unit 12',
            'city' => 'Singapore',
            'state' => 'Central',
            'postal_code' => '123456',
            'country' => 'Singapore',
            'is_primary' => true,
        ]);

        $response = $this
            ->actingAs($admin)
            ->put(route('members.update', $member), [
                'username' => $member->user->username,
                'first_name' => 'Profile',
                'last_name' => 'Image',
                'email' => $member->email,
                'phone' => $member->phone,
                'status' => Member::STATUS_ACTIVE,
                'date_of_birth' => optional($member->date_of_birth)->toDateString(),
                'addresses' => [[
                    'id' => $address->id,
                    'address_type_id' => $address->address_type_id,
                    'line_1' => $address->line_1,
                    'line_2' => $address->line_2,
                    'city' => $address->city,
                    'state' => $address->state,
                    'postal_code' => $address->postal_code,
                    'country' => $address->country,
                    'is_primary' => 1,
                ]],
                'profile_image' => UploadedFile::fake()->image('cropped-avatar.png', 512, 512),
            ]);

        $response->assertRedirect(route('members.show', $member));

        $member->refresh();

        $this->assertNotNull($member->profileImage);
        Storage::disk('public')->assertExists($member->profileImage->path);
    }

    public function test_saved_proof_of_address_persists_and_renders_on_member_edit_page(): void
    {
        $user = User::factory()->member()->create([
            'email' => 'proof@example.com',
        ]);

        $member = Member::factory()->completed()->create([
            'user_id' => $user->id,
            'email' => $user->email,
            'status' => Member::STATUS_ACTIVE,
        ]);

        $address = $member->addresses()->create([
            'address_type_id' => AddressType::first()->id,
            'line_1' => '88 Archive Road',
            'line_2' => 'Level 2',
            'city' => 'Johor Bahru',
            'state' => 'Johor',
            'postal_code' => '81110',
            'country' => 'Malaysia',
            'is_primary' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->patch(route('member.profile.update'), [
                'first_name' => $member->first_name,
                'last_name' => $member->last_name,
                'phone' => $member->phone,
                'date_of_birth' => optional($member->date_of_birth)->toDateString(),
                'referral_code' => '',
                'addresses' => [[
                    'id' => $address->id,
                    'address_type_id' => $address->address_type_id,
                    'line_1' => $address->line_1,
                    'line_2' => $address->line_2,
                    'city' => $address->city,
                    'state' => $address->state,
                    'postal_code' => $address->postal_code,
                    'country' => $address->country,
                    'is_primary' => 1,
                    'proof_of_address' => UploadedFile::fake()->image('proof-of-address.png'),
                ]],
            ]);

        $response->assertRedirect(route('member.dashboard'));

        $address->refresh();

        $this->assertNotNull($address->proofDocument);
        Storage::disk('public')->assertExists($address->proofDocument->path);

        $editResponse = $this->actingAs($user)->get(route('member.profile.edit'));

        $editResponse->assertOk();
        $editResponse->assertSee('proof-of-address.png');
        $editResponse->assertSee('Open file');
    }

    public function test_member_can_update_profile_image_from_profile_modal_endpoint(): void
    {
        $user = User::factory()->member()->create([
            'email' => 'member-image@example.com',
        ]);

        $member = Member::factory()->completed()->create([
            'user_id' => $user->id,
            'email' => $user->email,
            'status' => Member::STATUS_ACTIVE,
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('member.profile.image.update'), [
                'profile_image' => UploadedFile::fake()->image('modal-cropped-avatar.png', 512, 512),
            ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'message',
                'profile_image_url',
                'profile_image_name',
            ]);

        $member->refresh();

        $this->assertNotNull($member->profileImage);
        Storage::disk('public')->assertExists($member->profileImage->path);
    }

    public function test_admin_can_update_member_profile_image_from_image_endpoint(): void
    {
        $admin = User::factory()->admin()->create();
        $member = Member::factory()->completed()->create([
            'status' => Member::STATUS_ACTIVE,
        ]);

        $response = $this
            ->actingAs($admin)
            ->post(route('members.image.update', $member), [
                'profile_image' => UploadedFile::fake()->image('admin-avatar.png', 512, 512),
            ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'message',
                'profile_image_url',
                'profile_image_name',
            ]);

        $member->refresh();

        $this->assertNotNull($member->profileImage);
        Storage::disk('public')->assertExists($member->profileImage->path);
    }
}
