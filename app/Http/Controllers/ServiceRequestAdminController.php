<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Notification;
use App\Models\ServiceRequest;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class ServiceRequestAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            abort_unless(auth()->user()?->isAdmin(), 403);

            return $next($request);
        });
    }

    public function index()
    {
        $requests = ServiceRequest::with('user')->latest()->paginate(20);

        return view('admin.service-requests.index', compact('requests'));
    }

    public function show(ServiceRequest $serviceRequest)
    {
        $serviceRequest->load(['user', 'assignedDriver.user', 'assignedVehicle']);
        $drivers = Driver::approved()->where('status', 'available')->with('user')->orderBy('id')->get();
        $vehicles = Vehicle::approved()
            ->where('availability_status', 'available')
            ->where('vehicle_type', $serviceRequest->vehicle_type)
            ->orderBy('make')
            ->orderBy('model')
            ->get();

        return view('admin.service-requests.show', compact('serviceRequest', 'drivers', 'vehicles'));
    }

    public function review(Request $request, ServiceRequest $serviceRequest)
    {
        abort_unless($serviceRequest->status === 'pending', 422, 'This request has already been reviewed.');

        $decision = $request->input('decision');
        if ($decision === 'reject') {
            $validated = $request->validate([
                'decision' => ['required', Rule::in(['reject'])],
                'admin_message' => ['required', 'string', 'max:2000'],
            ]);
            $serviceRequest->update([
                'status' => 'rejected',
                'admin_message' => $validated['admin_message'],
                'reviewed_at' => now(),
            ]);
            $noticeTitle = 'Service request update';
            $noticeMessage = "Your request #{$serviceRequest->id} was not approved. " . $validated['admin_message'];
        } else {
            $validated = $request->validate([
                'decision' => ['required', Rule::in(['approve'])],
                'assigned_driver_id' => [
                    $serviceRequest->request_type === 'driver' ? 'required' : 'nullable',
                    Rule::exists('drivers', 'id')->where(fn ($query) => $query->where('approval_status', 'approved')->where('status', 'available')),
                ],
                'assigned_vehicle_id' => [
                    $serviceRequest->request_type === 'vehicle' ? 'required' : 'nullable',
                    Rule::exists('vehicles', 'id')->where(fn ($query) => $query
                        ->where('approval_status', 'approved')
                        ->where('availability_status', 'available')
                        ->where('vehicle_type', $serviceRequest->vehicle_type)),
                ],
                'quoted_amount' => ['required', 'numeric', 'min:0.01', 'max:1000000'],
                'admin_message' => ['required', 'string', 'max:2000'],
                'agreement_end_date' => ['nullable', 'date', 'after_or_equal:today'],
                'expiry_reminder_days' => ['required_with:agreement_end_date', 'nullable', Rule::in([10, 30])],
            ]);
            $serviceRequest->update([
                'status' => 'awaiting_payment',
                'assigned_driver_id' => $validated['assigned_driver_id'] ?? null,
                'assigned_vehicle_id' => $validated['assigned_vehicle_id'] ?? null,
                'quoted_amount' => (int) round(((float) $validated['quoted_amount']) * 100),
                'admin_message' => $validated['admin_message'],
                'reviewed_at' => now(),
                'agreement_end_date' => $validated['agreement_end_date'] ?? null,
                'expiry_reminder_days' => !empty($validated['agreement_end_date'])
                    ? (int) $validated['expiry_reminder_days']
                    : null,
                'expiry_reminder_sent_at' => null,
            ]);
            $noticeTitle = 'Your service request is ready';
            $noticeMessage = "Your request #{$serviceRequest->id} has been assigned and quoted at GHS "
                . number_format($serviceRequest->quoted_amount / 100, 2)
                . ". Pay securely from your service dashboard. " . $validated['admin_message'];
        }

        Notification::create([
            'user_id' => $serviceRequest->user_id,
            'title' => $noticeTitle,
            'message' => $noticeMessage,
            'type' => 'service_request',
            'status' => 'unread',
        ]);

        try {
            Mail::raw($noticeMessage, function ($message) use ($serviceRequest, $noticeTitle) {
                $message->to($serviceRequest->user->email)->subject($noticeTitle);
            });
        } catch (\Throwable $exception) {
            Log::error('Could not email service request decision.', [
                'service_request_id' => $serviceRequest->id,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            return redirect()->route('admin.service-requests.show', $serviceRequest)
                ->with('warning', 'The decision was saved and an in-dashboard notification was created, but the email could not be sent.');
        }

        return redirect()->route('admin.service-requests.show', $serviceRequest)
            ->with('success', 'The request decision was saved and the customer was notified.');
    }

    public function updateAgreement(Request $request, ServiceRequest $serviceRequest)
    {
        abort_unless(in_array($serviceRequest->status, ['awaiting_payment', 'paid', 'completed'], true), 422);

        $validated = $request->validate([
            'agreement_end_date' => ['nullable', 'date', 'after_or_equal:today'],
            'expiry_reminder_days' => ['required_with:agreement_end_date', 'nullable', Rule::in([10, 30])],
        ]);

        $dateChanged = $serviceRequest->agreement_end_date?->toDateString() !== ($validated['agreement_end_date'] ?? null)
            || (int) $serviceRequest->expiry_reminder_days !== (int) ($validated['expiry_reminder_days'] ?? 0);

        $serviceRequest->update([
            'agreement_end_date' => $validated['agreement_end_date'] ?? null,
            'expiry_reminder_days' => !empty($validated['agreement_end_date'])
                ? (int) $validated['expiry_reminder_days']
                : null,
            'expiry_reminder_sent_at' => $dateChanged ? null : $serviceRequest->expiry_reminder_sent_at,
        ]);

        return redirect()->route('admin.service-requests.show', $serviceRequest)
            ->with('success', 'The service agreement expiry and reminder schedule were updated.');
    }
}
