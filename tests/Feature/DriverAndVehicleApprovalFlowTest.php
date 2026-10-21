<?php

namespace Tests\Feature;

use App\Models\Driver;
use App\Models\Payment;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\QueryException;
use Tests\TestCase;

class DriverAndVehicleApprovalFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_only_submit_one_driver_profile(): void
    {
        $user = User::factory()->create();
        Driver::factory()->create([
            'user_id' => $user->id,
            'approval_status' => 'pending',
            'payment_status' => 'paid',
        ]);

        $this->actingAs($user)
            ->get(route('drivers.create'))
            ->assertRedirect(route('drivers.profile'));

        $this->actingAs($user)
            ->post(route('driver.payment.initiate'))
            ->assertRedirect(route('drivers.profile'));

        $this->assertDatabaseCount('drivers', 1);
        $this->assertDatabaseCount('payments', 0);

        try {
            Driver::factory()->create(['user_id' => $user->id]);
            $this->fail('The database must prevent a second driver profile for the same user.');
        } catch (QueryException) {
            $this->assertDatabaseCount('drivers', 1);
        }
    }

    public function test_paid_driver_and_vehicle_submissions_show_review_status_on_user_dashboard(): void
    {
        Mail::fake();
        $user = User::factory()->create();
        $payment = Payment::create([
            'user_id' => $user->id,
            'reference' => 'DRV-APPROVAL-FLOW',
            'amount' => 20,
            'type' => 'driver',
            'status' => 'completed',
            'data' => [
                'license_number' => 'ADB-12345678-12345',
                'license_expiry' => now()->addYear()->toDateString(),
                'license_class' => 'B',
                'experience_years' => 5,
                'emergency_person' => 'Emergency Contact',
                'emergency_phone' => '0200000000',
                'bio' => 'Experienced driver.',
                'profile_picture' => 'documents/profile.jpg',
                'license_file' => 'documents/license.pdf',
                'ghana_card_no' => 'GHA-123456789-0',
                'ghana_card_file' => 'documents/ghana-card.pdf',
                'guarantor' => 'First Guarantor',
                'guarantor_phone' => '0200000001',
                'g_ghana_card_no' => 'GHA-123456780-1',
                'guarantor_ghana_card_file' => 'documents/guarantor-id.pdf',
                'guarantor_passport_file' => 'documents/guarantor.jpg',
                'relationship' => 'family',
                'second_guarantor' => 'Second Guarantor',
                'second_guarantor_phone' => '0200000002',
                'second_g_ghana_card_no' => 'GHA-123456781-2',
                'second_guarantor_ghana_card_file' => 'documents/second-guarantor-id.pdf',
                'second_guarantor_passport_file' => 'documents/second-guarantor.jpg',
                'second_relationship' => 'friend',
            ],
        ]);

        $this->actingAs($user)
            ->get(route('payment.success', ['reference' => $payment->reference]))
            ->assertRedirect(route('dashboard'));

        $driver = Driver::where('user_id', $user->id)->firstOrFail();
        $this->assertSame('pending', $driver->approval_status);
        $this->assertSame('paid', $driver->payment_status);
        $this->assertSame('unavailable', $driver->status);

        $vehicle = Vehicle::factory()->create([
            'user_id' => $user->id,
            'make' => 'Toyota',
            'model' => 'Corolla',
            'approval_status' => 'pending',
            'payment_status' => 'completed',
        ]);

        $this->actingAs($user)
            ->get(route('drivers.profile'))
            ->assertOk()
            ->assertSee($driver->application_id);

        $this->actingAs($user)
            ->get(route('vehicles.pending-vehicles'))
            ->assertOk()
            ->assertSee($vehicle->application_id);

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->get(route('admin.pending-drivers'))
            ->assertOk()
            ->assertSee($driver->application_id);

        $this->actingAs($admin)
            ->get(route('admin.pending-vehicles'))
            ->assertOk()
            ->assertSee($vehicle->application_id);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Waiting for approval')
            ->assertSee('Your payment is confirmed.')
            ->assertSee('Toyota Corolla')
            ->assertSee('Your vehicle is waiting for admin approval.');

        $this->actingAs($admin)
            ->post(route('admin.drivers.approve', $driver))
            ->assertRedirect();
        $this->actingAs($admin)
            ->post(route('admin.vehicles.approve', $vehicle))
            ->assertRedirect();

        $this->assertDatabaseHas('drivers', [
            'id' => $driver->id,
            'approval_status' => 'approved',
            'status' => 'available',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Approved')
            ->assertSee('Your driver profile has been approved.')
            ->assertSee('Your vehicle has been approved.');
    }

    public function test_completed_driver_payment_webhook_retry_creates_pending_profile_and_blocks_resubmission(): void
    {
        $user = User::factory()->create();
        $payment = Payment::create([
            'user_id' => $user->id,
            'reference' => 'DRV-COMPLETED-WEBHOOK-RETRY',
            'amount' => 20,
            'type' => 'driver',
            'status' => 'completed',
            'data' => [
                'license_number' => 'DL-WEBHOOK-RETRY',
                'license_expiry' => now()->addYear()->toDateString(),
                'license_class' => 'B',
                'experience_years' => 5,
                'emergency_person' => 'Emergency Contact',
                'emergency_phone' => '0200000000',
                'bio' => 'Experienced driver.',
                'profile_picture' => 'documents/profile.jpg',
                'license_file' => 'documents/license.pdf',
                'ghana_card_no' => 'GHA-123456789-0',
                'ghana_card_file' => 'documents/ghana-card.pdf',
                'guarantor' => 'First Guarantor',
                'guarantor_phone' => '0200000001',
                'g_ghana_card_no' => 'GHA-123456780-1',
                'guarantor_ghana_card_file' => 'documents/guarantor-id.pdf',
                'guarantor_passport_file' => 'documents/guarantor.jpg',
                'relationship' => 'family',
                'second_guarantor' => 'Second Guarantor',
                'second_guarantor_phone' => '0200000002',
                'second_g_ghana_card_no' => 'GHA-123456781-2',
                'second_guarantor_ghana_card_file' => 'documents/second-guarantor-id.pdf',
                'second_guarantor_passport_file' => 'documents/second-guarantor.jpg',
                'second_relationship' => 'friend',
            ],
        ]);

        $this->post(route('payment.webhook'), ['reference' => $payment->reference])
            ->assertOk()
            ->assertJson(['message' => 'Already processed']);

        $driver = Driver::where('user_id', $user->id)->firstOrFail();
        $this->assertSame('pending', $driver->approval_status);
        $this->assertSame('paid', $driver->payment_status);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Waiting for approval')
            ->assertSee('Your payment is confirmed.');

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->get(route('admin.pending-drivers'))
            ->assertOk()
            ->assertSee($driver->license_number);

        $this->actingAs($user)
            ->get(route('drivers.create'))
            ->assertRedirect(route('drivers.profile'));

        $this->actingAs($user)
            ->post(route('drivers.store'))
            ->assertRedirect(route('drivers.profile'));

        $this->assertDatabaseCount('drivers', 1);
        $this->assertDatabaseCount('payments', 1);
    }
}
