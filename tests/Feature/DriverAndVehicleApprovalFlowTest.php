<?php

namespace Tests\Feature;

use App\Models\Driver;
use App\Models\Payment;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
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

    public function test_submitted_driver_and_vehicle_are_created_and_listed_for_admin_after_verified_payment(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        Mail::fake();
        config(['services.paystack.secret_key' => 'sk_test_example']);
        Http::fake(function (Request $request) {
            $reference = basename(parse_url($request->url(), PHP_URL_PATH));

            return Http::response([
                'status' => true,
                'data' => [
                    'status' => 'success',
                    'amount' => 20,
                    'reference' => $reference,
                ],
            ]);
        });

        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('drivers.store'), [
                'emergency_person' => 'Driver Emergency Contact',
                'emergency_phone' => '0200000100',
                'bio' => 'Test driver application.',
                'profile_picture' => UploadedFile::fake()->createWithContent(
                    'driver.png',
                    base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=')
                ),
                'license_number' => 'TEST-DL-123456',
                'license_expiry' => now()->addYear()->toDateString(),
                'license_class' => 'B',
                'experience_years' => 5,
                'license_file' => UploadedFile::fake()->create('license.pdf', 10, 'application/pdf'),
                'police_check_file' => UploadedFile::fake()->create('police-check.pdf', 10, 'application/pdf'),
                'police_check_consent' => '1',
                'ghana_card_no' => 'GHA-123456789-0',
                'ghana_card_file' => UploadedFile::fake()->create('ghana-card.pdf', 10, 'application/pdf'),
                'guarantor' => 'First Test Guarantor',
                'guarantor_phone' => '0200000101',
                'g_ghana_card_no' => 'GHA-123456780-1',
                'guarantor_ghana_card_file' => UploadedFile::fake()->create('guarantor-id.pdf', 10, 'application/pdf'),
                'guarantor_passport_file' => UploadedFile::fake()->createWithContent(
                    'guarantor.png',
                    base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=')
                ),
                'relationship' => 'family',
                'second_guarantor' => 'Second Test Guarantor',
                'second_guarantor_phone' => '0200000102',
                'second_g_ghana_card_no' => 'GHA-123456781-2',
                'second_guarantor_ghana_card_file' => UploadedFile::fake()->create('second-guarantor-id.pdf', 10, 'application/pdf'),
                'second_guarantor_passport_file' => UploadedFile::fake()->createWithContent(
                    'second-guarantor.png',
                    base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=')
                ),
                'second_relationship' => 'friend',
            ])
            ->assertOk()
            ->assertViewIs('payments.paystack-form');

        $driverPayment = Payment::where('user_id', $user->id)->where('type', 'driver')->firstOrFail();
        $this->actingAs($user)
            ->get(route('payment.success', ['reference' => $driverPayment->reference]))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('success', 'Payment confirmed. Your driver profile is waiting for admin approval.');

        $driver = Driver::where('user_id', $user->id)->firstOrFail();
        $this->assertSame('completed', $driverPayment->fresh()->status);
        $this->assertSame('pending', $driver->approval_status);
        $this->assertSame('paid', $driver->payment_status);
        $this->assertNotNull($driver->police_check_consent_at);
        Storage::disk('local')->assertExists($driver->police_check_file);

        $this->actingAs($user)
            ->post(route('vehicles.store'), [
                'make' => 'Test Motors',
                'model' => 'Approval Sedan',
                'year' => now()->year,
                'vehicle_type' => 'car',
                'color' => 'Blue',
                'license_plate' => '2301-GR1',
                'condition' => 'good',
                'registration_date' => '2020-01-01',
                'registration_expiry' => now()->addYear()->toDateString(),
                'insurance_expiry' => now()->addYear()->toDateString(),
                'inspection_expiry' => now()->addYear()->toDateString(),
                'registration_document' => UploadedFile::fake()->create('registration.pdf', 10, 'application/pdf'),
                'insurance_document' => UploadedFile::fake()->create('insurance.pdf', 10, 'application/pdf'),
                'roadworthiness_certificate' => UploadedFile::fake()->create('roadworthiness.pdf', 10, 'application/pdf'),
                'image_url' => [UploadedFile::fake()->createWithContent(
                    'vehicle.png',
                    base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=')
                )],
            ])
            ->assertOk()
            ->assertViewIs('payments.paystack-form');

        $vehiclePayment = Payment::where('user_id', $user->id)->where('type', 'vehicle')->firstOrFail();
        $this->actingAs($user)
            ->get(route('payment.success', ['reference' => $vehiclePayment->reference]))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('success', 'Payment confirmed. Your vehicle is waiting for admin approval.');

        $vehicle = Vehicle::where('user_id', $user->id)->firstOrFail();
        $this->assertSame('completed', $vehiclePayment->fresh()->status);
        $this->assertSame('pending', $vehicle->approval_status);
        $this->assertSame('completed', $vehicle->payment_status);

        $admin = User::factory()->create(['role' => 'admin']);
        $adminDashboard = $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk();
        $this->assertSame(1, $adminDashboard->viewData('stats')['pending_drivers']);
        $this->assertSame(1, $adminDashboard->viewData('stats')['pending_vehicles']);

        $this->actingAs($admin)
            ->get(route('admin.pending-drivers'))
            ->assertOk()
            ->assertSee('Pending Driver Approvals')
            ->assertSee($driver->license_number)
            ->assertSee('Police Background Check')
            ->assertSee('Pending Review');
        $this->actingAs($admin)
            ->get(route('admin.drivers.detail', $driver))
            ->assertOk()
            ->assertSee('Police Criminal Background Check')
            ->assertSee('Police check consent');

        $this->actingAs($admin)
            ->get(route('admin.pending-vehicles'))
            ->assertOk()
            ->assertSee('Pending Vehicle Approvals')
            ->assertSee($vehicle->license_plate)
            ->assertSee('Approval Sedan')
            ->assertSee('Pending Review');

        Http::assertSentCount(2);
    }

    public function test_old_approved_vehicles_remain_visible_on_user_and_admin_dashboards(): void
    {
        $user = User::factory()->create();
        $approvedVehicle = Vehicle::factory()->create([
            'user_id' => $user->id,
            'make' => 'VisibleApprovedMake',
            'model' => 'VisibleApprovedModel',
            'approval_status' => 'approved',
            'created_at' => now()->subDays(10),
        ]);

        foreach (range(1, 6) as $index) {
            Vehicle::factory()->create([
                'user_id' => $user->id,
                'approval_status' => 'pending',
                'created_at' => now()->addSeconds($index),
            ]);
        }

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('VisibleApprovedMake VisibleApprovedModel')
            ->assertSee(route('vehicles.my-vehicles'));

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('VisibleApprovedMake VisibleApprovedModel');
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

    public function test_user_and_admin_dashboard_recover_completed_driver_payment_without_a_webhook(): void
    {
        Mail::fake();
        $user = User::factory()->create();
        $payment = Payment::create([
            'user_id' => $user->id,
            'reference' => 'DRV-DASHBOARD-RECOVERY',
            'amount' => 20,
            'type' => 'driver',
            'status' => 'completed',
            'data' => $this->completedDriverPaymentData(),
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Waiting for approval')
            ->assertSee('Your payment is confirmed.');

        $driver = Driver::where('user_id', $user->id)->firstOrFail();
        $this->assertSame('pending', $driver->approval_status);
        $this->assertSame('paid', $driver->payment_status);
        $this->assertSame($payment->reference, $driver->payment_reference);

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

    public function test_completed_driver_payment_blocks_new_submission_even_if_profile_recovery_fails(): void
    {
        $user = User::factory()->create();
        Payment::create([
            'user_id' => $user->id,
            'reference' => 'DRV-INCOMPLETE-COMPLETED',
            'amount' => 20,
            'type' => 'driver',
            'status' => 'completed',
            'data' => ['license_number' => 'DL-INCOMPLETE'],
        ]);

        $this->actingAs($user)
            ->get(route('drivers.create'))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error');

        $this->actingAs($user)
            ->post(route('drivers.store'))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('drivers', 0);
        $this->assertDatabaseCount('payments', 1);
    }

    private function completedDriverPaymentData(): array
    {
        return [
            'license_number' => 'DL-DASHBOARD-RECOVERY',
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
        ];
    }
}
