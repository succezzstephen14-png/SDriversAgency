<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_form_renders_show_password_controls_for_both_password_fields(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('class="password-toggle" data-target="password" aria-label="Show password" aria-pressed="false"', false)
            ->assertSee('class="password-toggle" data-target="password_confirmation" aria-label="Show password" aria-pressed="false"', false)
            ->assertSee("event.target.closest('.password-toggle')", false)
            ->assertSee("targetInput.type = showPassword ? 'text' : 'password'", false);
    }
}
