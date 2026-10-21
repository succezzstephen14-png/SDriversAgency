<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentReferenceRetryTest extends TestCase
{
    use RefreshDatabase;

    public function test_retrying_pending_driver_payment_uses_a_new_reference_and_preserves_old_callbacks(): void
    {
        $user = User::factory()->create();
        $oldReference = 'DRV-OLD-REFERENCE';
        $payment = Payment::create([
            'user_id' => $user->id,
            'reference' => $oldReference,
            'amount' => 20,
            'type' => 'driver',
            'data' => $this->validDriverPaymentData(),
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->post(route('driver.payment.initiate'))
            ->assertOk()
            ->assertViewIs('payments.paystack-form')
            ->assertSee('Continue to Paystack')
            ->assertDontSee('payWithPaystack();', false);

        $payment->refresh();
        $this->assertNotSame($oldReference, $payment->reference);
        $this->assertContains($oldReference, $payment->data['previous_references']);

        $payment->update(['status' => 'completed']);
        $this->post(route('payment.webhook'), ['reference' => $oldReference])
            ->assertOk()
            ->assertJson(['message' => 'Already processed']);
        $this->assertDatabaseHas('drivers', [
            'user_id' => $user->id,
            'payment_reference' => $payment->fresh()->reference,
        ]);
    }

    public function test_retrying_pending_vehicle_payment_uses_a_new_reference(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $oldReference = 'VEH-OLD-REFERENCE';
        $payment = Payment::create([
            'user_id' => $user->id,
            'reference' => $oldReference,
            'amount' => 20,
            'type' => 'vehicle',
            'data' => ['license_plate' => '2301-GR1'],
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->post(route('vehicle.payment.initiate'), [
                'make' => 'Toyota',
                'model' => 'Corolla',
                'year' => now()->year,
                'vehicle_type' => 'car',
                'license_plate' => '2301-GR1',
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

        $payment->refresh();
        $this->assertNotSame($oldReference, $payment->reference);
        $this        ->assertContains($oldReference, $payment->data['previous_references']);
    }

    private function validDriverPaymentData(): array
    {
        return [
        'license_number' => 'DL-12345678',
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
