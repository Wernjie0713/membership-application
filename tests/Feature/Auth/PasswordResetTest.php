<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $response = $this->post('/forgot-password', ['email' => $user->email]);

        $response->assertSessionHas('status', \App\Http\Controllers\Auth\PasswordResetLinkController::RESET_REQUESTED_MESSAGE);
        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_reset_password_request_returns_generic_message_for_unknown_email(): void
    {
        Notification::fake();

        $response = $this->post('/forgot-password', ['email' => 'unknown@example.com']);

        $response->assertSessionHas('status', \App\Http\Controllers\Auth\PasswordResetLinkController::RESET_REQUESTED_MESSAGE);
        Notification::assertNothingSent();
    }

    public function test_deactivated_users_do_not_receive_password_reset_links(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'deactivated_at' => now(),
        ]);

        $response = $this->post('/forgot-password', ['email' => $user->email]);

        $response->assertSessionHas('status', \App\Http\Controllers\Auth\PasswordResetLinkController::RESET_REQUESTED_MESSAGE);
        Notification::assertNothingSent();
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
            $response = $this->get('/reset-password/'.$notification->token);

            $response->assertStatus(200);

            return true;
        });
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $response = $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

            $response
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('login'));

            return true;
        });
    }

    public function test_password_reset_notification_uses_the_configured_app_url(): void
    {
        Notification::fake();

        config()->set('app.url', 'https://members.example.com');

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $mailMessage = $notification->toMail($user);

            $this->assertStringStartsWith('https://members.example.com/reset-password/', $mailMessage->actionUrl);

            return true;
        });
    }
}
