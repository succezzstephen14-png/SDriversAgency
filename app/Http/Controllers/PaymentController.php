<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\Recruitment;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    /**
     * Initiate Paystack payment for driver registration.
     */
    public function initiateDriverPayment(Request $request)
    {
        // Redirect GET requests to the driver creation form
        if ($request->isMethod('GET') && empty($request->all())) {
            return redirect()->route('drivers.create')
                ->with('info', 'Please fill out the form to proceed with payment.');
        }

        $validated = $request->validate([
            'dob' => 'nullable|date|before:today',
            'emergency_person' => 'required|string|max:255',
            'emergency_phone' => 'required|string|max:20',
            'bio' => 'required|string|max:1000',
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',

            'license_number' => [
                'required',
                'string',
                'regex:/^ADB-\d{8}-\d{5}$/',
                Rule::unique('drivers', 'license_number'),
                function ($attribute, $value, $fail) {
                    if (Vehicle::where('license_plate', $value)->exists()) {
                        $fail('The driver license number cannot match an existing vehicle license plate.');
                    }
                },
            ],
            'license_expiry' => 'required|date|after:today',
            'license_class' => 'required|in:A,B,C,D,E',
            'license_file' => 'required|file|mimes:jpeg,png,jpg,gif,webp,pdf|max:5120',

            'experience_years' => 'required|integer|min:0|max:60',

            'ghana_card_no' => 'required|string|regex:/^GHA-\d{9}-\d$/',
            'ghana_card_file' => 'required|file|mimes:jpeg,png,jpg,gif,webp,pdf|max:5120',
            'passport_file' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,pdf|max:5120',

            'guarantor' => 'required|string|max:255',
            'guarantor_phone' => 'required|string|max:20',
            'g_ghana_card_no' => 'required|string|regex:/^GHA-\d{9}-\d$/',
            'guarantor_ghana_card_file' => 'required|file|mimes:jpeg,png,jpg,gif,webp,pdf|max:5120',
            'relationship' => 'required|in:family,employer,friend,colleague',
            'second_guarantor' => 'required|string|max:255',
            'second_guarantor_phone' => 'required|string|max:20',
            'second_g_ghana_card_no' => 'required|string|regex:/^GHA-\d{9}-\d$/',
            'second_guarantor_ghana_card_file' => 'required|file|mimes:jpeg,png,jpg,gif,webp,pdf|max:5120',
            'second_relationship' => 'required|in:family,employer,friend,colleague',
        ]);

        // Check for existing pending payments with the same license number
        $existingPending = Payment::where('user_id', Auth::id())
            ->where('type', 'driver')
            ->where('status', 'pending')
            ->whereJsonContains('data->license_number', $validated['license_number'])
            ->first();

        if ($existingPending) {
            return redirect()->route('payment.success', ['reference' => $existingPending->reference])
                ->with('info', 'You already have a pending payment for driver registration. Please complete your payment.');
        }

        // Handle file uploads
        $fileFields = [
            'profile_picture',
            'license_file',
            'ghana_card_file',
            'passport_file',
            'guarantor_ghana_card_file',
            'second_guarantor_ghana_card_file',
        ];
        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $validated[$field] = $request->file($field)->store('documents', 'public');
            }
        }

        $amount = 20; // GHS 0.20 in pesewas
        $reference = 'DRV-' . strtoupper(Str::random(12));

        $payment = Payment::create([
            'user_id' => Auth::id(),
            'reference' => $reference,
            'amount' => $amount,
            'type' => 'driver',
            'data' => $validated,
            'status' => 'pending',
        ]);

        return view('payments.paystack-form', compact('payment', 'amount'));
    }

    public function initiateVehiclePayment(Request $request)
    {
        // Redirect GET requests to the vehicle creation form
        if ($request->isMethod('GET') && empty($request->all())) {
            return redirect()->route('vehicles.create')
                ->with('info', 'Please fill out the form to proceed with payment.');
        }

        // Debug incoming files and inputs to help diagnose upload/validation issues
        try {
            $fileSummary = collect($request->allFiles())->map(function ($f) {
                if (is_array($f)) {
                    return collect($f)->map(fn($i) => $i->getClientOriginalName())->toArray();
                }
                return $f->getClientOriginalName();
            })->toArray();
        } catch (\Throwable $e) {
            $fileSummary = ['error' => $e->getMessage()];
        }

        \Log::info('Initiating vehicle payment', [
            'input_count' => count($request->all()),
            'files_count' => count($request->allFiles()),
            'files' => $fileSummary,
            'image_url_input' => $request->input('image_url'),
        ]);

        $validated = $request->validate([
            'make' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'year' => 'required|integer|min:1900|max:' . date('Y'),
            'vehicle_type' => 'required|in:car,van,truck,motorcycle',
            'color' => 'nullable|string|max:50',

            'license_plate' => [
                'required',
                'string',
                'max:20',
                Rule::unique('vehicles', 'license_plate'),
                function ($attribute, $value, $fail) {
                    if (Driver::where('license_number', $value)->exists()) {
                        $fail('The vehicle license plate cannot match an existing driver license number.');
                    }
                    if (!preg_match('/^(?:\d{4}-[A-Z]{2}\d|[A-Z]{2}\s\d{4}-\d{2})$/i', $value)) {
                        $fail('Use a valid Ghana plate format, e.g., "2301-GR1" or "AS 2567-12".');
                    }
                },
            ],
            'registration_number' => 'nullable|string|max:100',
            'vin' => ['nullable', 'string', 'size:17', 'regex:/^[A-HJ-NPR-Z0-9]{17}$/', Rule::unique('vehicles', 'vin')],

            'fuel_type' => 'nullable|in:petrol,diesel,hybrid,electric,gas',
            'transmission' => 'nullable|in:manual,automatic',
            'engine_size' => 'nullable|string|max:20',
            'capacity' => 'nullable|integer|min:1|max:100',
            'number_of_seats' => 'nullable|integer|min:1|max:100',
            'mileage' => 'nullable|integer|min:0',

            'condition' => 'required|in:excellent,very_good,good,fair,needs_maintenance',
            'registration_date' => 'required|date',
            'registration_expiry' => 'required|date',
            'insurance_expiry' => 'required|date',
            'inspection_expiry' => 'required|date',
            'registration_document' => 'required|file|mimes:jpeg,png,jpg,gif,webp,pdf|max:5120',
            'insurance_document' => 'required|file|mimes:jpeg,png,jpg,gif,webp,pdf|max:5120',
            'roadworthiness_certificate' => 'required|file|mimes:jpeg,png,jpg,gif,webp,pdf|max:5120',

            'image_url' => 'required|array|min:1',
            'image_url.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'description' => 'nullable|string',
        ]);

        // Check for existing pending payments with the same license plate
        $existingPending = Payment::where('user_id', Auth::id())
            ->where('type', 'vehicle')
            ->where('status', 'pending')
            ->whereJsonContains('data->license_plate', $validated['license_plate'])
            ->first();

        if ($existingPending) {
            \Log::info('Found existing pending payment for vehicle', ['reference' => $existingPending->reference]);
            return redirect()->route('payment.success', ['reference' => $existingPending->reference])
                ->with('info', 'You already have a pending payment for this vehicle. Please complete your payment.');
        }

        // Handle file uploads
        $fileFields = ['registration_document', 'insurance_document', 'roadworthiness_certificate'];
        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $validated[$field] = $request->file($field)->store('documents', 'public');
            }
        }

        // Handle multiple image uploads
        if ($request->hasFile('image_url')) {
            $imagePaths = [];
            foreach ($request->file('image_url') as $file) {
                $imagePaths[] = $file->store('documents', 'public');
            }
            $validated['image_url'] = $imagePaths;
        }

        $amount = 20; // GHS 0.20 in pesewas
        $reference = 'VEH-' . strtoupper(Str::random(12));

        \Log::info('Creating vehicle payment', [
            'reference' => $reference,
            'amount' => $amount,
            'user_id' => Auth::id(),
            'license_plate' => $validated['license_plate']
        ]);

        $payment = Payment::create([
            'user_id' => Auth::id(),
            'reference' => $reference,
            'amount' => $amount,
            'type' => 'vehicle',
            'data' => $validated,
            'status' => 'pending',
        ]);

        \Log::info('Vehicle payment created successfully', ['payment_id' => $payment->id]);

        return view('payments.paystack-form', compact('payment', 'amount'));
    }

    public function initiateRecruitmentPayment(Request $request)
    {
        // Redirect GET requests to the recruitment creation form
        if ($request->isMethod('GET') && empty($request->all())) {
            return redirect()->route('recruitments.create')
                ->with('info', 'Please fill out the form to proceed with payment.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'position_type' => 'required|in:driver,cleaner,gateman,vehicle_owner,security_guard,delivery_rider,maintenance_worker,office_assistant,housekeeper,warehouse_loader',
            'location' => 'required|string|max:255',
            'requirements' => 'nullable|string|max:2000',
            'salary_range_min' => 'nullable|numeric|min:0',
            'salary_range_max' => 'nullable|numeric|min:0',
        ]);

        // Check for existing pending payments with the same title (within 24 hours)
        $existingPending = Payment::where('user_id', Auth::id())
            ->where('type', 'recruitment')
            ->where('status', 'pending')
            ->where('created_at', '>=', now()->subHours(24))
            ->whereJsonContains('data->title', $validated['title'])
            ->first();

        if ($existingPending) {
            return redirect()->route('payment.success', ['reference' => $existingPending->reference])
                ->with('info', 'You already have a pending payment for a recruitment post. Please complete your payment.');
        }

        $amount = 2000;
        $reference = 'REC-' . strtoupper(Str::random(12));

        $payment = Payment::create([
            'user_id' => Auth::id(),
            'reference' => $reference,
            'amount' => $amount,
            'type' => 'recruitment',
            'data' => $validated,
            'status' => 'pending',
        ]);

        return view('payments.paystack-form', compact('payment', 'amount'));
    }

    /**
     * Initiate Paystack payment for driver or vehicle booking.
     */
    public function initiateBookingPayment(Request $request)
    {
        // Redirect GET requests to the book page
        if ($request->isMethod('GET')) {
            return redirect()->route('book.index')
                ->with('info', 'Please select a service to book before proceeding with payment.');
        }

        abort_if($request->input('booking_type') === 'driver', 404);

        $rules = [
            'booking_type' => 'required|in:vehicle',
            'vehicle_id' => 'required_if:booking_type,vehicle|exists:vehicles,id',
            'booking_date' => 'required|date|after:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'location' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ];

        if (!Auth::check()) {
            $rules['guest_name'] = 'required|string|max:255';
            $rules['guest_email'] = 'required|email|max:255';
            $rules['guest_phone'] = 'required|string|max:25';
        }

        $validated = $request->validate($rules);

        // Check for existing pending payments for the same booking
        $existingPending = Payment::where('user_id', Auth::id() ?? null)
            ->where('type', $validated['booking_type'])
            ->whereIn('status', ['pending', 'processing'])
            ->where('created_at', '>=', now()->subHours(1))
            ->whereJsonContains('data->booking_date', $validated['booking_date'])
            ->first();

        if ($existingPending && $validated['booking_type'] === 'driver' && isset($validated['driver_id'])) {
            if ($existingPending->data['driver_id'] === $validated['driver_id']) {
                return redirect()->route('payment.success', ['reference' => $existingPending->reference])
                    ->with('info', 'You already have a pending payment for this booking. Please complete your payment.');
            }
        }

        if ($existingPending && $validated['booking_type'] === 'vehicle' && isset($validated['vehicle_id'])) {
            if ($existingPending->data['vehicle_id'] === $validated['vehicle_id']) {
                return redirect()->route('payment.success', ['reference' => $existingPending->reference])
                    ->with('info', 'You already have a pending payment for this booking. Please complete your payment.');
            }
        }

        $vehicle = Vehicle::findOrFail($validated['vehicle_id']);
        if ($vehicle->approval_status !== 'approved' || (Auth::check() && Auth::id() === $vehicle->user_id)) {
            return redirect()->back()->with('error', 'This vehicle is not available for booking.');
        }
        $title = 'Vehicle Booking Payment';

        $reference = 'BKNG-' . strtoupper(Str::random(12));

        $startDateTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $validated['booking_date'] . ' ' . $validated['start_time']);
        $endDateTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $validated['booking_date'] . ' ' . $validated['end_time']);
        $durationMinutes = $startDateTime->diffInMinutes($endDateTime);
        $hourlyRate = 10;
        $minimumCharge = 10;
        $amount = max($minimumCharge, ceil($durationMinutes / 60) * $hourlyRate) * 100; // amount in pesewas

        
        // Handle guest user creation
        if (!Auth::check()) {
            $user = \App\Models\User::firstOrCreate(
                ['email' => $validated['guest_email']],
                [
                    'name' => $validated['guest_name'],
                    'phone' => $validated['guest_phone'] ?? null,
                    'role' => 'user',
                    'password' => bcrypt(Str::random(16)),
                ]
            );
        } else {
            $user = Auth::user();
        }

        $payment = Payment::create([
            'user_id' => $user->id,
            'reference' => $reference,
            'amount' => $amount,
            'type' => $validated['booking_type'], // Use booking_type as payment type
            'data' => array_merge($validated, [
                'booking_type' => $validated['booking_type'],
            ]),
            'status' => 'pending',
        ]);

        Booking::create([
            'user_id' => $user->id,
            'driver_id' => $validated['driver_id'] ?? null,
            'vehicle_id' => $validated['vehicle_id'] ?? null,
            'booking_type' => $validated['booking_type'],
            'booking_date' => $validated['booking_date'],
            'start_time' => $validated['booking_date'] . ' ' . $validated['start_time'],
            'end_time' => $validated['booking_date'] . ' ' . $validated['end_time'],
            'location' => $validated['location'],
            'description' => $validated['description'] ?? null,
            'guest_name' => $validated['guest_name'] ?? null,
            'guest_email' => $validated['guest_email'] ?? null,
            'guest_phone' => $validated['guest_phone'] ?? null,
            'amount' => $amount,
            'status' => 'pending',
            'approval_status' => 'pending',
            'payment_status' => 'pending',
            'payment_reference' => $reference,
        ]);

        $contactName = $validated['guest_name'] ?? $user->name;
        $contactEmail = $validated['guest_email'] ?? $user->email;

        $this->notifyAdmins(
            'New booking request received',
            "A new booking request has been submitted by {$contactName} ({$contactEmail}).\n\n" .
            "Type: " . ucfirst($validated['booking_type']) . "\n" .
            "Booking date: " . $validated['booking_date'] . "\n" .
            "Reference: {$reference}\n" .
            "Status: Pending approval."
        );

        return view('payments.paystack-form', compact('payment', 'amount', 'title'));
    }

    /**
     * Handle Paystack callback webhook.
     */
    public function handlePaystackCallback(Request $request)
    {
        $reference = $request->input('reference');
        $payment = Payment::where('reference', $reference)->first();

        if (!$payment) {
            return response()->json(['message' => 'Payment not found'], 404);
        }

        // Prevent duplicate processing
        if ($payment->status === 'completed') {
            return response()->json(['message' => 'Already processed']);
        }

        $verified = $this->verifyPaystackPayment($reference);

        if ($verified && $verified['data']['status'] === 'success') {

            // Verify amount matches
            if ($verified['data']['amount'] != $payment->amount) {
                return response()->json(['message' => 'Amount mismatch'], 400);
            }

            $payment->update(['status' => 'completed']);

            $this->createResourceFromPayment($payment);

            return response()->json(['message' => 'Payment verified successfully']);
        }

        $payment->update(['status' => 'failed']);
        return response()->json(['message' => 'Payment verification failed'], 400);
    }

    /**
     * Success page
     */
    public function paymentSuccess(Request $request)
    {
        $reference = $request->input('reference');
        \Log::info('Payment success callback', ['reference' => $reference]);

        $payment = Payment::where('reference', $reference)->first();

        if (!$payment) {
            \Log::error('Payment not found', ['reference' => $reference]);
            return redirect()->route('home')->with('error', 'Payment not found.');
        }

        if ($payment->status !== 'completed') {
            \Log::info('Verifying payment with Paystack', ['reference' => $reference]);

            $verified = $this->verifyPaystackPayment($reference);

            if ($verified && isset($verified['data']['status']) && $verified['data']['status'] === 'success') {
                if ($verified['data']['amount'] != $payment->amount) {
                    \Log::error('Payment amount mismatch', [
                        'expected' => $payment->amount,
                        'received' => $verified['data']['amount']
                    ]);
                    return redirect()->route('home')->with('error', 'Payment amount mismatch.');
                }

                \Log::info('Payment verified successfully, marking as completed');
                $payment->update(['status' => 'completed']);
            } else {
                \Log::warning('Payment verification failed', ['verification_response' => $verified]);
            }
        }

        if ($payment->status === 'completed') {
            $resourceCreated = $this->createResourceFromPayment($payment);
            if ($payment->type === 'driver' && $resourceCreated === false) {
                return redirect()->route('home')
                    ->with('error', 'Driver registration failed after payment. Please contact support.');
            }
            if (!empty($payment->data['booking_date'])) {
                if (Auth::check() && Auth::id() === $payment->user_id) {
                    return redirect()->route('bookings.my-bookings')
                        ->with('success', 'Your booking request has been submitted and is pending admin approval.');
                }

                return redirect()->route('home')
                    ->with('success', 'Your booking request has been sent. Please check the front page for updates.');
            }

            $routes = [
                'driver' => 'drivers.profile',
                'vehicle' => 'vehicles.my-vehicles',
                'recruitment' => 'recruitments.my-posts',
            ];

            return redirect()->route($routes[$payment->type] ?? 'home')
                ->with('success', ucfirst($payment->type) . ' created successfully! Pending approval.');
        }

        return redirect()->route('home')->with('error', 'Payment not completed.');
    }

    /**
     * Cancelled
     */
    public function paymentCancelled(Request $request)
    {
        $reference = $request->input('reference');
        $payment = Payment::where('reference', $reference)->first();

        if ($payment) {
            $payment->update(['status' => 'cancelled']);
        }

        return redirect()->route('home')->with('info', 'Payment cancelled.');
    }

    /**
     * Create resource safely (ONLY ONCE)
     */
    private function createResourceFromPayment($payment)
    {
        if ($payment->type === 'driver' && empty($payment->data['booking_date'])) {
            if (!Driver::where('payment_reference', $payment->reference)->exists()) {
                // Sanitize payment data - convert arrays to strings or null
                $sanitizedData = $this->sanitizePaymentData($payment->data);

                $requiredFields = [
                    'license_number', 'license_expiry', 'license_class', 'experience_years',
                    'emergency_person', 'emergency_phone', 'bio', 'profile_picture',
                    'guarantor', 'guarantor_phone', 'g_ghana_card_no',
                    'guarantor_ghana_card_file', 'relationship',
                    'second_guarantor', 'second_guarantor_phone', 'second_g_ghana_card_no',
                    'second_guarantor_ghana_card_file', 'second_relationship',
                ];
                $missingFields = array_filter($requiredFields, function ($field) use ($sanitizedData) {
                    if (!array_key_exists($field, $sanitizedData)) {
                        return true;
                    }

                    $value = $sanitizedData[$field];
                    return $value === null || $value === '' || (is_array($value) && empty($value));
                });

                if (!empty($missingFields)) {
                    \Log::error('Cannot create driver from payment: missing required driver fields', [
                        'reference' => $payment->reference,
                        'type' => $payment->type,
                        'missing_fields' => array_values($missingFields),
                        'payment_data' => $payment->data,
                    ]);

                    return false;
                }

                $driver = Driver::create(array_merge($sanitizedData, [
                    'user_id' => $payment->user_id,
                    'status' => 'available',
                    'approval_status' => 'pending',
                    'payment_status' => 'paid',
                    'payment_reference' => $payment->reference,
                ]));

                $user = $driver->user;
                $this->notifyAdmins(
                    'New driver registration pending approval',
                    "A new driver registration has been submitted and is pending your approval.\n\n" .
                    "Driver: {$user->name}\n" .
                    "License Number: {$driver->license_number}\n" .
                    "Email: {$user->email}\n" .
                    "Status: Pending approval."
                );
            }
        }

        if ($payment->type === 'vehicle' && empty($payment->data['booking_date'])) {
            \Log::info('Creating vehicle from payment', ['payment_id' => $payment->id, 'reference' => $payment->reference]);

            if (!Vehicle::where('payment_reference', $payment->reference)->exists()) {
                // Sanitize payment data - convert arrays to strings or null
                $sanitizedData = $this->sanitizePaymentData($payment->data);
                if (isset($sanitizedData['inspection_certificate']) && !isset($sanitizedData['roadworthiness_certificate'])) {
                    $sanitizedData['roadworthiness_certificate'] = $sanitizedData['inspection_certificate'];
                    unset($sanitizedData['inspection_certificate']);
                }
                
                $vehicle = Vehicle::create(array_merge($sanitizedData, [
                    'user_id' => $payment->user_id,
                    'approval_status' => 'pending',
                    'payment_status' => 'completed',
                    'payment_reference' => $payment->reference,
                ]));

                \Log::info('Vehicle created successfully', ['vehicle_id' => $vehicle->id, 'license_plate' => $vehicle->license_plate]);

                $user = $vehicle->user;
                $this->notifyAdmins(
                    'New vehicle registration pending approval',
                    "A new vehicle registration has been submitted and is pending your approval.\n\n" .
                    "Vehicle: {$vehicle->year} {$vehicle->make} {$vehicle->model}\n" .
                    "License Plate: {$vehicle->license_plate}\n" .
                    "Type: {$vehicle->vehicle_type}\n" .
                    "Owner: {$user->name}\n" .
                    "Email: {$user->email}\n" .
                    "Status: Pending approval."
                );
            } else {
                \Log::info('Vehicle already exists for payment reference', ['reference' => $payment->reference]);
            }
        }

        if ($payment->type === 'recruitment') {
            if (!Recruitment::where('payment_reference', $payment->reference)->exists()) {
                Recruitment::create(array_merge($payment->data, [
                    'user_id' => $payment->user_id,
                    'status' => 'open',
                    'posted_at' => now(),
                    'approval_status' => 'pending',
                    'payment_status' => 'completed',
                    'payment_reference' => $payment->reference,
                ]));
            }
        }

        if (!empty($payment->data['booking_date'])) {
            $data = $payment->data;
            $booking = Booking::where('payment_reference', $payment->reference)->first();

            if ($booking) {
                $booking->update([
                    'payment_status' => 'paid',
                    'payment_date' => now(),
                    'amount' => $payment->amount,
                ]);
                return true;
            }

            $startTime = $data['booking_date'] . ' ' . $data['start_time'];
            $endTime = $data['booking_date'] . ' ' . $data['end_time'];

            $booking = Booking::create([
                'user_id' => $payment->user_id,
                'driver_id' => $data['driver_id'] ?? null,
                'vehicle_id' => $data['vehicle_id'] ?? null,
                'booking_type' => $data['booking_type'] ?? $payment->type,
                'booking_date' => $data['booking_date'],
                'start_time' => $startTime,
                'end_time' => $endTime,
                'location' => $data['location'] ?? null,
                'description' => $data['description'] ?? null,
                'guest_name' => $data['guest_name'] ?? null,
                'guest_email' => $data['guest_email'] ?? null,
                'guest_phone' => $data['guest_phone'] ?? null,
                'amount' => $payment->amount,
                'status' => 'pending',
                'approval_status' => 'pending',
                'payment_status' => 'paid',
                'payment_reference' => $payment->reference,
                'payment_date' => now(),
            ]);

            $contactName = $booking->guest_name ?? $booking->user?->name ?? 'Guest';
            $contactEmail = $booking->guest_email ?? $booking->user?->email ?? 'No email provided';

            $this->notifyAdmins(
                'New booking request received',
                "A new booking request has been submitted by {$contactName} ({$contactEmail}).\n\n" .
                "Booking ID: {$booking->id}\n" .
                "Service: " . ucfirst($booking->booking_type) . "\n" .
                "Status: Pending approval."
            );
        }

        return true;
    }

    /**
     * Sanitize payment data - convert arrays and non-scalar values to strings or null
     */
    private function sanitizePaymentData($data)
    {
        if (!is_array($data)) {
            return $data;
        }

        $sanitized = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if ($key === 'image_url') {
                    $sanitized[$key] = array_values(array_filter($value, 'is_string'));
                    continue;
                }

                // Convert array to JSON string or take first element if it's a file array
                if (isset($value[0]) && is_string($value[0])) {
                    // If it's an array of strings (like file names), take first one
                    $sanitized[$key] = $value[0];
                } else {
                    // Otherwise convert to JSON
                    $sanitized[$key] = json_encode($value);
                }
            } elseif (is_object($value)) {
                // Convert objects to JSON string
                $sanitized[$key] = json_encode($value);
            } else {
                // Keep scalar values as-is
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    private function verifyPaystackPayment($reference)
    {
        $secretKey = config('services.paystack.secret_key');

        if (!$secretKey) {
            \Log::error('Paystack secret key not configured');
            return false;
        }

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . rawurlencode($reference),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer " . $secretKey,
                "Content-Type: application/json",
            ],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        if ($error) {
            \Log::error('Paystack API curl error', ['error' => $error, 'reference' => $reference]);
            return false;
        }

        if ($httpCode !== 200) {
            \Log::error('Paystack API HTTP error', ['http_code' => $httpCode, 'reference' => $reference]);
            return false;
        }

        $result = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            \Log::error('Paystack API JSON decode error', ['response' => $response, 'reference' => $reference]);
            return false;
        }

        \Log::info('Paystack verification response', ['reference' => $reference, 'status' => $result['data']['status'] ?? 'unknown']);

        return $result;
    }
}