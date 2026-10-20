<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_dashboard_renders_one_mobile_menu_toggle_and_a_backdrop(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('id="dashboardToggle"', false)
            ->assertSee('id="sidebarBackdrop"', false)
            ->assertSee('aria-controls="dashboardSidebar"', false);

        $this->assertSame(1, substr_count($response->getContent(), 'id="dashboardToggle"'));
    }
}
