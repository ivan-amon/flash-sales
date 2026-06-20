<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\Organizer;
use App\Notifications\QueuedResetOrganizerPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class OrganizerPasswordResetTest extends TestCase
{
    use RefreshDatabase;

    // ==================
    // FORGOT
    // ==================

    public function test_forgot_password_sends_reset_notification(): void
    {
        Notification::fake();

        $organizer = Organizer::factory()->create();

        $this->postJson('/api/organizer/password/forgot', ['email' => $organizer->email])
            ->assertStatus(200);

        Notification::assertSentTo($organizer, QueuedResetOrganizerPassword::class);
    }

    public function test_forgot_password_for_unknown_email_returns_generic_response_without_sending(): void
    {
        Notification::fake();

        $this->postJson('/api/organizer/password/forgot', ['email' => 'nobody@example.com'])
            ->assertStatus(200);

        Notification::assertNothingSent();
    }

    public function test_forgot_password_requires_a_valid_email(): void
    {
        $this->postJson('/api/organizer/password/forgot', ['email' => 'not-an-email'])
            ->assertStatus(422);
    }

    public function test_reset_notification_links_to_organizer_frontend_url(): void
    {
        $organizer = Organizer::factory()->create();

        $mail = (new QueuedResetOrganizerPassword('the-token'))->toMail($organizer);

        $this->assertStringContainsString(config('app.frontend_url').'/organizer/password/reset', $mail->actionUrl);
        $this->assertStringContainsString('the-token', $mail->actionUrl);
        $this->assertStringContainsString(urlencode($organizer->email), $mail->actionUrl);
    }

    // ==================
    // RESET
    // ==================

    public function test_organizer_can_reset_password_with_valid_token(): void
    {
        $organizer = Organizer::factory()->create();
        $token = Password::broker('organizers')->createToken($organizer);

        $this->postJson('/api/organizer/password/reset', [
            'token' => $token,
            'email' => $organizer->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])
            ->assertStatus(200)
            ->assertJsonStructure(['organizer', 'token', 'message']);

        $this->assertTrue(Hash::check('new-password', $organizer->fresh()->password));

        $this->postJson('/api/organizer/login', [
            'email' => $organizer->email,
            'password' => 'password123',
        ])->assertStatus(422);
    }

    public function test_reset_revokes_all_existing_tokens_and_issues_a_new_one(): void
    {
        $organizer = Organizer::factory()->create();
        $oldPlainToken = $organizer->createToken('auth_token', ['is_organizer'])->plainTextToken;
        $organizer->createToken('auth_token', ['is_organizer']);

        $this->assertSame(2, $organizer->tokens()->count());

        $token = Password::broker('organizers')->createToken($organizer);

        $newPlainToken = $this->postJson('/api/organizer/password/reset', [
            'token' => $token,
            'email' => $organizer->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertStatus(200)->json('token');

        $this->assertSame(1, $organizer->fresh()->tokens()->count());

        $this->withToken($oldPlainToken)->getJson('/api/organizer')->assertStatus(401);
        $this->withToken($newPlainToken)->getJson('/api/organizer')->assertStatus(200);
    }

    public function test_reset_fails_with_invalid_token(): void
    {
        $organizer = Organizer::factory()->create();

        $this->postJson('/api/organizer/password/reset', [
            'token' => 'invalid-token',
            'email' => $organizer->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertStatus(422);

        $this->assertTrue(Hash::check('password123', $organizer->fresh()->password));
    }

    public function test_reset_fails_with_wrong_email(): void
    {
        $organizer = Organizer::factory()->create();
        $token = Password::broker('organizers')->createToken($organizer);

        $this->postJson('/api/organizer/password/reset', [
            'token' => $token,
            'email' => 'someone-else@example.com',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertStatus(422);
    }

    public function test_reset_fails_when_password_confirmation_mismatches(): void
    {
        $organizer = Organizer::factory()->create();
        $token = Password::broker('organizers')->createToken($organizer);

        $this->postJson('/api/organizer/password/reset', [
            'token' => $token,
            'email' => $organizer->email,
            'password' => 'new-password',
            'password_confirmation' => 'different-password',
        ])->assertStatus(422);
    }
}
