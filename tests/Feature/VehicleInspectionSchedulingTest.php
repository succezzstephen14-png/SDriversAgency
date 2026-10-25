<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class VehicleInspectionSchedulingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_schedule_vehicle_inspection_and_notify_owner(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create(['phone' => '+233 24 123 4567']);
        $vehicle = Vehicle::factory()->create([
            'user_id' => $owner->id,
            'make' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2022,
            'approval_status' => 'pending',
        ]);
        $inspectionDate = now()->addDays(7)->toDateString();
        Mail::shouldReceive('raw')
            ->once()
            ->withArgs(fn ($body, $callback) => str_contains($body, now()->addDays(7)->format('F j, Y')) && is_callable($callback));

        $this->actingAs($admin)
            ->put(route('admin.vehicles.inspection.schedule', $vehicle), [
                'inspection_appointment_date' => $inspectionDate,
            ])
            ->assertRedirect(route('admin.vehicles.detail', $vehicle))
            ->assertSessionHas('success');

        $this->assertSame($inspectionDate, $vehicle->fresh()->inspection_appointment_date->toDateString());
        $this->assertDatabaseHas('notifications', [
            'user_id' => $owner->id,
            'title' => 'Vehicle inspection scheduled',
            'type' => 'system',
        ]);

        $this->actingAs($owner)
            ->get(route('vehicles.pending-vehicles'))
            ->assertOk()
            ->assertSee($vehicle->fresh()->inspection_appointment_date->format('M j, Y'));
    }

    public function test_admin_vehicle_review_shows_registration_details_and_owner_contact(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create([
            'name' => 'Vehicle Owner',
            'email' => 'vehicle-owner@example.test',
            'phone' => '+233 55 111 2233',
        ]);
        $vehicle = Vehicle::factory()->create([
            'user_id' => $owner->id,
            'make' => 'Toyota',
            'model' => 'Hilux',
            'year' => 2021,
            'registration_number' => 'REG-2021-55',
            'vin' => '1HGCM82633A004352',
            'color' => 'Blue',
            'fuel_type' => 'diesel',
            'transmission' => 'manual',
            'engine_size' => '2.8L',
            'capacity' => 5,
            'number_of_seats' => 5,
            'mileage' => 42000,
            'condition' => 'very_good',
            'registration_date' => '2021-05-10',
            'registration_expiry' => '2027-05-10',
            'insurance_expiry' => '2027-06-10',
            'inspection_expiry' => '2027-07-10',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.vehicles.detail', $vehicle))
            ->assertOk()
            ->assertSee('Vehicle Owner')
            ->assertSee('vehicle-owner@example.test')
            ->assertSee('+233 55 111 2233')
            ->assertSee('REG-2021-55')
            ->assertSee('Diesel')
            ->assertSee('Manual')
            ->assertSee('2.8L')
            ->assertSee('42,000 km')
            ->assertSee('Registration Date')
            ->assertSee('Roadworthiness Expiry')
            ->assertSee('Schedule Vehicle Inspection');
    }

    public function test_inspection_date_must_not_be_in_the_past(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $vehicle = Vehicle::factory()->create();

        $this->actingAs($admin)
            ->from(route('admin.vehicles.detail', $vehicle))
            ->put(route('admin.vehicles.inspection.schedule', $vehicle), [
                'inspection_appointment_date' => now()->subDay()->toDateString(),
            ])
            ->assertRedirect(route('admin.vehicles.detail', $vehicle))
            ->assertSessionHasErrors('inspection_appointment_date');

        $this->assertNull($vehicle->fresh()->inspection_appointment_date);
        $this->assertSame(0, Notification::where('title', 'Vehicle inspection scheduled')->count());
    }
}
