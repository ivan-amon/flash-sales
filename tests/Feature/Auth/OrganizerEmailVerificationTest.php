<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\Organizer;
use App\Notifications\QueuedVerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrganizerEmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    // ==================
    // REGISTRATION
    // ==================

    public function test_registration_sends_verification_notification(): void
    {
        Notification::fake();

        $this->postJson('/api/organizer/register', [
            'official_name' => 'OrgName',
            'email' => 'org@example.com',
            'phone' => '+1234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertStatus(201);

        $organizer = Organizer::where('email', 'org@example.com')->firstOrFail();

        $this->assertNull($organizer->email_verified_at);
        Notification::assertSentTo($organizer, QueuedVerifyEmail::class);
    }

    // ==================
    // VERIFY
    // ==================

    public function test_organizer_can_verify_email_with_valid_signed_url(): void
    {
        $organizer = Organizer::factory()->unverified()->create();

        $this->get($this->verificationUrl($organizer))
            ->assertRedirect(config('app.frontend_url').'/email/verified');

        $this->assertNotNull($organizer->fresh()->email_verified_at);
    }

    public function test_verification_fails_with_invalid_hash(): void
    {
        $organizer = Organizer::factory()->unverified()->create();

        $url = URL::temporarySignedRoute('organizer.verification.verify', now()->addMinutes(60), [
            'id' => $organizer->id,
            'hash' => sha1('wrong@example.com'),
        ]);

        $this->get($url)->assertStatus(403);

        $this->assertNull($organizer->fresh()->email_verified_at);
    }

    public function test_verification_fails_with_invalid_signature(): void
    {
        $organizer = Organizer::factory()->unverified()->create();

        $url = $this->verificationUrl($organizer).'tampered';

        $this->get($url)->assertStatus(403);

        $this->assertNull($organizer->fresh()->email_verified_at);
    }

    public function test_already_verified_organizer_is_redirected_without_error(): void
    {
        $organizer = Organizer::factory()->create();

        $this->get($this->verificationUrl($organizer))
            ->assertRedirect(config('app.frontend_url').'/email/verified');
    }

    // ==================
    // RESEND
    // ==================

    public function test_organizer_can_resend_verification_email(): void
    {
        Notification::fake();

        $organizer = Organizer::factory()->unverified()->create();
        Sanctum::actingAs($organizer, ['is_organizer']);

        $this->postJson('/api/organizer/email/verification-notification')
            ->assertStatus(200)
            ->assertJson(['message' => 'Verification link sent.']);

        Notification::assertSentTo($organizer, QueuedVerifyEmail::class);
    }

    public function test_resend_for_verified_organizer_returns_already_verified(): void
    {
        Notification::fake();

        $organizer = Organizer::factory()->create();
        Sanctum::actingAs($organizer, ['is_organizer']);

        $this->postJson('/api/organizer/email/verification-notification')
            ->assertStatus(200)
            ->assertJson(['message' => 'Email already verified.']);

        Notification::assertNothingSent();
    }

    public function test_guest_cannot_resend_verification_email(): void
    {
        $this->postJson('/api/organizer/email/verification-notification')->assertStatus(401);
    }

    // ==================
    // PROTECTED ROUTES
    // ==================

    public function test_unverified_organizer_cannot_access_verified_routes(): void
    {
        $organizer = Organizer::factory()->unverified()->create();
        Sanctum::actingAs($organizer, ['is_organizer']);

        $this->getJson('/api/organizer/events')->assertStatus(403);
    }

    public function test_verified_organizer_can_access_verified_routes(): void
    {
        $organizer = Organizer::factory()->create();
        Sanctum::actingAs($organizer, ['is_organizer']);

        $this->getJson('/api/organizer/events')->assertStatus(200);
    }

    /**
     * Build a valid signed verification URL for the given organizer.
     */
    private function verificationUrl(Organizer $organizer): string
    {
        return URL::temporarySignedRoute('organizer.verification.verify', now()->addMinutes(60), [
            'id' => $organizer->id,
            'hash' => sha1($organizer->getEmailForVerification()),
        ]);
    }
}
