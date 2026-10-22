<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ServiceCustomerController extends Controller
{
    public function registerForm(Request $request, ?string $requestType = null)
    {
        $requestType = $requestType ?? $request->query('type', 'driver');
        $requestType = in_array($requestType, ['driver', 'vehicle'], true) ? $requestType : 'driver';
        $request->session()->put('service.request_type', $requestType);

        if ($request->user()?->isServiceCustomer()) {
            if (!$request->user()->hasVerifiedEmail()) {
                return redirect()->route('verification.notice');
            }

            return redirect()->route('service.requests.create', $requestType);
        }

        return view('service.register', [
            'requestType' => $requestType,
        ]);
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', Password::defaults(), 'confirmed'],
            'request_type' => ['required', Rule::in(['driver', 'vehicle'])],
        ]);
        $request->session()->put('service.request_type', $validated['request_type']);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'service_customer',
            'onboarding_completed' => false,
        ]);

        event(new \Illuminate\Auth\Events\Registered($user));
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('verification.notice');
    }

    public function dashboard()
    {
        $requests = Auth::user()->serviceRequests()
            ->with(['assignedDriver.user', 'assignedVehicle', 'assignmentActivities.user'])
            ->latest()
            ->limit(5)
            ->get();
        $notifications = Auth::user()->alerts()->limit(5)->get();
        $counts = Auth::user()->serviceRequests()
            ->selectRaw("status, COUNT(*) as total")
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('service.dashboard', compact('requests', 'counts', 'notifications'));
    }

    public function requests()
    {
        $requests = Auth::user()->serviceRequests()
            ->with(['assignedDriver.user', 'assignedVehicle', 'assignmentActivities.user'])
            ->latest()
            ->paginate(10);
        $statusCounts = Auth::user()->serviceRequests()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');
        $typeCounts = Auth::user()->serviceRequests()
            ->selectRaw('request_type, COUNT(*) as total')
            ->groupBy('request_type')
            ->pluck('total', 'request_type');

        return view('service.requests', compact('requests', 'statusCounts', 'typeCounts'));
    }

    public function createRequest(string $type)
    {
        abort_unless(in_array($type, ['driver', 'vehicle'], true), 404);

        return view('service.request-form', [
            'type' => $type,
            'services' => $type === 'driver' ? ServiceRequest::DRIVER_SERVICES : ServiceRequest::VEHICLE_SERVICES,
            'vehicleTypes' => ['car' => 'Car', 'van' => 'Van', 'truck' => 'Truck', 'motorcycle' => 'Motorcycle'],
        ]);
    }

    public function storeRequest(Request $request)
    {
        $type = $request->input('request_type');
        abort_unless(in_array($type, ['driver', 'vehicle'], true), 422);
        $serviceTypes = $type === 'driver'
            ? array_keys(ServiceRequest::DRIVER_SERVICES)
            : array_keys(ServiceRequest::VEHICLE_SERVICES);

        $validated = $request->validate([
            'request_type' => ['required', Rule::in(['driver', 'vehicle'])],
            'service_type' => ['required', Rule::in($serviceTypes)],
            'vehicle_type' => [$type === 'vehicle' ? 'required' : 'nullable', Rule::in(['car', 'van', 'truck', 'motorcycle'])],
            'location' => ['required', 'string', 'max:255'],
            'preferred_start_date' => ['nullable', 'date', 'after_or_equal:today'],
            'duration' => ['nullable', 'string', 'max:100'],
            'details' => ['nullable', 'string', 'max:3000'],
        ]);
        $validated['user_id'] = Auth::id();
        $serviceRequest = ServiceRequest::create($validated);

        return redirect()->route('service.requests.index')
            ->with('success', 'Your request was submitted. It will remain pending until an administrator reviews it.');
    }

    public function pay(ServiceRequest $serviceRequest)
    {
        abort_unless($serviceRequest->user_id === Auth::id(), 403);
        abort_unless(
            $serviceRequest->status === 'awaiting_payment'
                && $serviceRequest->quoted_amount !== null
                && $serviceRequest->quoted_amount > 0,
            404
        );

        $existingPayment = Payment::where('reference', $serviceRequest->payment_reference)
            ->where('user_id', Auth::id())
            ->where('status', 'pending')
            ->first();
        if ($existingPayment) {
            return view('payments.paystack-form', [
                'payment' => $existingPayment,
                'amount' => $existingPayment->amount,
                'serviceRequest' => $serviceRequest,
            ]);
        }

        $reference = 'SRQ-' . strtoupper(Str::random(12));
        $payment = Payment::create([
            'user_id' => Auth::id(),
            'reference' => $reference,
            'amount' => $serviceRequest->quoted_amount,
            'type' => $serviceRequest->request_type === 'driver' ? 'driver' : 'vehicle',
            'data' => ['service_request_id' => $serviceRequest->id],
            'status' => 'pending',
        ]);
        $serviceRequest->update(['payment_reference' => $reference]);

        return view('payments.paystack-form', [
            'payment' => $payment,
            'amount' => $payment->amount,
            'serviceRequest' => $serviceRequest,
        ]);
    }

    public function profile()
    {
        $user = Auth::user();

        return view('service.profile', compact('user'));
    }
}
