<?php

namespace Tests\Feature\Auth;

use App\Models\Member;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_verification_screen_can_be_rendered(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/verify-email');

        $response->assertOk();
    }

    public function test_email_can_be_verified(): void
    {
        Event::fake();

        $user = User::factory()->member()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'id' => $user->id,
            'hash' => sha1($user->email),
        ]);

        $response = $this->actingAs($user)->get($verificationUrl);

        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $response->assertRedirect(route('member.onboarding.create', absolute: false).'?verified=1');
    }

    public function test_email_is_not_verified_with_invalid_hash(): void
    {
        $user = User::factory()->member()->unverified()->create();

        $this->actingAs($user)->get(route('verification.verify', [
            'id' => $user->id,
            'hash' => 'invalid-hash',
        ]));

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_unverified_member_is_redirected_to_verification_notice_when_accessing_member_dashboard(): void
    {
        $user = User::factory()->member()->unverified()->create();

        Member::factory()->completed()->create([
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        $response = $this->actingAs($user)->get(route('member.dashboard'));

        $response->assertRedirect(route('verification.notice', absolute: false));
    }
}
