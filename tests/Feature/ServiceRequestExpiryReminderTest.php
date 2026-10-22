<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ServiceRequestExpiryReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_selected_expiry_reminder_notifies_admin_and_customer_only_once(): void
    {
        Mail::shouldReceive('raw')->twice();
        $this->travelTo(now()->setDate(2026, 10, 22)->setTime(8, 0));
        $customer = User::factory()->create(['role' => 'service_customer']);
        $admin = User::factory()->create(['role' => 'admin']);
        $serviceRequest = ServiceRequest::create([
            'user_id' => $customer->id,
            'request_type' => 'driver',
            'service_type' => 'personal_driver',
            'location' => 'Accra',
            'status' => 'paid',
            'agreement_end_date' => now()->addDays(10)->toDateString(),
            'expiry_reminder_days' => 10,
        ]);

        Artisan::call('service-requests:send-expiry-reminders');

        $this->assertNotNull($serviceRequest->fresh()->expiry_reminder_sent_at);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $customer->id,
            'title' => 'Service agreement nearing expiry',
            'type' => 'service_request_expiry',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $admin->id,
            'title' => 'Service agreement nearing expiry',
            'type' => 'service_request_expiry',
        ]);
        $this->assertSame(2, Notification::where('type', 'service_request_expiry')->count());

        Artisan::call('service-requests:send-expiry-reminders');

        $this->assertSame(2, Notification::where('type', 'service_request_expiry')->count());
    }

    public function test_selected_reminder_respects_its_lead_time_and_ignores_unpaid_or_expired_requests(): void
    {
        Mail::shouldReceive('raw')->never();
        $this->travelTo(now()->setDate(2026, 10, 22)->setTime(8, 0));
        $customer = User::factory()->create(['role' => 'service_customer']);
        User::factory()->create(['role' => 'admin']);

        $notDue = ServiceRequest::create([
            'user_id' => $customer->id,
            'request_type' => 'driver',
            'service_type' => 'personal_driver',
            'location' => 'Accra',
            'status' => 'paid',
            'agreement_end_date' => now()->addDays(20)->toDateString(),
            'expiry_reminder_days' => 10,
        ]);
        $unpaid = ServiceRequest::create([
            'user_id' => $customer->id,
            'request_type' => 'vehicle',
            'service_type' => 'work_and_pay',
            'vehicle_type' => 'van',
            'location' => 'Kumasi',
            'status' => 'awaiting_payment',
            'agreement_end_date' => now()->addDays(5)->toDateString(),
            'expiry_reminder_days' => 10,
        ]);
        $expired = ServiceRequest::create([
            'user_id' => $customer->id,
            'request_type' => 'driver',
            'service_type' => 'personal_driver',
            'location' => 'Tema',
            'status' => 'paid',
            'agreement_end_date' => now()->subDay()->toDateString(),
            'expiry_reminder_days' => 30,
        ]);

        Artisan::call('service-requests:send-expiry-reminders');

        $this->assertNull($notDue->fresh()->expiry_reminder_sent_at);
        $this->assertNull($unpaid->fresh()->expiry_reminder_sent_at);
        $this->assertNull($expired->fresh()->expiry_reminder_sent_at);
        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_admin_can_set_an_agreement_end_date_and_reminder_for_an_existing_paid_request(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'service_customer']);
        $serviceRequest = ServiceRequest::create([
            'user_id' => $customer->id,
            'request_type' => 'vehicle',
            'service_type' => 'work_and_pay',
            'vehicle_type' => 'van',
            'location' => 'Kumasi',
            'status' => 'paid',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.service-requests.agreement.update', $serviceRequest), [
                'agreement_end_date' => now()->addMonths(2)->toDateString(),
                'expiry_reminder_days' => '30',
            ])
            ->assertRedirect(route('admin.service-requests.show', $serviceRequest))
            ->assertSessionHas('success');

        $this->assertSame(now()->addMonths(2)->toDateString(), $serviceRequest->fresh()->agreement_end_date->toDateString());
        $this->assertSame(30, $serviceRequest->fresh()->expiry_reminder_days);
    }

    public function test_admin_dashboard_lists_upcoming_service_agreement_expiries(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'service_customer']);
        $endDate = now()->addDays(10)->toDateString();

        ServiceRequest::create([
            'user_id' => $customer->id,
            'request_type' => 'driver',
            'service_type' => 'personal_driver',
            'location' => 'Accra',
            'status' => 'paid',
            'agreement_end_date' => $endDate,
            'expiry_reminder_days' => 10,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Service agreements nearing expiry')
            ->assertSee($customer->name)
            ->assertSee('10 days before expiry')
            ->assertSee(route('admin.service-requests.show', 1), false);
    }
}
