<?php

namespace Tests\Feature;

use App\Models\Driver;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class DriverTestSchedulingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_schedule_driving_test_and_notify_driver(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $driverUser = User::factory()->create();
        $driver = Driver::factory()->create([
            'user_id' => $driverUser->id,
            'approval_status' => 'pending',
        ]);
        $testDate = now()->addDays(7);
        Mail::shouldReceive('raw')
            ->once()
            ->withArgs(fn ($body, $callback) => str_contains($body, $testDate->format('F j, Y')) && is_callable($callback));

        $this->actingAs($admin)
            ->put(route('admin.drivers.drive-test.schedule', $driver), [
                'drive_test_appointment_date' => $testDate->toDateString(),
            ])
            ->assertRedirect(route('admin.drivers.detail', $driver))
            ->assertSessionHas('success');

        $this->assertSame($testDate->toDateString(), $driver->fresh()->drive_test_appointment_date->toDateString());
        $this->assertDatabaseHas('notifications', [
            'user_id' => $driverUser->id,
            'title' => 'Driving test scheduled',
            'type' => 'system',
        ]);

        $this->actingAs($driverUser)
            ->get(route('drivers.profile'))
            ->assertOk()
            ->assertSee($testDate->format('F j, Y'));
    }

    public function test_admin_driver_review_has_driving_test_schedule_form(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $driver = Driver::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.drivers.detail', $driver))
            ->assertOk()
            ->assertSee('Schedule Driving Test')
            ->assertSee('drive_test_appointment_date')
            ->assertSee('Issue test date');
    }

    public function test_driving_test_date_cannot_be_scheduled_in_the_past(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $driver = Driver::factory()->create();

        $this->actingAs($admin)
            ->from(route('admin.drivers.detail', $driver))
            ->put(route('admin.drivers.drive-test.schedule', $driver), [
                'drive_test_appointment_date' => now()->subDay()->toDateString(),
            ])
            ->assertRedirect(route('admin.drivers.detail', $driver))
            ->assertSessionHasErrors('drive_test_appointment_date');

        $this->assertNull($driver->fresh()->drive_test_appointment_date);
        $this->assertSame(0, Notification::where('title', 'Driving test scheduled')->count());
    }
}
