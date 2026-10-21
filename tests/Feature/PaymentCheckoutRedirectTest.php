<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class PaymentCheckoutRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_checkout_initializes_paystack_and_redirects_to_hosted_checkout(): void
    {
        config(['services.paystack.secret_key' => 'sk_test_example']);
        Http::fake(function ($request) {
            return Http::response([
                'status' => true,
                'data' => [
                    'authorization_url' => 'https://checkout.paystack.com/test-access-code',
                    'access_code' => 'test-access-code',
                    'reference' => $request['reference'],
                ],
            ]);
        });

        $user = User::factory()->create();
        $payment = Payment::create([
            'user_id' => $user->id,
            'reference' => 'DRV-OLD-REFERENCE',
            'amount' => 20,
            'type' => 'driver',
            'data' => ['license_number' => 'DL-TEST-123'],
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)
            ->post($this->checkoutUrl($payment))
            ->assertRedirect('https://checkout.paystack.com/test-access-code');

        Http::assertSent(function ($request) use ($payment) {
            return $request->url() === 'https://api.paystack.co/transaction/initialize'
                && $request->hasHeader('Authorization', 'Bearer sk_test_example')
                && $request['amount'] === 20
                && $request['currency'] === 'GHS'
                && $request['reference'] === $payment->fresh()->reference;
        });

        $payment->refresh();
        $this->assertSame('pending', $payment->status);
        $this->assertStringStartsWith('DRV-', $payment->reference);
        $this->assertContains('DRV-OLD-REFERENCE', $payment->data['previous_references']);
    }

    public function test_checkout_rejects_a_signed_link_used_by_a_different_logged_in_user(): void
    {
        Http::fake();
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $payment = Payment::create([
            'user_id' => $owner->id,
            'reference' => 'DRV-OWNER-REFERENCE',
            'amount' => 20,
            'type' => 'driver',
            'data' => [],
            'status' => 'pending',
        ]);

        $this->actingAs($otherUser)
            ->post($this->checkoutUrl($payment))
            ->assertForbidden();

        Http::assertNothingSent();
    }

    public function test_paystack_initialization_failure_returns_an_actionable_error_without_completing_payment(): void
    {
        config(['services.paystack.secret_key' => 'sk_test_example']);
        Http::fake([
            'https://api.paystack.co/transaction/initialize' => Http::response([
                'status' => false,
                'message' => 'Unable to initialize transaction',
            ], 400),
        ]);

        $user = User::factory()->create();
        $payment = Payment::create([
            'user_id' => $user->id,
            'reference' => 'VEH-OLD-REFERENCE',
            'amount' => 20,
            'type' => 'vehicle',
            'data' => [],
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->post($this->checkoutUrl($payment))
            ->assertOk()
            ->assertViewIs('payments.paystack-form')
            ->assertSee('Paystack could not start this payment. Please try again.');

        $this->assertSame('pending', $payment->fresh()->status);
    }

    public function test_checkout_updates_service_request_reference_when_retrying_initialization(): void
    {
        config(['services.paystack.secret_key' => 'sk_test_example']);
        Http::fake(function ($request) {
            return Http::response([
                'status' => true,
                'data' => [
                    'authorization_url' => 'https://checkout.paystack.com/test-access-code',
                    'access_code' => 'test-access-code',
                    'reference' => $request['reference'],
                ],
            ]);
        });

        $user = User::factory()->create();
        $serviceRequest = ServiceRequest::create([
            'user_id' => $user->id,
            'request_type' => 'driver',
            'service_type' => 'personal_driver',
            'location' => 'Accra',
            'status' => 'awaiting_payment',
            'quoted_amount' => 2000,
            'payment_reference' => 'SRQ-OLD-REFERENCE',
        ]);
        $payment = Payment::create([
            'user_id' => $user->id,
            'reference' => 'SRQ-OLD-REFERENCE',
            'amount' => 2000,
            'type' => 'driver',
            'data' => ['service_request_id' => $serviceRequest->id],
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->post($this->checkoutUrl($payment))
            ->assertRedirect('https://checkout.paystack.com/test-access-code');

        $this->assertSame($payment->fresh()->reference, $serviceRequest->fresh()->payment_reference);
    }

    private function checkoutUrl(Payment $payment): string
    {
        return URL::temporarySignedRoute(
            'payment.checkout',
            now()->addMinutes(30),
            ['payment' => $payment->getKey()]
        );
    }
}
