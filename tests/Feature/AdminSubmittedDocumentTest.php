<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Driver;
use App\Models\Recruitment;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminSubmittedDocumentTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_a_submitted_driver_document_without_a_public_storage_link(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        Storage::disk('public')->put('documents/ghana-card.jpg', 'ghana card image');
        Storage::disk('local')->put('documents/police-check.pdf', 'police check document');
        Storage::disk('public')->put('documents/guarantor-passport.jpg', 'guarantor passport image');
        Storage::disk('public')->put('documents/second-guarantor-passport.jpg', 'second guarantor passport image');

        $admin = User::factory()->create(['role' => 'admin']);
        $driver = Driver::factory()->create([
            'ghana_card_file' => 'documents/ghana-card.jpg',
            'police_check_file' => 'documents/police-check.pdf',
            'police_check_consent_at' => now(),
            'guarantor_passport_file' => 'documents/guarantor-passport.jpg',
            'second_guarantor_passport_file' => 'documents/second-guarantor-passport.jpg',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.documents.show', [
            'type' => 'driver',
            'id' => $driver->id,
            'field' => 'ghana_card_file',
        ]));

        $response->assertOk();
        $response->assertHeader('Content-Disposition', 'inline; filename=ghana-card.jpg');

        $this->actingAs($admin)
            ->get(route('admin.documents.show', [
                'type' => 'driver',
                'id' => $driver->id,
                'field' => 'guarantor_passport_file',
            ]))
            ->assertOk()
            ->assertHeader('Content-Disposition', 'inline; filename=guarantor-passport.jpg');

        $this->actingAs($admin)
            ->get(route('admin.documents.show', [
                'type' => 'driver',
                'id' => $driver->id,
                'field' => 'second_guarantor_passport_file',
            ]))
            ->assertOk()
            ->assertHeader('Content-Disposition', 'inline; filename=second-guarantor-passport.jpg');

        $this->actingAs($admin)
            ->get(route('admin.documents.show', [
                'type' => 'driver',
                'id' => $driver->id,
                'field' => 'police_check_file',
            ]))
            ->assertOk()
            ->assertHeader('Content-Disposition', 'inline; filename=police-check.pdf');

        $this->actingAs($admin)
            ->get(route('admin.drivers.detail', $driver))
            ->assertOk()
            ->assertSee(route('admin.documents.show', [
                'type' => 'driver',
                'id' => $driver->id,
                'field' => 'ghana_card_file',
            ]))
            ->assertSee(route('admin.documents.show', [
                'type' => 'driver',
                'id' => $driver->id,
                'field' => 'police_check_file',
            ]))
            ->assertSee(route('admin.documents.show', [
                'type' => 'driver',
                'id' => $driver->id,
                'field' => 'guarantor_passport_file',
            ]))
            ->assertSee(route('admin.documents.show', [
                'type' => 'driver',
                'id' => $driver->id,
                'field' => 'second_guarantor_passport_file',
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
        $recruitment = Recruitment::create([
            'user_id' => User::factory()->create()->id,
            'title' => 'Driver position',
            'description' => 'Position description',
            'location' => 'Accra',
            'approval_status' => 'approved',
        ]);
        $booking = Booking::create([
            'user_id' => $applicant->id,
            'recruitment_id' => $recruitment->id,
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

        $this->actingAs($admin)
            ->get(route('admin.recruitment-review'))
            ->assertOk()
            ->assertSee(route('admin.documents.show', [
                'type' => 'booking',
                'id' => $booking->id,
                'field' => 'application_documents',
                'index' => 0,
            ]), false);
    }

    public function test_admin_review_documents_stream_driver_vehicle_and_recruitment_uploads(): void
    {
        Storage::fake('public');
        foreach ([
            'documents/guarantor-passport.jpg',
            'documents/second-guarantor-passport.jpg',
            'documents/registration.pdf',
            'documents/insurance.pdf',
            'documents/roadworthiness.pdf',
            'documents/job-brief.pdf',
            'documents/offer.pdf',
        ] as $path) {
            Storage::disk('public')->put($path, 'submitted document');
        }

        $admin = User::factory()->create(['role' => 'admin']);
        $driver = Driver::factory()->create([
            'guarantor_passport_file' => 'documents/guarantor-passport.jpg',
            'second_guarantor_passport_file' => 'documents/second-guarantor-passport.jpg',
        ]);
        $vehicle = Vehicle::factory()->create([
            'approval_status' => 'pending',
            'registration_document' => 'documents/registration.pdf',
            'insurance_document' => 'documents/insurance.pdf',
            'roadworthiness_certificate' => 'documents/roadworthiness.pdf',
        ]);
        $recruitment = Recruitment::create([
            'user_id' => User::factory()->create()->id,
            'title' => 'Driver position',
            'description' => 'Position description',
            'location' => 'Accra',
            'job_brief' => 'documents/job-brief.pdf',
            'offer_document' => 'documents/offer.pdf',
            'approval_status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.recruitment-review'))
            ->assertOk()
            ->assertSee(route('admin.documents.show', [
                'type' => 'driver',
                'id' => $driver->id,
                'field' => 'guarantor_passport_file',
            ]), false)
            ->assertSee(route('admin.documents.show', [
                'type' => 'driver',
                'id' => $driver->id,
                'field' => 'second_guarantor_passport_file',
            ]), false)
            ->assertSee(route('admin.documents.show', [
                'type' => 'vehicle',
                'id' => $vehicle->id,
                'field' => 'road_worthy',
            ]), false)
            ->assertSee(route('admin.documents.show', [
                'type' => 'recruitment',
                'id' => $recruitment->id,
                'field' => 'job_brief',
            ]), false)
            ->assertSee(route('admin.documents.show', [
                'type' => 'recruitment',
                'id' => $recruitment->id,
                'field' => 'offer_document',
            ]), false);

        foreach ([
            [$driver, 'driver', 'guarantor_passport_file'],
            [$driver, 'driver', 'second_guarantor_passport_file'],
            [$vehicle, 'vehicle', 'registration_document'],
            [$vehicle, 'vehicle', 'insurance_document'],
            [$vehicle, 'vehicle', 'roadworthiness_certificate'],
            [$recruitment, 'recruitment', 'job_brief'],
            [$recruitment, 'recruitment', 'offer_document'],
        ] as [$record, $type, $field]) {
            $this->get(route('admin.documents.show', [
                'type' => $type,
                'id' => $record->id,
                'field' => $field,
            ]))->assertOk();
        }
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
