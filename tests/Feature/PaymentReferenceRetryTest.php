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
            'data' => ['license_number' => 'DL-12345678'],
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->post(route('driver.payment.initiate'))
            ->assertOk()
            ->assertViewIs('payments.paystack-form')
            ->assertSee('Pay with Paystack')
            ->assertDontSee('payWithPaystack();', false);

        $payment->refresh();
        $this->assertNotSame($oldReference, $payment->reference);
        $this->assertContains($oldReference, $payment->data['previous_references']);

        $payment->update(['status' => 'completed']);
        $this->post(route('payment.webhook'), ['reference' => $oldReference])
            ->assertOk()
            ->assertJson(['message' => 'Already processed']);
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
            ->assertSee('Pay with Paystack')
            ->assertDontSee('payWithPaystack();', false);

        $payment->refresh();
        $this->assertNotSame($oldReference, $payment->reference);
        $this->assertContains($oldReference, $payment->data['previous_references']);
    }
}
