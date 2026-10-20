<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfilePasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_change_password_from_settings(): void
    {
        $user = User::factory()->create([
            'password' => 'CurrentPassword1!',
        ]);

        $this->actingAs($user)
            ->put(route('profile.password.update'), [
                'current_password' => 'CurrentPassword1!',
                'password' => 'NewSecurePassword1!',
                'password_confirmation' => 'NewSecurePassword1!',
            ])
            ->assertRedirect(route('profile'))
            ->assertSessionHas('success', 'Password updated successfully.');

        $this->assertTrue(Hash::check('NewSecurePassword1!', $user->fresh()->password));
    }

    public function test_password_cannot_be_changed_with_an_incorrect_current_password(): void
    {
        $user = User::factory()->create([
            'password' => 'CurrentPassword1!',
        ]);

        $this->actingAs($user)
            ->put(route('profile.password.update'), [
                'current_password' => 'IncorrectPassword1!',
                'password' => 'NewSecurePassword1!',
                'password_confirmation' => 'NewSecurePassword1!',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('CurrentPassword1!', $user->fresh()->password));
    }

    public function test_password_change_requires_authentication(): void
    {
        $this->put(route('profile.password.update'), [
            'current_password' => 'CurrentPassword1!',
            'password' => 'NewSecurePassword1!',
            'password_confirmation' => 'NewSecurePassword1!',
        ])->assertRedirect(route('login'));
    }
}
