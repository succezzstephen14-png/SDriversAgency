<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Driver;
use App\Models\Payment;
use App\Models\Vehicle;
use App\Models\Recruitment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BookingController extends Controller
{
    /**
     * Show available services for booking
     */
    public function index()
    {
        $vehicles = Vehicle::with('user')
            ->where('status', 'available')
            ->where('approval_status', 'approved')
            ->take(6)
            ->get();

        $recruitments = Recruitment::with('user')
            ->where('status', 'open')
            ->where('approval_status', 'approved')
            ->take(6)
            ->get();

        return view('bookings.index', compact('vehicles', 'recruitments'));
    }
    /**
     * Show booking form for drivers
     */
    public function bookDriverForm(Driver $driver)
    {
        abort(404);
    }

    /**
     * Show booking form for vehicles
     */
    public function bookVehicleForm(Vehicle $vehicle)
    {
        if ($vehicle->approval_status !== 'approved') {
            return redirect()->back()->with('error', 'This vehicle is not available for booking.');
        }
        return view('bookings.vehicle-book', compact('vehicle'));
    }

    /**
     * Show centralized booking page for driver or vehicle
     */
    public function showBookingPage($type, $id)
    {
        if ($type !== 'vehicle') {
            abort(404);
        }

        $item = Vehicle::with('user')->where('approval_status', 'approved')->findOrFail($id);
        if ($item->status !== 'available') {
            return redirect()->back()->with('error', 'This vehicle is not available for booking.');
        }

        return view('bookings.booking-page', [
            'bookingType' => $type,
            'item' => $item,
        ]);
    }

    /**
     * Show centralized application page for recruitment
     */
    public function showApplicationPage($role, $id)
    {
        $recruitment = Recruitment::findOrFail($id);
        $role = str_replace('-', '_', $role);

        if ($recruitment->approval_status !== 'approved' || $recruitment->status !== 'open') {
            return redirect()->back()->with('error', 'This opportunity is not available.');
        }

        if ($recruitment->position_type !== $role) {
            $redirectRoute = request()->is('guest/*') ? 'guest.application.page' : 'application.page';
            return redirect()->route($redirectRoute, [
                'role' => str_replace('_', '-', $recruitment->position_type),
                'recruitment' => $recruitment->id,
            ])->with('info', 'You have been redirected to the correct application role.');
        }

        $roleLabel = str_replace('_', ' ', ucfirst($role));

        return view('bookings.application-page', [
            'recruitment' => $recruitment,
            'routeRole' => $role,
            'roleLabel' => $roleLabel,
        ]);
    }

    /**
     * Store recruitment application submission
     */
    public function storeApplication(Request $request, $role, Recruitment $recruitment)
    {
        $role = str_replace('-', '_', $role);

        if (Auth::check() && Auth::user()->role !== 'user') {
            return redirect()->back()->with('error', 'Only users can apply for recruitments.');
        }

        if ($recruitment->approval_status !== 'approved' || $recruitment->status !== 'open') {
            return redirect()->back()->with('error', 'This opportunity is not available.');
        }

        if ($recruitment->position_type !== $role) {
            return redirect()->back()->with('error', 'You cannot apply for this role on the selected recruitment.');
        }

        $rules = [
            'application_role' => 'required|in:driver,cleaner,gateman,vehicle_owner,security_guard,delivery_rider,maintenance_worker,office_assistant,housekeeper,warehouse_loader',
            'description' => 'required|string|max:1000',
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ];

        if (!Auth::check()) {
            $rules['guest_name'] = 'required|string|max:255';
            $rules['guest_email'] = 'required|email|max:255';
            $rules['guest_phone'] = 'required|string|max:25';
        }

        $validated = $request->validate($rules);

        $documentPaths = [];
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $document) {
                $documentPaths[] = Storage::disk('public')->put('applications', $document);
            }
        }

        $user = Auth::user() ?? $this->resolveGuestUser($validated);

        $booking = Booking::create([
            'user_id' => $user->id,
            'recruitment_id' => $recruitment->id,
            'booking_type' => 'recruitment',
            'booking_date' => now(),
            'description' => $validated['description'],
            'application_role' => $validated['application_role'],
            'application_documents' => $documentPaths,
            'guest_name' => $validated['guest_name'] ?? null,
            'guest_email' => $validated['guest_email'] ?? null,
            'guest_phone' => $validated['guest_phone'] ?? null,
            'amount' => 0,
            'status' => 'pending',
            'approval_status' => 'pending',
            'payment_status' => 'paid',
        ]);

        $contactName = $booking->guest_name ?? $booking->user->name;
        $contactEmail = $booking->guest_email ?? $booking->user->email;

        $this->notifyAdmins(
            'New recruitment application received',
            "A new recruitment application has been submitted by {$contactName} ({$contactEmail}) for {$recruitment->title}.\n\n" .
            "Role: {$booking->application_role}\n" .
            "Application ID: {$booking->id}\n" .
            "Status: Pending approval."
        );

        $this->sendOwnerDecisionEmail(
            $booking,
            'Recruitment application received',
            'Your application has been received and is being reviewed. Management will get back to you with a decision.'
        );

        if (!Auth::check()) {
            return redirect()->route('home')
                ->with('success', 'Application sent successfully! Your application is pending admin review.');
        }

        return redirect()->route('dashboard')
            ->with('success', 'Application sent successfully! Your application is pending admin review.');
    }

    /**
     * Store driver booking
     */
    public function storeDriverBooking(Request $request, Driver $driver)
    {
        abort(404);
    }

    /**
     * Store vehicle booking
     */
    public function storeVehicleBooking(Request $request, Vehicle $vehicle)
    {
        if (Auth::id() === $vehicle->user_id) {
            return redirect()->back()->with('error', 'You cannot book your own vehicle.');
        }

        if ($vehicle->approval_status !== 'approved') {
            return redirect()->back()->with('error', 'This vehicle is not available for booking.');
        }

        $validated = $request->validate([
            'booking_date' => 'required|date|after:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'location' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $amount = 100.00; // Default amount for vehicle booking

        $payment = Payment::create([
            'user_id' => Auth::id(),
            'reference' => 'BKNG-' . strtoupper(
                substr(bin2hex(random_bytes(8)), 0, 12)
            ),
            'amount' => $amount,
            'type' => 'booking',
            'data' => array_merge($validated, [
                'booking_type' => 'vehicle',
                'vehicle_id' => $vehicle->id,
            ]),
            'status' => 'pending',
        ]);

        $title = 'Vehicle Booking Payment';

        return view('payments.paystack-form', compact('payment', 'amount', 'title'));
    }

    /**
     * Store recruitment application
     */
    public function storeRecruitmentBooking(Request $request, Recruitment $recruitment)
    {
        if (Auth::id() === $recruitment->user_id) {
            return redirect()->back()->with('error', 'You cannot apply to your own job posting.');
        }

        $rules = [
            'description' => 'required|string|max:1000',
        ];

        if (!Auth::check()) {
            $rules['guest_name'] = 'required|string|max:255';
            $rules['guest_email'] = 'required|email|max:255';
            $rules['guest_phone'] = 'required|string|max:25';
        }

        $validated = $request->validate($rules);

        $user = Auth::user() ?? $this->resolveGuestUser($validated);

        $booking = Booking::create([
            'user_id' => $user->id,
            'recruitment_id' => $recruitment->id,
            'booking_type' => 'recruitment',
            'booking_date' => now(),
            'description' => $validated['description'],
            'guest_name' => $validated['guest_name'] ?? null,
            'guest_email' => $validated['guest_email'] ?? null,
            'guest_phone' => $validated['guest_phone'] ?? null,
            'amount' => 0,
            'status' => 'pending',
            'approval_status' => 'pending',
            'payment_status' => 'paid', // No payment for recruitment
        ]);

        $contactName = $booking->guest_name ?? $booking->user->name;
        $contactEmail = $booking->guest_email ?? $booking->user->email;

        $this->notifyAdmins(
            'New recruitment application received',
            "A new recruitment application has been submitted by {$contactName} ({$contactEmail}) for {$recruitment->title}.\n\n" .
            "Application ID: {$booking->id}\n" .
            "Status: Pending approval."
        );

        $this->sendOwnerDecisionEmail(
            $booking,
            'Recruitment application received',
            'Your application has been received and is being reviewed. Management will get back to you with a decision.'
        );

        if (!Auth::check()) {
            return redirect()->route('home')
                ->with('success', 'Thank you! Your application has been submitted and is pending admin review.');
        }

        return redirect()->route('dashboard')
            ->with('success', 'Application submitted! Awaiting admin review.');
    }

    /**
     * Show booking details
     */
    public function show(Booking $booking)
    {
        if ($booking->booking_type === 'recruitment' && !Auth::user()->isAdmin()) {
            abort(403, 'Only administrators can view recruitment applications.');
        }

        if (!Auth::user()->isAdmin() && Auth::id() !== $booking->user_id) {
            abort(403, 'Unauthorized');
        }

        $booking->load(['user', 'driver.user', 'vehicle', 'assignmentActivities.user']);

        return view('bookings.show', compact('booking'));
    }

    /**
     * User's bookings list
     */
    public function myBookings()
    {
        $bookings = Auth::user()->bookings()
            ->where('booking_type', '!=', 'recruitment')
            ->with(['driver.user', 'vehicle', 'assignmentActivities.user'])
            ->latest()
            ->paginate(10);
        return view('bookings.my-bookings', compact('bookings'));
    }

    public function myApplications()
    {
        abort_unless(Auth::user()->isAdmin(), 403, 'Only administrators can view recruitment applications.');

        return redirect()->route('admin.applications');
    }
}
