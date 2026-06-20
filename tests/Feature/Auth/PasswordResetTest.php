<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\QueuedResetUserPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    // ==================
    // FORGOT
    // ==================

    public function test_forgot_password_sends_reset_notification(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->postJson('/api/password/forgot', ['email' => $user->email])
            ->assertStatus(200);

        Notification::assertSentTo($user, QueuedResetUserPassword::class);
    }

    public function test_forgot_password_for_unknown_email_returns_generic_response_without_sending(): void
    {
        Notification::fake();

        $this->postJson('/api/password/forgot', ['email' => 'nobody@example.com'])
            ->assertStatus(200);

        Notification::assertNothingSent();
    }

    public function test_forgot_password_requires_a_valid_email(): void
    {
        $this->postJson('/api/password/forgot', ['email' => 'not-an-email'])
            ->assertStatus(422);
    }

    public function test_reset_notification_links_to_user_frontend_url(): void
    {
        $user = User::factory()->create();

        $mail = (new QueuedResetUserPassword('the-token'))->toMail($user);

        $this->assertStringContainsString(config('app.frontend_url').'/password/reset', $mail->actionUrl);
        $this->assertStringContainsString('the-token', $mail->actionUrl);
        $this->assertStringContainsString(urlencode($user->email), $mail->actionUrl);
    }

    // ==================
    // RESET
    // ==================

    public function test_user_can_reset_password_with_valid_token(): void
    {
        $user = User::factory()->create();
        $token = Password::broker('users')->createToken($user);

        $this->postJson('/api/password/reset', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])
            ->assertStatus(200)
            ->assertJsonStructure(['user', 'token', 'message']);

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertStatus(422);
    }

    public function test_reset_revokes_all_existing_tokens_and_issues_a_new_one(): void
    {
        $user = User::factory()->create();
        $oldPlainToken = $user->createToken('auth_token', ['is_user'])->plainTextToken;
        $user->createToken('auth_token', ['is_user']);

        $this->assertSame(2, $user->tokens()->count());

        $token = Password::broker('users')->createToken($user);

        $newPlainToken = $this->postJson('/api/password/reset', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertStatus(200)->json('token');

        $this->assertSame(1, $user->fresh()->tokens()->count());

        $this->withToken($oldPlainToken)->getJson('/api/user')->assertStatus(401);
        $this->withToken($newPlainToken)->getJson('/api/user')->assertStatus(200);
    }

    public function test_reset_fails_with_invalid_token(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/password/reset', [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertStatus(422);

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_reset_fails_with_wrong_email(): void
    {
        $user = User::factory()->create();
        $token = Password::broker('users')->createToken($user);

        $this->postJson('/api/password/reset', [
            'token' => $token,
            'email' => 'someone-else@example.com',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertStatus(422);
    }

    public function test_reset_fails_when_password_confirmation_mismatches(): void
    {
        $user = User::factory()->create();
        $token = Password::broker('users')->createToken($user);

        $this->postJson('/api/password/reset', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'different-password',
        ])->assertStatus(422);
    }
}
