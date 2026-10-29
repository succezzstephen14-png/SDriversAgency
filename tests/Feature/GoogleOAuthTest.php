<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

class GoogleOAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_sign_in_starts_on_the_same_host_as_its_callback(): void
    {
        config([
            'services.google.client_id' => 'test-client-id',
            'services.google.client_secret' => 'test-client-secret',
            'services.google.redirect' => 'http://127.0.0.1:8000/auth/google/callback',
        ]);

        Socialite::shouldReceive('driver')->never();

        $this->get('http://localhost:8000/auth/google')
            ->assertRedirect('http://127.0.0.1:8000/auth/google');
    }

    public function test_google_callback_logs_in_an_existing_user_and_redirects_to_the_dashboard(): void
    {
        $user = User::factory()->create(['email' => 'person@example.com']);
        $googleUser = Mockery::mock(SocialiteUser::class);
        $googleUser->shouldReceive('getEmail')->once()->andReturn('person@example.com');

        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')->once()->andReturn($googleUser);
        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($provider);

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_google_registration_saves_the_role_selected_before_oauth(): void
    {
        $googleUser = new \Laravel\Socialite\Two\User();
        $googleUser->email = 'new-driver@example.com';
        $googleUser->name = 'New Driver';
        $googleUser->user = ['email_verified' => true];

        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')->once()->andReturn($googleUser);
        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($provider);

        $this->withSession(['registration.role' => 'driver'])
            ->get(route('auth.google.callback'))
            ->assertRedirect(route('verification.notice'));

        $this->assertDatabaseHas('users', [
            'email' => 'new-driver@example.com',
            'role' => 'driver',
        ]);
    }

    public function test_google_verified_email_is_marked_verified_for_an_existing_user(): void
    {
        $user = User::factory()->unverified()->create(['email' => 'verified@example.com']);
        $googleUser = new \Laravel\Socialite\Two\User();
        $googleUser->email = 'verified@example.com';
        $googleUser->user = ['email_verified' => true];

        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')->once()->andReturn($googleUser);
        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($provider);

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_google_callback_reports_failure_and_logs_the_diagnostic(): void
    {
        Log::shouldReceive('error')
            ->once()
            ->with('Google sign-in callback failed.', Mockery::on(
                fn (array $context) => $context['exception'] === \RuntimeException::class
                    && $context['message'] === 'OAuth token exchange failed.'
            ));

        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')->once()->andThrow(new \RuntimeException('OAuth token exchange failed.'));
        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($provider);

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('social');
    }

    public function test_google_sign_in_fails_with_a_clear_message_when_credentials_are_missing(): void
    {
        config([
            'services.google.client_id' => null,
            'services.google.client_secret' => null,
        ]);

        Log::shouldReceive('error')
            ->once()
            ->with('Google sign-in is unavailable because OAuth credentials are not configured.');

        $this->get(route('auth.google'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('social');
    }
}
