<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminController;
use App\Models\Booking;
use App\Models\Driver;
use App\Models\Recruitment;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

class AdminApprovalEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_driver_approval_sends_email_to_owner(): void
    {
        $driverOwner = User::factory()->create([
            'name' => 'Driver Owner',
            'email' => 'owner@example.com',
            'role' => 'user',
        ]);

        $driver = Driver::factory()->create([
            'user_id' => $driverOwner->id,
            'approval_status' => 'pending',
        ]);

        Mail::shouldReceive('raw')
            ->once()
            ->withArgs(function (string $text, callable $callback) use ($driverOwner) {
                $this->assertStringContainsString("Hello {$driverOwner->name},", $text);
                $this->assertStringContainsString('available for admin review', $text);
                $this->assertStringContainsString('will not be shown on the public website', $text);

                $mailMessage = Mockery::mock();
                $mailMessage->shouldReceive('to')->once()->with($driverOwner->email)->andReturnSelf();
                $mailMessage->shouldReceive('subject')->once()->with('Driver profile approved')->andReturnSelf();

                $callback($mailMessage);

                return true;
            });

        $controller = app(AdminController::class);
        $controller->approveDriver($driver);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $driverOwner->id,
            'title' => 'Driver profile approved',
            'type' => 'system',
        ]);
    }

    public function test_booking_approval_succeeds_when_smtp_certificate_verification_fails(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create(['role' => 'user']);
        $booking = Booking::create([
            'user_id' => $owner->id,
            'booking_type' => 'driver',
            'booking_date' => now(),
            'status' => 'pending',
            'approval_status' => 'pending',
            'payment_status' => 'pending',
        ]);

        Mail::shouldReceive('raw')
            ->once()
            ->andThrow(new TransportException('SSL certificate verify failed'));
        Log::shouldReceive('error')
            ->once()
            ->withArgs(function (string $message, array $context): bool {
                return str_contains($message, 'email')
                    && str_contains($context['error'], 'SSL certificate verify failed');
            });

        $this->actingAs($admin)
            ->post(route('admin.bookings.approve', $booking))
            ->assertRedirect()
            ->assertSessionHas('success', 'Booking approved!')
            ->assertSessionHas('warning', 'The booking was approved, but the notification email could not be sent. Check the mail server certificate configuration and application logs.');

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'approval_status' => 'approved',
            'status' => 'confirmed',
        ]);
    }

    public function test_recruitment_application_approval_sends_only_a_recruitment_decision(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $applicant = User::factory()->create(['role' => 'user', 'name' => 'Stephen Abuki']);
        $recruitmentOwner = User::factory()->create(['role' => 'admin']);
        $recruitment = Recruitment::create([
            'user_id' => $recruitmentOwner->id,
            'title' => 'Cleaner',
            'description' => 'Cleaner opportunity',
            'position_type' => 'cleaner',
            'location' => 'Accra',
            'status' => 'open',
            'approval_status' => 'approved',
            'posted_at' => now(),
        ]);
        $booking = Booking::create([
            'user_id' => $applicant->id,
            'recruitment_id' => $recruitment->id,
            'booking_type' => 'recruitment',
            'application_role' => 'cleaner',
            'booking_date' => now(),
            'description' => 'Good',
            'status' => 'pending',
            'approval_status' => 'pending',
            'payment_status' => 'paid',
        ]);

        Mail::shouldReceive('raw')
            ->once()
            ->withArgs(function (string $body, callable $callback) use ($applicant): bool {
                $this->assertStringContainsString('Your recruitment application has been approved.', $body);
                $this->assertStringNotContainsString('Your booking request', $body);
                $this->assertStringNotContainsString('DRIVER DETAILS', $body);
                $this->assertStringNotContainsString('VEHICLE DETAILS', $body);

                $mailMessage = Mockery::mock();
                $mailMessage->shouldReceive('to')->once()->with($applicant->email)->andReturnSelf();
                $mailMessage->shouldReceive('subject')->once()->with('Your recruitment application has been approved')->andReturnSelf();
                $callback($mailMessage);

                return true;
            });

        $this->actingAs($admin)
            ->post(route('admin.bookings.approve', $booking))
            ->assertRedirect()
            ->assertSessionHas('success', 'Booking approved!');
    }

    public function test_recruitment_review_ajax_approval_uses_the_recruitment_decision_email(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $applicant = User::factory()->create(['role' => 'user']);
        $booking = Booking::create([
            'user_id' => $applicant->id,
            'booking_type' => 'recruitment',
            'booking_date' => now(),
            'application_role' => 'cleaner',
            'status' => 'pending',
            'approval_status' => 'pending',
            'payment_status' => 'paid',
        ]);

        Mail::shouldReceive('raw')
            ->once()
            ->withArgs(function (string $body, callable $callback) use ($applicant): bool {
                $this->assertStringContainsString('Your recruitment application has been approved.', $body);
                $this->assertStringNotContainsString('Your booking request', $body);

                $mailMessage = Mockery::mock();
                $mailMessage->shouldReceive('to')->once()->with($applicant->email)->andReturnSelf();
                $mailMessage->shouldReceive('subject')->once()->with('Your recruitment application has been approved')->andReturnSelf();
                $callback($mailMessage);

                return true;
            });

        $this->actingAs($admin)
            ->postJson(route('admin.ajax.approve'), [
                'type' => 'booking',
                'id' => $booking->id,
            ])
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_driver_booking_approval_email_contains_driver_details_only(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $applicant = User::factory()->create(['role' => 'user']);
        $driver = Driver::factory()->create([
            'license_number' => 'DL-12345678',
            'experience_years' => 7,
        ]);
        $booking = Booking::create([
            'user_id' => $applicant->id,
            'driver_id' => $driver->id,
            'booking_type' => 'driver',
            'booking_date' => now(),
            'status' => 'pending',
            'approval_status' => 'pending',
            'payment_status' => 'pending',
        ]);

        Mail::shouldReceive('raw')
            ->once()
            ->withArgs(function (string $body, callable $callback) use ($applicant): bool {
                $this->assertStringContainsString('=== DRIVER DETAILS ===', $body);
                $this->assertStringContainsString('DL-12345678', $body);
                $this->assertStringNotContainsString('VEHICLE DETAILS', $body);
                $this->assertStringContainsString('Thank you for choosing SUCCEZZ Drivers Agency. If you have any questions, please contact our support team.', $body);

                $mailMessage = Mockery::mock();
                $mailMessage->shouldReceive('to')->once()->with($applicant->email)->andReturnSelf();
                $mailMessage->shouldReceive('subject')->once()->with('Your driver booking has been approved - SUCCEZZ Drivers Agency')->andReturnSelf();
                $callback($mailMessage);

                return true;
            });

        $this->actingAs($admin)
            ->post(route('admin.bookings.approve', $booking))
            ->assertRedirect();
    }

    public function test_vehicle_booking_approval_email_contains_vehicle_details_only(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $applicant = User::factory()->create(['role' => 'user']);
        $vehicle = Vehicle::factory()->create([
            'make' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2022,
            'license_plate' => 'GT-1234-22',
            'capacity' => 5,
        ]);
        $booking = Booking::create([
            'user_id' => $applicant->id,
            'vehicle_id' => $vehicle->id,
            'booking_type' => 'vehicle',
            'booking_date' => now(),
            'status' => 'pending',
            'approval_status' => 'pending',
            'payment_status' => 'pending',
        ]);

        Mail::shouldReceive('raw')
            ->once()
            ->withArgs(function (string $body, callable $callback) use ($applicant): bool {
                $this->assertStringContainsString('=== VEHICLE DETAILS ===', $body);
                $this->assertStringContainsString('Toyota Corolla', $body);
                $this->assertStringContainsString('GT-1234-22', $body);
                $this->assertStringNotContainsString('DRIVER DETAILS', $body);
                $this->assertStringContainsString('Thank you for choosing SUCCEZZ Drivers Agency. If you have any questions, please contact our support team.', $body);

                $mailMessage = Mockery::mock();
                $mailMessage->shouldReceive('to')->once()->with($applicant->email)->andReturnSelf();
                $mailMessage->shouldReceive('subject')->once()->with('Your vehicle booking has been approved - SUCCEZZ Drivers Agency')->andReturnSelf();
                $callback($mailMessage);

                return true;
            });

        $this->actingAs($admin)
            ->post(route('admin.bookings.approve', $booking))
            ->assertRedirect();
    }

    public function test_admin_layout_displays_email_delivery_warnings(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->withSession(['warning' => 'Email delivery failed'])
            ->get(route('admin.bookings'))
            ->assertOk()
            ->assertSee('toastr.warning')
            ->assertSee('Email delivery failed');
    }
}
