<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_sends_reset_notification_and_displays_success(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'reset@example.com']);

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertRedirect()
            ->assertSessionHas('success', 'Password reset link sent to your email.');

        Notification::assertSentTo($user, ResetPassword::class);

        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('Password reset link sent to your email.');
    }

    public function test_forgot_password_displays_error_when_reset_link_cannot_be_sent(): void
    {
        $this->post(route('password.email'), ['email' => 'missing@example.com'])
            ->assertRedirect()
            ->assertSessionHasErrors('email');

        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('Unable to send reset link. Please try again.');
    }
}
