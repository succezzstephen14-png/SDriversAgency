<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SeparatedAdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_regular_users_are_redirected_from_public_site_pages_to_the_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get(route('home'))
            ->assertRedirect(route('dashboard'));

        $this->actingAs($user)
            ->get(route('vehicles.index'))
            ->assertRedirect(route('dashboard'));

        $this->actingAs($user)
            ->get(route('cms.page', ['page' => 'about']))
            ->assertRedirect(route('dashboard'));
    }

    public function test_homepage_hero_guides_drivers_and_vehicle_owners_to_relevant_actions(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Your Next Opportunity')
            ->assertSee('Starts Here.')
            ->assertSee('Professional drivers. Trusted partnerships. Opportunities that move you forward.')
            ->assertSee('Are You a Driver?')
            ->assertSee('Find opportunities, build your future, and work towards owning your own vehicle.')
            ->assertSee('Find Driving Jobs')
            ->assertSee(route('recruitments.index'), false)
            ->assertSee('Work &amp; Pay', false)
            ->assertSee(route('service.register.vehicle'), false)
            ->assertSee('Own a Vehicle?')
            ->assertSee('Connect with professional drivers and discover services designed around your vehicle.')
            ->assertSee('Register a Vehicle')
            ->assertSee(route('vehicles.create'), false)
            ->assertSee('Find a Driver')
            ->assertSee(route('drivers.index'), false)
            ->assertSee('Get Started')
            ->assertSee(route('register'), false)
            ->assertSee('Explore opportunities')
            ->assertSee('Explore owner services')
            ->assertSee('Looking for a Driver?')
            ->assertSee('Request a professional driver for your business and keep your people and operations moving.')
            ->assertSee('Request a Driver')
            ->assertSee(route('service.login', ['requestType' => 'driver']), false)
            ->assertSee('Sign in to request a driver')
            ->assertSee('aria-roledescription="carousel"', false)
            ->assertSee('aria-label="Previous audience"', false)
            ->assertSee('aria-label="Next audience"', false)
            ->assertSee('id="audienceCarouselToggle"', false)
            ->assertSee('Pause automatic slides')
            ->assertSee('6500')
            ->assertSee('Show business driver requests')
            ->assertSee('audienceTrack.style.transform');
    }

    public function test_regular_users_can_still_access_their_dashboard_and_authenticated_routes(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('vehicles.create'))
            ->assertOk();
    }

    public function test_admin_login_and_dashboard_use_separate_paths_on_the_configured_domain(): void
    {
        $adminLoginUrl = route('admin.login');
        $adminDashboardUrl = route('admin.dashboard');

        $this->assertSame(config('admin.domain'), parse_url($adminLoginUrl, PHP_URL_HOST));
        $this->assertSame('/admin/login', parse_url($adminLoginUrl, PHP_URL_PATH));
        $this->assertSame('/login', parse_url(route('login'), PHP_URL_PATH));
        $this->assertSame(config('admin.domain'), parse_url($adminDashboardUrl, PHP_URL_HOST));

        $this->get($adminLoginUrl)->assertOk();
    }

    public function test_unauthenticated_admin_access_redirects_to_admin_login(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'));

        $this->get(route('admin.home'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_sign_in_on_the_configured_domain_and_open_the_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'password' => Hash::make('AdminPassword1!'),
        ]);

        $this->post(route('admin.login.post'), [
            'email' => $admin->email,
            'password' => 'AdminPassword1!',
        ])->assertRedirect(route('admin.dashboard'));

        $this->get(route('admin.dashboard'))->assertOk();
    }

    public function test_admin_credentials_on_regular_login_are_sent_to_the_admin_portal_without_a_session(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'password' => Hash::make('AdminPassword1!'),
        ]);

        $this->post(route('login.post'), [
            'email' => $admin->email,
            'password' => 'AdminPassword1!',
        ])->assertRedirect(route('admin.login'));

        $this->assertGuest();
    }

    public function test_regular_users_cannot_open_the_admin_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }
}
