<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminSubmittedDocumentTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_a_submitted_driver_document_without_a_public_storage_link(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('documents/ghana-card.jpg', 'ghana card image');

        $admin = User::factory()->create(['role' => 'admin']);
        $driver = Driver::factory()->create([
            'ghana_card_file' => 'documents/ghana-card.jpg',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.documents.show', [
            'type' => 'driver',
            'id' => $driver->id,
            'field' => 'ghana_card_file',
        ]));

        $response->assertOk();
        $response->assertHeader('Content-Disposition', 'inline; filename=ghana-card.jpg');

        $this->actingAs($admin)
            ->get(route('admin.drivers.detail', $driver))
            ->assertOk()
            ->assertSee(route('admin.documents.show', [
                'type' => 'driver',
                'id' => $driver->id,
                'field' => 'ghana_card_file',
            ]));

        $this->actingAs($admin)
            ->get(route('admin.recruitment-review'))
            ->assertOk()
            ->assertSee('/admin/documents/DOCUMENT_TYPE/DOCUMENT_ID/DOCUMENT_FIELD', false);
    }

    public function test_admin_can_view_an_applicant_document_attached_to_a_booking(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('applications/cv.pdf', 'application document');

        $admin = User::factory()->create(['role' => 'admin']);
        $applicant = User::factory()->create();
        $booking = Booking::create([
            'user_id' => $applicant->id,
            'booking_type' => 'recruitment',
            'booking_date' => now(),
            'application_documents' => ['applications/cv.pdf'],
            'status' => 'pending',
            'approval_status' => 'pending',
            'payment_status' => 'paid',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.documents.show', [
            'type' => 'booking',
            'id' => $booking->id,
            'field' => 'application_documents',
            'index' => 0,
        ]));

        $response->assertOk();
        $response->assertHeader('Content-Disposition', 'inline; filename=cv.pdf');
    }

    public function test_non_admin_cannot_view_submitted_documents(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('documents/license.jpg', 'license image');

        $user = User::factory()->create(['role' => 'user']);
        $driver = Driver::factory()->create([
            'license_file' => 'documents/license.jpg',
        ]);

        $this->actingAs($user)
            ->get(route('admin.documents.show', [
                'type' => 'driver',
                'id' => $driver->id,
                'field' => 'license_file',
            ]))
            ->assertForbidden();
    }

    public function test_driver_profile_picture_is_visible_in_the_driver_and_admin_portals(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('documents/driver-photo.jpg', 'profile photo');

        $driverUser = User::factory()->create(['role' => 'user']);
        $driver = Driver::factory()->create([
            'user_id' => $driverUser->id,
            'profile_picture' => 'documents/driver-photo.jpg',
        ]);
        $photoUrl = Storage::disk('public')->url('documents/driver-photo.jpg');

        $this->assertSame($photoUrl, $driver->getProfilePictureUrl());

        $this->actingAs($driverUser)
            ->get(route('drivers.profile'))
            ->assertOk()
            ->assertSee($photoUrl, false)
            ->assertSee('profile picture');

        $admin = User::factory()->create(['role' => 'admin']);
        $photoRoute = route('admin.documents.show', [
            'type' => 'driver',
            'id' => $driver->id,
            'field' => 'profile_picture',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.drivers'))
            ->assertOk()
            ->assertSee($photoRoute, false);

        $this->actingAs($admin)
            ->get(route('admin.drivers.detail', $driver))
            ->assertOk()
            ->assertSee($photoRoute, false)
            ->assertSee('profile picture');
    }

    public function test_profile_picture_url_falls_back_to_legacy_profile_image(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('documents/legacy-driver-photo.jpg', 'profile photo');

        $driver = Driver::factory()->create([
            'profile_image' => 'documents/legacy-driver-photo.jpg',
        ]);

        $this->assertSame(
            Storage::disk('public')->url('documents/legacy-driver-photo.jpg'),
            $driver->getProfilePictureUrl()
        );
    }
}
