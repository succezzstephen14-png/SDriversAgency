<?php

namespace Tests\Feature;

use App\Models\Driver;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class DriverVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_approved_drivers_are_not_exposed_on_public_pages_or_endpoints(): void
    {
        $driver = Driver::factory()->create([
            'approval_status' => 'approved',
            'status' => 'available',
        ]);

        $this->get(route('drivers.index'))->assertNotFound();
        $this->get(route('drivers.search'))->assertNotFound();
        $this->get(route('drivers.show', $driver))->assertNotFound();
        $this->get('/api/drivers/available')->assertNotFound();
        $this->get(route('booking.page', ['type' => 'driver', 'id' => $driver->id]))->assertNotFound();
        $this->get(route('guest.booking.page', ['type' => 'driver', 'id' => $driver->id]))->assertNotFound();

        $this->get(route('book.index'))
            ->assertOk()
            ->assertDontSee($driver->user->name);
    }

    public function test_driver_booking_payment_cannot_be_started_directly(): void
    {
        $this->post(route('booking.payment.initiate'), [
            'booking_type' => 'driver',
        ])->assertNotFound();
    }

    public function test_regular_users_cannot_see_driver_details_in_existing_bookings(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $driver = Driver::factory()->create([
            'approval_status' => 'approved',
            'status' => 'available',
        ]);
        $booking = Booking::create([
            'user_id' => $user->id,
            'driver_id' => $driver->id,
            'booking_type' => 'driver',
            'booking_date' => now(),
            'status' => 'confirmed',
            'approval_status' => 'approved',
            'payment_status' => 'paid',
        ]);

        $this->actingAs($user)
            ->get(route('bookings.show', $booking))
            ->assertOk()
            ->assertDontSee($driver->user->name)
            ->assertDontSee($driver->license_number);

        $this->actingAs($user)
            ->get(route('bookings.my-bookings'))
            ->assertOk()
            ->assertDontSee($driver->user->name);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee($driver->user->name);
    }

    public function test_admins_can_approve_and_review_drivers_without_publishing_them(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        $driver = Driver::factory()->create([
            'approval_status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.drivers.approve', $driver))
            ->assertRedirect();

        $this->assertDatabaseHas('drivers', [
            'id' => $driver->id,
            'approval_status' => 'approved',
            'status' => 'available',
        ]);

        $this->get(route('drivers.show', $driver))->assertNotFound();

        $this->actingAs($admin)
            ->get(route('admin.drivers.detail', $driver))
            ->assertOk()
            ->assertSee($driver->user->name);

        $this->actingAs($admin)
            ->get(route('admin.drivers'))
            ->assertOk()
            ->assertSee($driver->user->name);
    }

    public function test_admin_can_save_driver_vetting_checks_without_blocking_approval(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        $driver = Driver::factory()->create(['approval_status' => 'pending']);

        $this->actingAs($admin)
            ->get(route('admin.drivers.detail', $driver))
            ->assertOk()
            ->assertSee('Driver vetting checklist')
            ->assertSee('Two referees and completed reference checks')
            ->assertSee('Signed consent and privacy notice for screening');

        $this->actingAs($admin)
            ->put(route('admin.drivers.vetting-checks.update', $driver), [
                'checks' => [
                    'application_form' => '1',
                    'ghana_card_verified' => '1',
                ],
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame([
            'application_form' => true,
            'ghana_card_verified' => true,
            'license_verified' => false,
            'passport_photo' => false,
            'contact_details' => false,
            'references' => false,
            'employment' => false,
            'police_check' => false,
            'emergency_contact' => false,
            'code_of_conduct' => false,
            'confidentiality' => false,
            'driving_assessment' => false,
            'screening_consent' => false,
        ], $driver->fresh()->vetting_checks);

        $this->actingAs($admin)
            ->post(route('admin.drivers.approve', $driver))
            ->assertRedirect();

        $this->assertDatabaseHas('drivers', [
            'id' => $driver->id,
            'approval_status' => 'approved',
        ]);
    }
}
