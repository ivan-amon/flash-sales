<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    // ==================
    // REGISTRATION
    // ==================

    public function test_registration_sends_verification_notification(): void
    {
        Notification::fake();

        $this->postJson('/api/register', [
            'name' => 'Ivan',
            'email' => 'ivan@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertStatus(201);

        $user = User::where('email', 'ivan@example.com')->firstOrFail();

        $this->assertNull($user->email_verified_at);
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    // ==================
    // VERIFY
    // ==================

    public function test_user_can_verify_email_with_valid_signed_url(): void
    {
        $user = User::factory()->unverified()->create();

        $this->get($this->verificationUrl($user))
            ->assertRedirect(config('app.frontend_url').'/email/verified');

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_verification_fails_with_invalid_hash(): void
    {
        $user = User::factory()->unverified()->create();

        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'id' => $user->id,
            'hash' => sha1('wrong@example.com'),
        ]);

        $this->get($url)->assertStatus(403);

        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_verification_fails_with_invalid_signature(): void
    {
        $user = User::factory()->unverified()->create();

        $url = $this->verificationUrl($user).'tampered';

        $this->get($url)->assertStatus(403);

        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_already_verified_user_is_redirected_without_error(): void
    {
        $user = User::factory()->create();

        $this->get($this->verificationUrl($user))
            ->assertRedirect(config('app.frontend_url').'/email/verified');
    }

    // ==================
    // RESEND
    // ==================

    public function test_user_can_resend_verification_email(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();
        Sanctum::actingAs($user, ['is_user']);

        $this->postJson('/api/email/verification-notification')
            ->assertStatus(200)
            ->assertJson(['message' => 'Verification link sent.']);

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_resend_for_verified_user_returns_already_verified(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        Sanctum::actingAs($user, ['is_user']);

        $this->postJson('/api/email/verification-notification')
            ->assertStatus(200)
            ->assertJson(['message' => 'Email already verified.']);

        Notification::assertNothingSent();
    }

    public function test_guest_cannot_resend_verification_email(): void
    {
        $this->postJson('/api/email/verification-notification')->assertStatus(401);
    }

    // ==================
    // PROTECTED ROUTES
    // ==================

    public function test_unverified_user_cannot_access_verified_routes(): void
    {
        $user = User::factory()->unverified()->create();
        Sanctum::actingAs($user, ['is_user']);

        $this->getJson('/api/orders')->assertStatus(403);
    }

    public function test_verified_user_can_access_verified_routes(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['is_user']);

        $this->getJson('/api/orders')->assertStatus(200);
    }

    /**
     * Build a valid signed verification URL for the given user.
     */
    private function verificationUrl(User $user): string
    {
        return URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'id' => $user->id,
            'hash' => sha1($user->getEmailForVerification()),
        ]);
    }
}
