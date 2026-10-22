<?php

namespace Tests\Feature;

use App\Models\Driver;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ServiceCustomerPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_customer_registration_requires_email_verification_for_dashboard_access(): void
    {
        Mail::fake();
        $this->get(route('service.register.driver'))
            ->assertOk()
            ->assertSee('You are starting a driver request.')
            ->assertSee('name="request_type" value="driver"', false)
            ->assertSee('Request a service');
        $this->get(route('service.login'))->assertOk();

        $response = $this->post(route('service.register.post'), [
            'name' => 'Service Customer',
            'email' => 'service-customer@example.test',
            'password' => 'Safe-password-123!',
            'password_confirmation' => 'Safe-password-123!',
            'request_type' => 'driver',
        ]);

        $user = User::where('email', 'service-customer@example.test')->firstOrFail();
        $this->assertSame('service_customer', $user->role);
        $response->assertRedirect(route('verification.notice'));

        $this->actingAs($user)
            ->get(route('service.dashboard'))
            ->assertRedirect(route('verification.notice'));

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(10),
            ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())]
        );
        $this->actingAs($user)->get($verificationUrl)->assertRedirect(route('service.requests.create', 'driver'));

        $this->actingAs($user)->get(route('service.dashboard'))->assertOk();
        $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('service.dashboard'));
        $this->actingAs($user)->get(route('vehicles.create'))->assertRedirect(route('service.dashboard'));
    }

    public function test_existing_service_customer_login_returns_to_the_selected_request_form(): void
    {
        $user = User::factory()->create([
            'role' => 'service_customer',
            'password' => 'Safe-password-123!',
        ]);

        $this->get(route('service.login', ['requestType' => 'vehicle']))
            ->assertOk()
            ->assertSee('name="request_type" value="vehicle"', false);

        $this->post(route('service.login.post'), [
            'email' => $user->email,
            'password' => 'Safe-password-123!',
            'request_type' => 'vehicle',
        ])->assertRedirect(route('service.requests.create', 'vehicle'));

        $this->actingAs($user)
            ->get(route('service.register.vehicle'))
            ->assertRedirect(route('service.requests.create', 'vehicle'));
    }

    public function test_service_customers_can_submit_separate_driver_and_vehicle_requests(): void
    {
        $user = User::factory()->create(['role' => 'service_customer']);

        $this->actingAs($user)->post(route('service.requests.store'), [
            'request_type' => 'driver',
            'service_type' => 'personal_driver',
            'location' => 'Accra',
            'preferred_start_date' => now()->addDays(3)->toDateString(),
            'duration' => 'Six months',
        ])->assertRedirect(route('service.requests.index'));

        $this->actingAs($user)->post(route('service.requests.store'), [
            'request_type' => 'vehicle',
            'service_type' => 'work_and_pay',
            'vehicle_type' => 'van',
            'location' => 'Kumasi',
            'details' => 'Need a vehicle for deliveries.',
        ])->assertRedirect(route('service.requests.index'));

        $this->assertDatabaseCount('service_requests', 2);
        $this->assertDatabaseHas('service_requests', [
            'user_id' => $user->id,
            'request_type' => 'vehicle',
            'service_type' => 'work_and_pay',
            'vehicle_type' => 'van',
            'status' => 'pending',
        ]);
    }

    public function test_customer_request_dashboard_shows_request_type_summaries_and_card_details(): void
    {
        $user = User::factory()->create(['role' => 'service_customer']);
        ServiceRequest::create([
            'user_id' => $user->id,
            'request_type' => 'driver',
            'service_type' => 'personal_driver',
            'location' => 'Accra',
            'status' => 'pending',
        ]);
        ServiceRequest::create([
            'user_id' => $user->id,
            'request_type' => 'vehicle',
            'service_type' => 'work_and_pay',
            'vehicle_type' => 'van',
            'location' => 'Kumasi',
            'status' => 'awaiting_payment',
            'quoted_amount' => 12550,
        ]);

        $this->actingAs($user)
            ->get(route('service.requests.index'))
            ->assertOk()
            ->assertSee('Track your driver and vehicle requests')
            ->assertSee('All requests')
            ->assertSee('Driver requests')
            ->assertSee('Vehicle requests')
            ->assertSee('Awaiting review')
            ->assertSee('Driver request')
            ->assertSee('Vehicle request')
            ->assertSee('Awaiting payment')
            ->assertSee('GHS 125.50');
    }

    public function test_admin_assignment_sends_dashboard_notification_and_quote(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'service_customer']);
        $driver = Driver::factory()->create([
            'approval_status' => 'approved',
            'status' => 'available',
        ]);
        $serviceRequest = ServiceRequest::create([
            'user_id' => $customer->id,
            'request_type' => 'driver',
            'service_type' => 'long_journey_driver',
            'location' => 'Accra',
        ]);

        $this->actingAs($admin)->put(route('admin.service-requests.review', $serviceRequest), [
            'decision' => 'approve',
            'assigned_driver_id' => $driver->id,
            'quoted_amount' => '250.50',
            'admin_message' => 'Your driver is available for the requested date.',
        ])->assertRedirect(route('admin.service-requests.show', $serviceRequest));

        $serviceRequest->refresh();
        $this->assertSame('awaiting_payment', $serviceRequest->status);
        $this->assertSame($driver->id, $serviceRequest->assigned_driver_id);
        $this->assertSame(25050, $serviceRequest->quoted_amount);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $customer->id,
            'type' => 'service_request',
            'status' => 'unread',
        ]);
        $this->assertSame(1, Notification::where('user_id', $customer->id)->count());
    }

    public function test_admin_can_reject_a_request_with_a_customer_message(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'service_customer']);
        $serviceRequest = ServiceRequest::create([
            'user_id' => $customer->id,
            'request_type' => 'vehicle',
            'service_type' => 'daily_sales',
            'vehicle_type' => 'car',
            'location' => 'Accra',
        ]);

        $this->actingAs($admin)->put(route('admin.service-requests.review', $serviceRequest), [
            'decision' => 'reject',
            'admin_message' => 'Please provide a later start date.',
        ])->assertRedirect(route('admin.service-requests.show', $serviceRequest));

        $this->assertSame('rejected', $serviceRequest->fresh()->status);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $customer->id,
            'message' => 'Your request #' . $serviceRequest->id . ' was not approved. Please provide a later start date.',
        ]);
    }

    public function test_only_the_request_owner_can_initiate_its_quoted_payment(): void
    {
        $owner = User::factory()->create(['role' => 'service_customer']);
        $otherCustomer = User::factory()->create(['role' => 'service_customer']);
        $serviceRequest = ServiceRequest::create([
            'user_id' => $owner->id,
            'request_type' => 'vehicle',
            'service_type' => 'work_and_pay',
            'vehicle_type' => 'van',
            'location' => 'Kumasi',
            'status' => 'awaiting_payment',
            'quoted_amount' => 12345,
        ]);

        $this->actingAs($otherCustomer)
            ->post(route('service.requests.pay', $serviceRequest))
            ->assertForbidden();

        $this->actingAs($owner)
            ->post(route('service.requests.pay', $serviceRequest))
            ->assertOk()
            ->assertViewIs('payments.paystack-form');

        $this->assertDatabaseHas('payments', [
            'user_id' => $owner->id,
            'amount' => 12345,
            'type' => 'vehicle',
        ]);
    }

    public function test_verified_service_request_payment_marks_request_paid_without_creating_a_driver_profile(): void
    {
        $customer = User::factory()->create(['role' => 'service_customer']);
        $serviceRequest = ServiceRequest::create([
            'user_id' => $customer->id,
            'request_type' => 'driver',
            'service_type' => 'personal_driver',
            'location' => 'Accra',
            'status' => 'awaiting_payment',
            'quoted_amount' => 15000,
            'payment_reference' => 'SRQ-TEST-PAYMENT',
        ]);
        $payment = Payment::create([
            'user_id' => $customer->id,
            'reference' => $serviceRequest->payment_reference,
            'amount' => 15000,
            'type' => 'driver',
            'data' => ['service_request_id' => $serviceRequest->id],
            'status' => 'completed',
        ]);

        $this->actingAs($customer)
            ->get(route('payment.success', ['reference' => $payment->reference]))
            ->assertRedirect(route('service.dashboard'));

        $this->assertSame('paid', $serviceRequest->fresh()->status);
        $this->assertDatabaseMissing('drivers', ['user_id' => $customer->id]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $customer->id,
            'title' => 'Service request payment received',
        ]);
    }

    public function test_driver_and_vehicle_service_requests_can_be_quoted_and_paid_after_paystack_verification(): void
    {
        Mail::fake();
        config(['services.paystack.secret_key' => 'sk_test_example']);
        Http::fake(function (Request $request) {
            $reference = basename(parse_url($request->url(), PHP_URL_PATH));
            $payment = Payment::where('reference', $reference)->firstOrFail();

            return Http::response([
                'status' => true,
                'data' => [
                    'status' => 'success',
                    'amount' => $payment->amount,
                    'reference' => $reference,
                ],
            ]);
        });

        $customer = User::factory()->create(['role' => 'service_customer']);
        $admin = User::factory()->create(['role' => 'admin']);
        $driver = Driver::factory()->create([
            'approval_status' => 'approved',
            'status' => 'available',
        ]);
        $vehicle = Vehicle::factory()->create([
            'approval_status' => 'approved',
            'availability_status' => 'available',
            'vehicle_type' => 'van',
        ]);

        $requestDetails = [
            [
                'request_type' => 'driver',
                'service_type' => 'personal_driver',
                'location' => 'Test Accra Service Area',
                'assigned_driver_id' => $driver->id,
                'quoted_amount' => '125.50',
            ],
            [
                'request_type' => 'vehicle',
                'service_type' => 'work_and_pay',
                'vehicle_type' => 'van',
                'location' => 'Test Kumasi Service Area',
                'assigned_vehicle_id' => $vehicle->id,
                'quoted_amount' => '200.00',
            ],
        ];

        foreach ($requestDetails as $details) {
            $this->actingAs($customer)
                ->post(route('service.requests.store'), [
                    'request_type' => $details['request_type'],
                    'service_type' => $details['service_type'],
                    'vehicle_type' => $details['vehicle_type'] ?? null,
                    'location' => $details['location'],
                    'details' => 'Test-only service request.',
                ])
                ->assertRedirect(route('service.requests.index'));

            $serviceRequest = ServiceRequest::where('user_id', $customer->id)
                ->where('request_type', $details['request_type'])
                ->latest('id')
                ->firstOrFail();

            $this->actingAs($admin)
                ->put(route('admin.service-requests.review', $serviceRequest), [
                    'decision' => 'approve',
                    'assigned_driver_id' => $details['assigned_driver_id'] ?? null,
                    'assigned_vehicle_id' => $details['assigned_vehicle_id'] ?? null,
                    'quoted_amount' => $details['quoted_amount'],
                    'admin_message' => 'TEST ONLY: Assigned for your requested dates.',
                ])
                ->assertRedirect(route('admin.service-requests.show', $serviceRequest));

            $serviceRequest->refresh();
            $this->assertSame('awaiting_payment', $serviceRequest->status);
            $this->assertSame((int) round((float) $details['quoted_amount'] * 100), $serviceRequest->quoted_amount);

            $this->actingAs($customer)
                ->post(route('service.requests.pay', $serviceRequest))
                ->assertOk()
                ->assertViewIs('payments.paystack-form');

            $payment = Payment::where('reference', $serviceRequest->fresh()->payment_reference)->firstOrFail();
            $this->actingAs($customer)
                ->get(route('payment.success', ['reference' => $payment->reference]))
                ->assertRedirect(route('service.dashboard'))
                ->assertSessionHas('success', 'Payment confirmed. Your service request is now marked as paid.');

            $this->assertSame('completed', $payment->fresh()->status);
            $this->assertSame('paid', $serviceRequest->fresh()->status);
            $this->assertDatabaseHas('notifications', [
                'user_id' => $customer->id,
                'title' => 'Service request payment received',
            ]);
        }

        $this->assertDatabaseMissing('drivers', ['user_id' => $customer->id]);
        Http::assertSentCount(2);
    }
}
