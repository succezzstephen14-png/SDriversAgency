<?php

namespace Tests\Feature;

use App\Models\Driver;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            ->assertSee('You are starting a driver request.');
        $this->get(route('service.login'))->assertOk();

        $response = $this->post(route('service.register.post'), [
            'name' => 'Service Customer',
            'email' => 'service-customer@example.test',
            'password' => 'Safe-password-123!',
            'password_confirmation' => 'Safe-password-123!',
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
        $this->actingAs($user)->get($verificationUrl)->assertRedirect(route('service.dashboard'));

        $this->actingAs($user)->get(route('service.dashboard'))->assertOk();
        $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('service.dashboard'));
        $this->actingAs($user)->get(route('vehicles.create'))->assertRedirect(route('service.dashboard'));
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
}
