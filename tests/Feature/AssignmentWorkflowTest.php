<?php

namespace Tests\Feature;

use App\Models\AssignmentActivity;
use App\Models\Booking;
use App\Models\Driver;
use App\Models\Notification;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssignmentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_driver_customer_and_admin_can_manage_assignment_updates_and_feedback(): void
    {
        $customer = User::factory()->create(['role' => 'service_customer']);
        $bookingCustomer = User::factory()->create(['role' => 'user']);
        $driverUser = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $driver = Driver::factory()->create([
            'user_id' => $driverUser->id,
            'approval_status' => 'approved',
            'status' => 'available',
        ]);
        $vehicle = Vehicle::factory()->create([
            'approval_status' => 'approved',
            'availability_status' => 'available',
            'vehicle_type' => 'van',
        ]);
        $driverRequest = ServiceRequest::create([
            'user_id' => $customer->id,
            'request_type' => 'driver',
            'service_type' => 'personal_driver',
            'location' => 'Accra',
            'status' => 'paid',
            'assigned_driver_id' => $driver->id,
        ]);
        $vehicleRequest = ServiceRequest::create([
            'user_id' => $customer->id,
            'request_type' => 'vehicle',
            'service_type' => 'work_and_pay',
            'vehicle_type' => 'van',
            'location' => 'Kumasi',
            'status' => 'paid',
            'assigned_vehicle_id' => $vehicle->id,
        ]);
        $booking = Booking::create([
            'user_id' => $bookingCustomer->id,
            'driver_id' => $driver->id,
            'booking_type' => 'driver',
            'status' => 'confirmed',
            'approval_status' => 'approved',
            'booking_date' => now()->addDay(),
            'location' => 'Tema',
            'amount' => 100,
            'payment_status' => 'paid',
        ]);

        $this->actingAs($driverUser)
            ->get(route('driver.assignments.index'))
            ->assertOk()
            ->assertSee($customer->name)
            ->assertSee('Service request')
            ->assertSee('Booking');

        $this->actingAs($driverUser)
            ->post(route('assignments.activity', ['type' => 'service-request', 'id' => $driverRequest->id]), [
                'entry_type' => 'progress',
                'status' => 'in_progress',
                'message' => 'The assigned driver has started the service.',
            ])
            ->assertSessionHas('success');

        $this->actingAs($driverUser)
            ->post(route('assignments.activity', ['type' => 'service-request', 'id' => $driverRequest->id]), [
                'entry_type' => 'driver_report',
                'message' => 'Please review a concern about the service recipient.',
            ])
            ->assertSessionHas('success');

        $this->actingAs($customer)
            ->get(route('service.requests.index'))
            ->assertOk()
            ->assertSee('The assigned driver has started the service.')
            ->assertDontSee('Please review a concern about the service recipient.');

        $this->actingAs($customer)
            ->post(route('service.assignments.activity', ['type' => 'service-request', 'id' => $driverRequest->id]), [
                'entry_type' => 'customer_report',
                'rating' => 5,
                'message' => 'The driver arrived on time and was professional.',
            ])
            ->assertSessionHas('success');

        $this->actingAs($customer)
            ->get(route('service.requests.index'))
            ->assertOk()
            ->assertSee('The driver arrived on time and was professional.')
            ->assertDontSee('Please review a concern about the service recipient.');

        $this->actingAs($bookingCustomer)
            ->get(route('bookings.show', $booking))
            ->assertOk()
            ->assertSee('Send private report to admin');

        $this->actingAs($bookingCustomer)
            ->post(route('assignments.activity', ['type' => 'booking', 'id' => $booking->id]), [
                'entry_type' => 'customer_report',
                'rating' => 3,
                'message' => 'Please review a concern about the assigned service.',
            ])
            ->assertSessionHas('success');

        $this->actingAs($driverUser)
            ->post(route('assignments.activity', ['type' => 'booking', 'id' => $booking->id]), [
                'entry_type' => 'driver_report',
                'message' => 'I sent a private report for the booking.',
            ])
            ->assertSessionHas('success');

        $this->actingAs($admin)
            ->get(route('admin.assignments.index'))
            ->assertOk()
            ->assertSee($customer->name)
            ->assertSee($driverUser->name)
            ->assertSee($vehicle->license_plate)
            ->assertSee('Please review a concern about the service recipient.')
            ->assertSee('Please review a concern about the assigned service.')
            ->assertSee('I sent a private report for the booking.')
            ->assertSee('The driver arrived on time and was professional.');

        $this->actingAs($admin)
            ->post(route('admin.assignments.activity', ['type' => 'service-request', 'id' => $driverRequest->id]), [
                'entry_type' => 'driver_assessment',
                'rating' => 4,
                'message' => 'Safe handling and good communication during the practical assessment.',
            ])
            ->assertSessionHas('success');

        $this->actingAs($admin)
            ->post(route('admin.assignments.activity', ['type' => 'booking', 'id' => $booking->id]), [
                'entry_type' => 'admin_feedback',
                'message' => 'The report was reviewed and follow-up was completed.',
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('assignment_activities', [
            'assignable_type' => ServiceRequest::class,
            'assignable_id' => (string) $driverRequest->id,
            'entry_type' => 'driver_assessment',
            'rating' => 4,
        ]);
        $this->assertDatabaseHas('assignment_activities', [
            'assignable_type' => Booking::class,
            'assignable_id' => $booking->id,
            'entry_type' => 'admin_feedback',
        ]);
        $this->assertDatabaseHas('assignment_activities', [
            'assignable_type' => Booking::class,
            'assignable_id' => $booking->id,
            'entry_type' => 'customer_report',
            'rating' => 3,
        ]);
        $this->assertGreaterThanOrEqual(2, Notification::where('type', 'assignment_report')->where('user_id', $admin->id)->count());
        $this->assertDatabaseHas('notifications', [
            'user_id' => $bookingCustomer->id,
            'type' => 'assignment_feedback',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $driverUser->id,
            'title' => 'Driver assessment recorded',
        ]);

        $this->actingAs($driverUser)
            ->get(route('driver.assignments.index'))
            ->assertOk()
            ->assertSee('Safe handling and good communication during the practical assessment.');

        $this->assertSame(5, AssignmentActivity::where('entry_type', 'customer_report')->value('rating'));
        $this->assertSame(4, AssignmentActivity::where('entry_type', 'driver_assessment')->value('rating'));
    }

    public function test_only_the_assigned_customer_driver_or_admin_can_submit_assignment_activity(): void
    {
        $customer = User::factory()->create();
        $otherUser = User::factory()->create();
        $driverUser = User::factory()->create();
        $driver = Driver::factory()->create([
            'user_id' => $driverUser->id,
            'approval_status' => 'approved',
        ]);
        $serviceRequest = ServiceRequest::create([
            'user_id' => $customer->id,
            'request_type' => 'driver',
            'service_type' => 'personal_driver',
            'location' => 'Accra',
            'status' => 'paid',
            'assigned_driver_id' => $driver->id,
        ]);

        $this->actingAs($otherUser)
            ->post(route('assignments.activity', ['type' => 'service-request', 'id' => $serviceRequest->id]), [
                'entry_type' => 'customer_report',
                'message' => 'Unauthorized report.',
            ])
            ->assertForbidden();

        $this->actingAs($customer)
            ->post(route('assignments.activity', ['type' => 'service-request', 'id' => $serviceRequest->id]), [
                'entry_type' => 'driver_report',
                'message' => 'Unauthorized driver report.',
            ])
            ->assertForbidden();

        $this->actingAs($driverUser)
            ->post(route('assignments.activity', ['type' => 'service-request', 'id' => $serviceRequest->id]), [
                'entry_type' => 'admin_feedback',
                'message' => 'Unauthorized admin feedback.',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('assignment_activities', 0);
    }
}
