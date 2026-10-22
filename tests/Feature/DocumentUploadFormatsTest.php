<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentUploadFormatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_driver_registration_accepts_pdf_identity_and_license_documents(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->post(route('drivers.store'), [
                'dob' => '1990-01-01',
                'emergency_person' => 'Emergency Contact',
                'emergency_phone' => '0240000000',
                'bio' => 'Experienced, careful driver.',
                'profile_picture' => UploadedFile::fake()->createWithContent(
                    'profile.png',
                    base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=')
                ),
                'license_number' => 'DL-12345678',
                'license_expiry' => now()->addYears(2)->toDateString(),
                'license_class' => 'B',
                'license_file' => UploadedFile::fake()->create('license.pdf', 100, 'application/pdf'),
                'police_check_file' => UploadedFile::fake()->create('police-check.pdf', 100, 'application/pdf'),
                'police_check_consent' => '1',
                'experience_years' => '5',
                'ghana_card_no' => 'GHA-001122330-1',
                'ghana_card_file' => UploadedFile::fake()->create('ghana-card.pdf', 100, 'application/pdf'),
                'passport_file' => UploadedFile::fake()->create('passport.pdf', 100, 'application/pdf'),
                'guarantor' => 'Guarantor Name',
                'guarantor_phone' => '0241111111',
                'g_ghana_card_no' => 'GHA-001122330-1',
                'guarantor_ghana_card_file' => UploadedFile::fake()->create('guarantor-card.pdf', 100, 'application/pdf'),
                'guarantor_passport_file' => UploadedFile::fake()->createWithContent(
                    'guarantor-passport.png',
                    base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=')
                ),
                'relationship' => 'family',
                'second_guarantor' => 'Second Guarantor',
                'second_guarantor_phone' => '0242222222',
                'second_g_ghana_card_no' => 'GHA-112233445-6',
                'second_guarantor_ghana_card_file' => UploadedFile::fake()->create('second-guarantor-card.pdf', 100, 'application/pdf'),
                'second_guarantor_passport_file' => UploadedFile::fake()->createWithContent(
                    'second-guarantor-passport.png',
                    base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=')
                ),
                'second_relationship' => 'friend',
            ])
            ->assertOk()
            ->assertViewIs('payments.paystack-form')
            ->assertSee('Continue to Paystack')
            ->assertDontSee('payWithPaystack();', false);

        $payment = Payment::where('user_id', $user->id)->where('type', 'driver')->firstOrFail();
        foreach (['profile_picture', 'license_file', 'ghana_card_file', 'passport_file', 'guarantor_ghana_card_file', 'guarantor_passport_file', 'second_guarantor_ghana_card_file', 'second_guarantor_passport_file'] as $field) {
            Storage::disk('public')->assertExists($payment->data[$field]);
        }
        Storage::disk('local')->assertExists($payment->data['police_check_file']);
        Storage::disk('public')->assertMissing($payment->data['police_check_file']);
        $this->assertSame('Experienced, careful driver.', $payment->data['bio']);
        $this->assertNotEmpty($payment->data['police_check_consent_at']);
    }

    public function test_vehicle_registration_accepts_pdf_vehicle_documents(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->post(route('vehicles.store'), [
                'make' => 'Toyota',
                'model' => 'Corolla',
                'year' => now()->year,
                'vehicle_type' => 'car',
                'license_plate' => 'GR 1234-25',
                'vin' => '1HGCM82633A004352',
                'condition' => 'good',
                'registration_date' => '2020-01-01',
                'registration_expiry' => '2027-01-01',
                'insurance_expiry' => '2027-02-01',
                'inspection_expiry' => '2027-03-01',
                'registration_document' => UploadedFile::fake()->create('registration.pdf', 100, 'application/pdf'),
                'insurance_document' => UploadedFile::fake()->create('insurance.pdf', 100, 'application/pdf'),
                'roadworthiness_certificate' => UploadedFile::fake()->create('roadworthy.pdf', 100, 'application/pdf'),
                'image_url' => [UploadedFile::fake()->createWithContent(
                    'vehicle.png',
                    base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=')
                )],
            ])
            ->assertOk()
            ->assertViewIs('payments.paystack-form')
            ->assertSee('Continue to Paystack')
            ->assertDontSee('payWithPaystack();', false);

        $payment = Payment::where('user_id', $user->id)->where('type', 'vehicle')->firstOrFail();
        foreach (['registration_document', 'insurance_document', 'roadworthiness_certificate'] as $field) {
            Storage::disk('public')->assertExists($payment->data[$field]);
        }
    }

    public function test_vehicle_registration_accepts_both_requested_plate_formats(): void
    {
        Storage::fake('public');

        foreach (['2301-GR1', 'AS 2567-12'] as $plate) {
            $user = User::factory()->create(['role' => 'user']);

            $this->actingAs($user)
                ->post(route('vehicles.store'), [
                    'make' => 'Toyota',
                    'model' => 'Corolla',
                    'year' => now()->year,
                    'vehicle_type' => 'car',
                    'license_plate' => $plate,
                    'condition' => 'good',
                    'registration_date' => '2020-01-01',
                    'registration_expiry' => '2027-01-01',
                    'insurance_expiry' => '2027-02-01',
                    'inspection_expiry' => '2027-03-01',
                    'registration_document' => UploadedFile::fake()->create('registration.pdf', 100, 'application/pdf'),
                    'insurance_document' => UploadedFile::fake()->create('insurance.pdf', 100, 'application/pdf'),
                    'roadworthiness_certificate' => UploadedFile::fake()->create('roadworthy.pdf', 100, 'application/pdf'),
                    'image_url' => [UploadedFile::fake()->createWithContent(
                        'vehicle.png',
                        base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=')
                    )],
                ])
                ->assertOk()
                ->assertViewIs('payments.paystack-form');

            $this->assertDatabaseHas('payments', [
                'user_id' => $user->id,
                'type' => 'vehicle',
            ]);
        }
    }

    public function test_driver_and_vehicle_upload_fields_offer_image_and_pdf_formats(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get(route('drivers.create'))
            ->assertOk()
            ->assertSee('placeholder="Enter your driver\'s license number"', false)
            ->assertDontSee('pattern="ADB-', false)
            ->assertSee('pattern="GHA-[0-9]{9}-[0-9]"', false)
            ->assertSee('id="profile_picture" name="profile_picture"', false)
            ->assertSee('accept="image/jpeg,image/png,image/gif,image/webp,.jpg,.jpeg,.png,.gif,.webp" required', false)
            ->assertSee('id="bio" name="bio"', false)
            ->assertSee('second_guarantor_ghana_card_file', false)
            ->assertSee('name="license_file" accept=".jpg,.jpeg,.png,.gif,.webp,.pdf"', false)
            ->assertSee('name="police_check_file" accept=".jpg,.jpeg,.png,.gif,.webp,.pdf" required', false)
            ->assertSee('name="police_check_consent" value="1"', false)
            ->assertSee('name="ghana_card_file" accept=".jpg,.jpeg,.png,.gif,.webp,.pdf"', false)
            ->assertSee('name="passport_file" accept=".jpg,.jpeg,.png,.gif,.webp,.pdf"', false);

        $this->actingAs($user)
            ->get(route('vehicles.create'))
            ->assertOk()
            ->assertSee('2301-GR1 or AS 2567-12')
            ->assertSee('pattern="[A-HJ-NPR-Z0-9]{17}"', false)
            ->assertSee('name="image_url[]"', false)
            ->assertSee('name="registration_date"', false)
            ->assertSee('name="roadworthiness_certificate" accept=".jpg,.jpeg,.png,.gif,.webp,.pdf"', false)
            ->assertSee('name="registration_document" accept=".jpg,.jpeg,.png,.gif,.webp,.pdf"', false);
    }

    public function test_driver_application_rejects_missing_license_and_malformed_card_numbers(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->post(route('drivers.store'), [
                'license_number' => '',
                'ghana_card_no' => 'GHA-12345678-1',
                'g_ghana_card_no' => 'GHA-12345678-1',
            ])
            ->assertSessionHasErrors([
                'license_number', 'ghana_card_no', 'g_ghana_card_no',
                'profile_picture', 'bio', 'second_guarantor',
                'second_guarantor_phone', 'second_g_ghana_card_no',
                'second_guarantor_ghana_card_file', 'second_relationship',
            ]);
    }

    public function test_driver_application_requires_police_check_document_and_consent(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->post(route('drivers.store'), [])
            ->assertSessionHasErrors(['police_check_file', 'police_check_consent']);
    }

    public function test_vehicle_registration_requires_a_valid_vin_and_required_documents_dates_and_image(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->post(route('vehicles.store'), [
                'make' => 'Toyota',
                'model' => 'Corolla',
                'year' => now()->year,
                'vehicle_type' => 'car',
                'license_plate' => '2301-GR1',
                'vin' => '1HGCM82633A00435I',
                'condition' => 'good',
            ])
            ->assertSessionHasErrors([
                'vin',
                'registration_date',
                'registration_expiry',
                'insurance_expiry',
                'inspection_expiry',
                'registration_document',
                'insurance_document',
                'roadworthiness_certificate',
                'image_url',
            ]);
    }
}
