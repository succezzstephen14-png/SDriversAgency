<?php

namespace Tests\Feature;

use App\Models\Driver;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ApplicationChangeReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_approved_driver_changes_are_staged_until_admin_approval(): void
    {
        $owner = User::factory()->create();
        $driver = Driver::factory()->for($owner)->create([
            'approval_status' => 'approved',
            'license_number' => 'ADB-00000000-11111',
            'license_expiry' => now()->addYears(2)->toDateString(),
        ]);

        $this->actingAs($owner)
            ->put(route('drivers.update'), [
                'license_number' => 'ADB-00000000-11112',
                'license_expiry' => now()->addYears(3)->toDateString(),
                'license_class' => 'B',
                'experience_years' => '8',
                'status' => 'available',
                'bio' => 'Updated driver profile',
            ])
            ->assertRedirect(route('drivers.profile'));

        $this->assertSame('ADB-00000000-11111', $driver->fresh()->license_number);
        $this->assertSame('ADB-00000000-11112', $driver->fresh()->pending_application_changes['license_number']);

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->post(route('admin.drivers.application-changes.approve', $driver))
            ->assertRedirect(route('admin.drivers.detail', $driver));

        $driver->refresh();
        $this->assertSame('ADB-00000000-11112', $driver->license_number);
        $this->assertNull($driver->pending_application_changes);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $owner->id,
            'title' => 'Driver application changes approved',
        ]);
    }

    public function test_rejected_driver_changes_leave_approved_details_unchanged(): void
    {
        $owner = User::factory()->create();
        $driver = Driver::factory()->for($owner)->create([
            'approval_status' => 'approved',
            'license_number' => 'ADB-00000000-11111',
            'pending_application_changes' => ['license_number' => 'NEW12345'],
        ]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.drivers.application-changes.reject', $driver))
            ->assertRedirect(route('admin.drivers.detail', $driver));

        $this->assertSame('ADB-00000000-11111', $driver->fresh()->license_number);
        $this->assertNull($driver->fresh()->pending_application_changes);
    }

    public function test_approved_vehicle_changes_are_staged_until_admin_approval(): void
    {
        $owner = User::factory()->create();
        $vehicle = Vehicle::factory()->for($owner)->create([
            'approval_status' => 'approved',
            'make' => 'Ford',
            'image_url' => ['existing-vehicle.jpg'],
            'registration_date' => '2020-01-01',
            'registration_expiry' => '2027-01-01',
            'insurance_expiry' => '2027-02-01',
            'inspection_expiry' => '2027-03-01',
        ]);

        $this->actingAs($owner)
            ->put(route('vehicles.update', $vehicle), [
                'make' => 'Toyota',
                'model' => 'Corolla',
                'year' => now()->year,
                'vehicle_type' => 'car',
                'license_plate' => 'GR 1234-25',
                'status' => 'available',
                'registration_date' => '2021-01-01',
                'registration_expiry' => '2027-01-01',
                'insurance_expiry' => '2027-02-01',
                'inspection_expiry' => '2027-03-01',
            ])
            ->assertRedirect(route('vehicles.show', $vehicle));

        $this->assertSame('Ford', $vehicle->fresh()->make);
        $this->assertSame('Toyota', $vehicle->fresh()->pending_application_changes['make']);

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->post(route('admin.vehicles.application-changes.approve', $vehicle))
            ->assertRedirect(route('admin.vehicles.detail', $vehicle));

        $vehicle->refresh();
        $this->assertSame('Toyota', $vehicle->make);
        $this->assertSame('2021-01-01', $vehicle->registration_date->format('Y-m-d'));
        $this->assertNull($vehicle->pending_application_changes);
    }

    public function test_rejected_vehicle_changes_leave_approved_details_unchanged(): void
    {
        $owner = User::factory()->create();
        $vehicle = Vehicle::factory()->for($owner)->create([
            'approval_status' => 'approved',
            'make' => 'Ford',
            'pending_application_changes' => ['make' => 'Toyota'],
        ]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.vehicles.application-changes.reject', $vehicle))
            ->assertRedirect(route('admin.vehicles.detail', $vehicle));

        $this->assertSame('Ford', $vehicle->fresh()->make);
        $this->assertNull($vehicle->fresh()->pending_application_changes);
    }

    public function test_user_profile_changes_wait_for_admin_approval_and_lock_resubmission(): void
    {
        $user = User::factory()->create([
            'name' => 'Current Name',
            'phone' => '0240000000',
            'address' => 'Current address',
        ]);

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'name' => 'Requested Name',
                'phone' => '0241111111',
                'address' => 'Requested address',
            ])
            ->assertRedirect(route('profile'));

        $this->assertSame('Current Name', $user->fresh()->name);
        $this->assertSame('Requested Name', $user->fresh()->pending_profile_changes['name']);

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'name' => 'Another Name',
                'phone' => '0242222222',
                'address' => 'Another address',
            ])
            ->assertSessionHasErrors('profile');

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->post(route('admin.users.profile-changes.approve', $user))
            ->assertRedirect(route('admin.users.edit', $user));

        $user->refresh();
        $this->assertSame('Requested Name', $user->name);
        $this->assertSame('0241111111', $user->phone);
        $this->assertNull($user->pending_profile_changes);
    }

    public function test_admin_can_generate_a_reset_link_that_resets_a_users_password(): void
    {
        $user = User::factory()->create(['email' => 'reset-target@example.com']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.users.password-reset-link', $user))
            ->assertRedirect(route('admin.users.edit', $user))
            ->assertSessionHas('user_password_reset_link');

        $resetLink = session('user_password_reset_link');
        $token = basename(parse_url($resetLink, PHP_URL_PATH));

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'ChangedPassword1!',
            'password_confirmation' => 'ChangedPassword1!',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('ChangedPassword1!', $user->fresh()->password));
    }

    public function test_pending_driver_photo_is_private_until_approved(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $owner = User::factory()->create();
        $driver = Driver::factory()->for($owner)->create([
            'approval_status' => 'approved',
            'license_number' => 'ADB-00000000-11111',
            'license_expiry' => now()->addYears(2)->toDateString(),
        ]);

        $this->actingAs($owner)
            ->put(route('drivers.update'), [
                'license_number' => 'ADB-00000000-11111',
                'license_expiry' => now()->addYears(2)->toDateString(),
                'license_class' => $driver->license_class,
                'experience_years' => (string) $driver->experience_years,
                'status' => $driver->status,
                'bio' => $driver->bio,
                'profile_picture' => UploadedFile::fake()->createWithContent(
                    'new-photo.gif',
                    base64_decode('R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=')
                ),
            ])
            ->assertRedirect(route('drivers.profile'));

        $privatePath = substr($driver->fresh()->pending_application_changes['profile_picture'], strlen('private:'));
        Storage::disk('local')->assertExists($privatePath);
        Storage::disk('public')->assertMissing($privatePath);

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->get(route('admin.application-changes.documents.show', [
                'type' => 'driver',
                'id' => $driver->id,
                'field' => 'profile_picture',
            ]))
            ->assertOk();

        $this->post(route('admin.drivers.application-changes.approve', $driver))
            ->assertRedirect(route('admin.drivers.detail', $driver));

        Storage::disk('public')->assertExists($privatePath);
        Storage::disk('local')->assertMissing($privatePath);
        $this->assertSame($privatePath, $driver->fresh()->profile_picture);
    }
}
