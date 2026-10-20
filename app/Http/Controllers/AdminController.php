<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\Recruitment;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Notification;
use App\Models\CmsReportSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password as PasswordFacade;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * Admin middleware check.
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!$request->user() || !$request->user()->isAdmin()) {
                abort(403, 'Unauthorized access');
            }
            return $next($request);
        });
    }

    /**
     * Send a notification and email to the model owner.
     */
    private function notifyOwner($model, string $title, string $message): void
    {
        $this->notifyModelOwner($model, $title, $message);
        $this->sendOwnerDecisionEmail($model, $title, $message);
    }

    private function sendBookingDecisionEmail(string $email, string $subject, string $body): bool
    {
        try {
            Mail::raw($body, function ($message) use ($email, $subject) {
                $message->to($email)->subject($subject);
            });

            return true;
        } catch (\Throwable $e) {
            \Log::error('Failed to send booking decision email', [
                'email' => $email,
                'subject' => $subject,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function sendBookingDecisionNotification(Booking $booking, bool $approved, ?string $reason = null): bool
    {
        $contactName = $booking->guest_name ?? $booking->user?->name ?? 'Guest';
        $contactEmail = $booking->guest_email ?? $booking->user?->email;

        if (!$contactEmail) {
            return true;
        }

        if ($booking->booking_type === 'recruitment') {
            $subject = $approved
                ? 'Your recruitment application has been approved'
                : 'Your recruitment application has been rejected';
            $body = $approved
                ? "Hello {$contactName},\n\nYour recruitment application has been approved."
                : "Hello {$contactName},\n\nYour recruitment application has been rejected.\n\nReason: {$reason}";

            return $this->sendBookingDecisionEmail($contactEmail, $subject, $body);
        }

        $bookingLabel = $booking->booking_type === 'driver' ? 'driver' : 'vehicle';
        $subject = $approved
            ? "Your {$bookingLabel} booking has been approved - SUCCEZZ Drivers Agency"
            : "Your {$bookingLabel} booking has been rejected";
        $body = $approved
            ? "Hello {$contactName},\n\nYour {$bookingLabel} booking request (ID: {$booking->id}) has been approved."
            : "Hello {$contactName},\n\nYour {$bookingLabel} booking request (ID: {$booking->id}) has been rejected.\n\nReason: {$reason}";

        if ($approved && $booking->booking_type === 'driver' && $booking->driver) {
            $body .= "\n\n=== DRIVER DETAILS ===\n";
            $body .= 'Driver: ' . ($booking->driver->user?->name ?? 'N/A') . "\n";
            $body .= 'License Number: ' . ($booking->driver->license_number ?? 'N/A') . "\n";
            $body .= 'Experience: ' . ($booking->driver->experience_years ?? 'N/A') . " years\n";
        } elseif ($approved && $booking->booking_type === 'vehicle' && $booking->vehicle) {
            $body .= "\n\n=== VEHICLE DETAILS ===\n";
            $body .= 'Vehicle: ' . trim(($booking->vehicle->make ?? '') . ' ' . ($booking->vehicle->model ?? '')) . "\n";
            $body .= 'Year: ' . ($booking->vehicle->year ?? 'N/A') . "\n";
            $body .= 'License Plate: ' . ($booking->vehicle->license_plate ?? 'N/A') . "\n";
            $body .= 'Capacity: ' . ($booking->vehicle->capacity ?? 'N/A') . " seats\n";
        }

        if ($approved) {
            if ($booking->booking_date) {
                $body .= "\nBooking Date: " . $booking->booking_date->format('F j, Y') . "\n";
            }
            if ($booking->start_time) {
                $body .= 'Start Time: ' . $booking->start_time->format('g:i A') . "\n";
            }
            if ($booking->location) {
                $body .= 'Location: ' . $booking->location . "\n";
            }

            $body .= "\nThank you for choosing SUCCEZZ Drivers Agency. If you have any questions, please contact our support team.";
        }

        return $this->sendBookingDecisionEmail($contactEmail, $subject, $body);
    }

    /**
     * Logout admin user.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Show all users for admin management.
     */
    public function users(Request $request)
    {
        $query = User::query();

        if ($search = $request->query('search')) {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = $request->query('role')) {
            $query->where('role', $role);
        }

        if ($status = $request->query('status')) {
            if ($status === 'onboarded') {
                $query->where('onboarding_completed', true);
            } elseif ($status === 'pending') {
                $query->where('onboarding_completed', false);
            }
        }

        if ($accountType = $request->query('accountType')) {
            if ($accountType === 'driver') {
                $query->has('driver');
            } elseif ($accountType === 'vehicle') {
                $query->has('vehicles');
            } elseif ($accountType === 'both') {
                $query->has('driver')->has('vehicles');
            }
        }

        $users = $query->paginate(15)->withQueryString();

        $metrics = [
            'total_users' => User::count(),
            'total_admins' => User::where('role', 'admin')->count(),
            'total_regular_users' => User::where('role', 'user')->count(),
            'completed_onboarding' => User::where('onboarding_completed', true)->count(),
            'pending_onboarding' => User::where('onboarding_completed', false)->count(),
            'new_last_30_days' => User::where('created_at', '>=', now()->subDays(30))->count(),
            'active_this_month' => User::where('updated_at', '>=', now()->startOfMonth())->count(),
        ];

        return view('admin.users', compact('users', 'metrics'));
    }

    /**
     * Edit user details.
     */
    public function editUser(User $user)
    {
        return view('admin.users-edit', compact('user'));
    }

    /**
     * Update user details.
     */
    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'role' => 'required|in:user,admin',
        ]);

        $user->pending_profile_changes = null;
        $user->fill($validated)->save();

        return redirect()->route('admin.users')
            ->with('success', 'User updated successfully!');
    }

    public function approveUserProfileChanges(User $user)
    {
        $changes = $user->pending_profile_changes;
        $allowedChanges = ['name', 'phone', 'address'];
        $changes = is_array($changes) ? array_intersect_key($changes, array_flip($allowedChanges)) : [];

        if ($changes === []) {
            return redirect()->route('admin.users.edit', $user)
                ->with('error', 'This user has no profile changes awaiting review.');
        }

        $user->fill($changes);
        $user->pending_profile_changes = null;
        $user->save();
        $user->syncOnboardingStatus();

        Notification::create([
            'user_id' => $user->id,
            'title' => 'Profile changes approved',
            'message' => 'Your updated profile details have been approved by an administrator.',
            'type' => 'system',
            'status' => 'unread',
        ]);

        return redirect()->route('admin.users.edit', $user)
            ->with('success', 'Profile changes approved and applied.');
    }

    public function rejectUserProfileChanges(User $user)
    {
        if (!$user->pending_profile_changes) {
            return redirect()->route('admin.users.edit', $user)
                ->with('error', 'This user has no profile changes awaiting review.');
        }

        $user->pending_profile_changes = null;
        $user->save();

        Notification::create([
            'user_id' => $user->id,
            'title' => 'Profile changes not approved',
            'message' => 'Your profile update was not approved. Please contact support if you need more information.',
            'type' => 'system',
            'status' => 'unread',
        ]);

        return redirect()->route('admin.users.edit', $user)
            ->with('success', 'Profile changes rejected. The user’s approved details remain unchanged.');
    }

    public function createUserPasswordResetLink(User $user)
    {
        $token = PasswordFacade::broker()->createToken($user);
        $resetLink = URL::route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ]);

        return redirect()->route('admin.users.edit', $user)
            ->with('success', 'A password reset link was generated. Share it with the user through a secure channel.')
            ->with('user_password_reset_link', $resetLink);
    }

    public function approveDriverApplicationChanges(Driver $driver)
    {
        $changes = $this->filterApplicationChanges($driver->pending_application_changes, [
            'license_number', 'license_expiry', 'license_class', 'experience_years',
            'status', 'bio', 'profile_picture',
        ]);

        if ($changes === []) {
            return redirect()->route('admin.drivers.detail', $driver)
                ->with('error', 'This driver has no application changes awaiting review.');
        }

        $pendingChanges = $driver->pending_application_changes;
        $changes = $this->promotePendingApplicationFiles('driver', $changes);
        $driver->fill($changes);
        $driver->pending_application_changes = null;
        $driver->save();
        $this->deletePrivatePendingApplicationFiles('driver', $pendingChanges);

        $this->notifyProfileReviewDecision($driver->user_id, 'Driver application changes approved', 'Your updated driver application details have been approved.');

        return redirect()->route('admin.drivers.detail', $driver)
            ->with('success', 'Driver application changes approved and applied.');
    }

    public function rejectDriverApplicationChanges(Driver $driver)
    {
        if (!$driver->pending_application_changes) {
            return redirect()->route('admin.drivers.detail', $driver)
                ->with('error', 'This driver has no application changes awaiting review.');
        }

        $this->deletePendingApplicationFiles('driver', $driver->pending_application_changes);
        $driver->pending_application_changes = null;
        $driver->save();

        $this->notifyProfileReviewDecision($driver->user_id, 'Driver application changes not approved', 'Your driver application update was not approved. Your previously approved details remain active.');

        return redirect()->route('admin.drivers.detail', $driver)
            ->with('success', 'Driver application changes rejected. The approved details remain unchanged.');
    }

    public function approveVehicleApplicationChanges(Vehicle $vehicle)
    {
        $changes = $this->filterApplicationChanges($vehicle->pending_application_changes, [
            'make', 'model', 'year', 'vehicle_type', 'color', 'license_plate',
            'registration_number', 'vin', 'fuel_type', 'transmission', 'engine_size',
            'capacity', 'number_of_seats', 'mileage', 'condition', 'status',
            'registration_date', 'registration_expiry', 'insurance_expiry', 'inspection_expiry',
            'registration_document', 'insurance_document', 'roadworthiness_certificate',
            'image_url', 'description',
        ]);

        if ($changes === []) {
            return redirect()->route('admin.vehicles.detail', $vehicle)
                ->with('error', 'This vehicle has no application changes awaiting review.');
        }

        $pendingChanges = $vehicle->pending_application_changes;
        $changes = $this->promotePendingApplicationFiles('vehicle', $changes);
        $vehicle->fill($changes);
        $vehicle->pending_application_changes = null;
        $vehicle->save();
        $this->deletePrivatePendingApplicationFiles('vehicle', $pendingChanges);

        $this->notifyProfileReviewDecision($vehicle->user_id, 'Vehicle application changes approved', 'Your updated vehicle application details have been approved.');

        return redirect()->route('admin.vehicles.detail', $vehicle)
            ->with('success', 'Vehicle application changes approved and applied.');
    }

    public function rejectVehicleApplicationChanges(Vehicle $vehicle)
    {
        if (!$vehicle->pending_application_changes) {
            return redirect()->route('admin.vehicles.detail', $vehicle)
                ->with('error', 'This vehicle has no application changes awaiting review.');
        }

        $this->deletePendingApplicationFiles('vehicle', $vehicle->pending_application_changes);
        $vehicle->pending_application_changes = null;
        $vehicle->save();

        $this->notifyProfileReviewDecision($vehicle->user_id, 'Vehicle application changes not approved', 'Your vehicle application update was not approved. Your previously approved details remain active.');

        return redirect()->route('admin.vehicles.detail', $vehicle)
            ->with('success', 'Vehicle application changes rejected. The approved details remain unchanged.');
    }

    public function viewPendingApplicationDocument(string $type, string $id, string $field, ?int $index = null)
    {
        abort_unless(in_array($type, ['driver', 'vehicle'], true), 404);

        $model = $type === 'driver' ? Driver::findOrFail($id) : Vehicle::findOrFail($id);
        $changes = $model->pending_application_changes ?? [];
        $allowedFields = $type === 'driver'
            ? ['profile_picture']
            : ['registration_document', 'insurance_document', 'roadworthiness_certificate', 'image_url'];
        abort_unless(in_array($field, $allowedFields, true), 404);
        abort_unless(array_key_exists($field, $changes), 404);

        $path = $changes[$field];
        if ($field === 'image_url') {
            abort_unless(is_array($path) && $index !== null && array_key_exists($index, $path), 404);
            $path = $path[$index];
        }

        abort_unless(is_string($path), 404);
        $disk = str_starts_with($path, 'private:') ? 'local' : 'public';
        $path = $disk === 'local' ? substr($path, strlen('private:')) : $path;
        abort_unless(Storage::disk($disk)->exists($path), 404);

        return Storage::disk($disk)->response($path);
    }

    private function filterApplicationChanges(mixed $changes, array $allowedFields): array
    {
        return is_array($changes)
            ? array_intersect_key($changes, array_flip($allowedFields))
            : [];
    }

    private function deletePendingApplicationFiles(string $type, array $changes): void
    {
        $fileFields = $type === 'driver'
            ? ['profile_picture']
            : ['registration_document', 'insurance_document', 'roadworthiness_certificate'];

        foreach ($fileFields as $field) {
            if (isset($changes[$field]) && is_string($changes[$field])) {
                $this->deletePendingApplicationFile($changes[$field]);
            }
        }

        if ($type === 'vehicle' && isset($changes['image_url']) && is_array($changes['image_url'])) {
            foreach (array_filter($changes['image_url'], 'is_string') as $path) {
                $this->deletePendingApplicationFile($path);
            }
        }
    }

    private function deletePrivatePendingApplicationFiles(string $type, array $changes): void
    {
        $fileFields = $type === 'driver'
            ? ['profile_picture']
            : ['registration_document', 'insurance_document', 'roadworthiness_certificate'];

        foreach ($fileFields as $field) {
            if (isset($changes[$field]) && is_string($changes[$field]) && str_starts_with($changes[$field], 'private:')) {
                Storage::disk('local')->delete(substr($changes[$field], strlen('private:')));
            }
        }

        if ($type === 'vehicle' && isset($changes['image_url']) && is_array($changes['image_url'])) {
            foreach ($changes['image_url'] as $path) {
                if (is_string($path) && str_starts_with($path, 'private:')) {
                    Storage::disk('local')->delete(substr($path, strlen('private:')));
                }
            }
        }
    }

    private function promotePendingApplicationFiles(string $type, array $changes): array
    {
        $fileFields = $type === 'driver'
            ? ['profile_picture']
            : ['registration_document', 'insurance_document', 'roadworthiness_certificate'];

        foreach ($fileFields as $field) {
            if (isset($changes[$field]) && is_string($changes[$field])) {
                $changes[$field] = $this->promotePendingApplicationFile($changes[$field]);
            }
        }

        if ($type === 'vehicle' && isset($changes['image_url']) && is_array($changes['image_url'])) {
            $changes['image_url'] = array_map(
                fn ($path) => is_string($path) ? $this->promotePendingApplicationFile($path) : $path,
                $changes['image_url']
            );
        }

        return $changes;
    }

    private function promotePendingApplicationFile(string $path): string
    {
        if (!str_starts_with($path, 'private:')) {
            return $path;
        }

        $privatePath = substr($path, strlen('private:'));
        if (!Storage::disk('local')->exists($privatePath)) {
            throw new \RuntimeException('An application document awaiting approval is missing.');
        }
        $contents = Storage::disk('local')->get($privatePath);
        if (!Storage::disk('public')->put($privatePath, $contents)) {
            throw new \RuntimeException('Unable to publish an approved application document.');
        }

        return $privatePath;
    }

    private function deletePendingApplicationFile(string $path): void
    {
        if (str_starts_with($path, 'private:')) {
            Storage::disk('local')->delete(substr($path, strlen('private:')));
            return;
        }

        Storage::disk('public')->delete($path);
    }

    private function notifyProfileReviewDecision(int|string $userId, string $title, string $message): void
    {
        Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => 'system',
            'status' => 'unread',
        ]);
    }

    /**
     * Show admin dashboard.
     */
    public function dashboard()
    {
        // Enhanced statistics
        $stats = [
            'total_users' => User::count(),
            'total_vehicles' => Vehicle::count(),
            'total_drivers' => Driver::count(),
            'total_bookings' => Booking::count(),
            'guest_bookings' => Booking::where(function ($query) {
                $query->whereNotNull('guest_name')
                      ->orWhereNotNull('guest_email')
                      ->orWhereNotNull('guest_phone');
            })->count(),
            'open_recruitments' => Recruitment::where('status', 'open')->count(),
            'available_vehicles' => Vehicle::where('status', 'available')->count(),
            'available_drivers' => Driver::where('status', 'available')->count(),
            'pending_drivers' => Driver::where('approval_status', 'pending')->count(),
            'pending_vehicles' => Vehicle::where('approval_status', 'pending')->count(),
            'pending_recruitments' => Recruitment::where('approval_status', 'pending')->count(),
            'pending_bookings' => Booking::where('approval_status', 'pending')->count(),
            'pending_payments' => Payment::where('approval_status', 'pending')->count(),
            'total_revenue' => (Payment::where('approval_status', 'approved')->sum('amount') ?? 0) / 100,
            'monthly_revenue' => (Payment::where('approval_status', 'approved')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('amount') ?? 0) / 100,
        ];

        $stats['pending_approvals'] = $stats['pending_drivers']
            + $stats['pending_vehicles']
            + $stats['pending_recruitments']
            + $stats['pending_bookings']
            + $stats['pending_payments'];

        // Recent activities
        $recent_activities = collect();

        // Recent user registrations
        $recent_users = User::latest()->take(3)->get();
        foreach ($recent_users as $user) {
            $recent_activities->push([
                'type' => 'user',
                'icon' => 'user-plus',
                'title' => 'New User Registration',
                'description' => $user->name . ' joined the platform',
                'time' => $user->created_at->diffForHumans()
            ]);
        }

        // Recent bookings
        $recent_bookings = Booking::with(['user', 'driver.user', 'vehicle'])->latest()->take(3)->get();
        foreach ($recent_bookings as $booking) {
            $type = $booking->booking_type === 'driver' ? 'Driver' : 'Vehicle';
            $name = $booking->driver ? $booking->driver->user->name : ($booking->vehicle ? $booking->vehicle->make . ' ' . $booking->vehicle->model : 'Unknown');
            $recent_activities->push([
                'type' => 'booking',
                'icon' => 'calendar-check',
                'title' => 'New ' . $type . ' Booking',
                'description' => 'Booked ' . $name . ' by ' . ($booking->user ? $booking->user->name : ($booking->guest_name ?? 'Guest')),
                'time' => $booking->created_at->diffForHumans()
            ]);
        }

        // Recent driver approvals
        $recent_driver_approvals = Driver::where('approval_status', 'approved')->latest()->take(2)->get();
        foreach ($recent_driver_approvals as $driver) {
            $recent_activities->push([
                'type' => 'driver',
                'icon' => 'user-check',
                'title' => 'Driver Approved',
                'description' => $driver->user->name . ' approved as driver',
                'time' => $driver->updated_at->diffForHumans()
            ]);
        }

        // Recent vehicle approvals
        $recent_vehicle_approvals = Vehicle::where('approval_status', 'approved')->latest()->take(2)->get();
        foreach ($recent_vehicle_approvals as $vehicle) {
            $recent_activities->push([
                'type' => 'vehicle',
                'icon' => 'car',
                'title' => 'Vehicle Approved',
                'description' => $vehicle->make . ' ' . $vehicle->model . ' approved for ' . $vehicle->user->name,
                'time' => $vehicle->updated_at->diffForHumans()
            ]);
        }

        $recent_activities = $recent_activities->sortByDesc(function ($activity) {
            return strtotime($activity['time']);
        })->take(8);

        // Chart data for last 14 days
        $today = now();
        $last14Days = collect(range(13, 0, -1))->map(function ($offset) use ($today) {
            return $today->copy()->subDays($offset)->format('M d');
        });

        $bookingCounts = Booking::where('created_at', '>=', $today->copy()->subDays(13))
            ->get()
            ->groupBy(function ($booking) {
                return $booking->created_at->format('M d');
            })
            ->map->count();

        $bookingsByDay = $last14Days->map(fn($label) => $bookingCounts[$label] ?? 0)->all();

        // Application status data
        $applicationStatusCounts = Recruitment::selectRaw('approval_status, count(*) as total')
            ->groupBy('approval_status')
            ->pluck('total', 'approval_status')
            ->toArray();

        $applicationStatuses = [
            'pending' => $applicationStatusCounts['pending'] ?? 0,
            'approved' => $applicationStatusCounts['approved'] ?? 0,
            'rejected' => $applicationStatusCounts['rejected'] ?? 0,
        ];

        // Revenue data for last 6 months
        $revenueMonths = collect(range(5, 0, -1))->map(function ($offset) use ($today) {
            return $today->copy()->subMonths($offset)->format('M Y');
        });

        $revenueCounts = Payment::where('approval_status', 'approved')
            ->where('created_at', '>=', $today->copy()->subMonths(5))
            ->get()
            ->groupBy(function ($payment) {
                return $payment->created_at->format('M Y');
            })
            ->map(function ($payments) {
                return $payments->sum('amount') / 100;
            });

        $revenueByMonth = $revenueMonths->map(fn($label) => $revenueCounts[$label] ?? 0)->all();

        // Recent items
        $recent_vehicles = Vehicle::with('user')->latest()->take(5)->get();
        $recent_drivers = Driver::with('user')->latest()->take(5)->get();

        return view(
            'admin.dashboard',
            compact(
                'stats',
                'recent_activities',
                'recent_vehicles',
                'recent_drivers',
                'last14Days',
                'bookingsByDay',
                'applicationStatuses',
                'revenueMonths',
                'revenueByMonth'
            )
        );
    }

    /**
     * Provide chart metrics for the admin dashboard.
     */
    public function chartData()
    {
        $today = now();
        $last14Days = collect(range(13, 0, -1))->map(function ($offset) use ($today) {
            return $today->copy()->subDays($offset)->format('M d');
        });

        $bookingCounts = Booking::where('created_at', '>=', $today->copy()->subDays(13))
            ->get()
            ->groupBy(function ($booking) {
                return $booking->created_at->format('M d');
            })
            ->map->count();

        $bookingsByDay = $last14Days->map(fn($label) => $bookingCounts[$label] ?? 0)->all();

        $applicationStatusCounts = Recruitment::selectRaw('approval_status, count(*) as total')
            ->groupBy('approval_status')
            ->pluck('total', 'approval_status')
            ->toArray();

        $months = collect(range(5, 0, -1))->map(function ($offset) use ($today) {
            return $today->copy()->subMonths($offset)->format('M Y');
        });

        $driverGrowthCounts = Driver::where('created_at', '>=', $today->copy()->subMonths(5))
            ->get()
            ->groupBy(function ($driver) {
                return $driver->created_at->format('M Y');
            })
            ->map->count();

        $vehicleGrowthCounts = Vehicle::where('created_at', '>=', $today->copy()->subMonths(5))
            ->get()
            ->groupBy(function ($vehicle) {
                return $vehicle->created_at->format('M Y');
            })
            ->map->count();

        return response()->json([
            'labels' => $last14Days->all(),
            'bookings' => $bookingsByDay,
            'application_status' => [
                'pending' => $applicationStatusCounts['pending'] ?? 0,
                'approved' => $applicationStatusCounts['approved'] ?? 0,
                'rejected' => $applicationStatusCounts['rejected'] ?? 0,
            ],
            'growth' => [
                'labels' => $months->all(),
                'drivers' => $months->map(fn($label) => $driverGrowthCounts[$label] ?? 0)->all(),
                'vehicles' => $months->map(fn($label) => $vehicleGrowthCounts[$label] ?? 0)->all(),
            ],
        ]);
    }

    /**
     * Show full application or booking details before approval or rejection.
     */
    public function applicationDetail(Booking $booking)
    {
        $booking->load('user', 'driver', 'vehicle', 'recruitment');
        return view('admin.application-detail', compact('booking'));
    }

    /**
     * Stream an uploaded document after confirming it belongs to the selected record.
     */
    public function viewSubmittedDocument(string $type, string $id, string $field, ?string $index = null)
    {
        $documentFields = [
            'driver' => [
                'license' => 'license_file',
                'license_file' => 'license_file',
                'ghana_card' => 'ghana_card_file',
                'ghana_card_file' => 'ghana_card_file',
                'passport' => 'passport_file',
                'passport_file' => 'passport_file',
                'guarantor_id' => 'guarantor_ghana_card_file',
                'guarantor_ghana_card_file' => 'guarantor_ghana_card_file',
                'second_guarantor_id' => 'second_guarantor_ghana_card_file',
                'second_guarantor_ghana_card_file' => 'second_guarantor_ghana_card_file',
                'profile_picture' => 'profile_picture',
                'profile_image' => 'profile_image',
            ],
            'vehicle' => [
                'registration' => 'registration_document',
                'registration_document' => 'registration_document',
                'insurance' => 'insurance_document',
                'insurance_document' => 'insurance_document',
                'road_worthy' => 'roadworthiness_certificate',
                'roadworthiness_certificate' => 'roadworthiness_certificate',
                'road_worthy_certificate' => 'road_worthy_certificate',
                'ownership' => 'ownership_document',
                'ownership_document' => 'ownership_document',
                'images' => 'image_url',
                'image_url' => 'image_url',
            ],
            'recruitment' => [
                'company_registration' => 'company_registration',
                'tax_clearance' => 'tax_clearance',
                'job_brief' => 'job_brief',
                'offer_document' => 'offer_document',
            ],
            'booking' => [
                'application_documents' => 'application_documents',
            ],
        ];

        abort_unless(isset($documentFields[$type][$field]), 404);

        $modelClass = match ($type) {
            'driver' => Driver::class,
            'vehicle' => Vehicle::class,
            'recruitment' => Recruitment::class,
            'booking' => Booking::class,
        };
        $record = $modelClass::findOrFail($id);
        $storedDocument = $type === 'vehicle' && $documentFields[$type][$field] === 'image_url'
            ? $record->imagePaths()
            : $record->getAttribute($documentFields[$type][$field]);

        if (is_array($storedDocument)) {
            abort_unless($index !== null && ctype_digit($index), 404);
            $storedDocument = $storedDocument[(int) $index] ?? null;
        } else {
            abort_if($index !== null, 404);
        }

        abort_unless(
            is_string($storedDocument)
                && $storedDocument !== ''
                && ! str_starts_with($storedDocument, '/')
                && ! preg_match('#(^|/)\.\.?(/|$)#', str_replace('\\', '/', $storedDocument)),
            404
        );

        $disk = Storage::disk('public');
        abort_unless($disk->exists($storedDocument), 404);

        return $disk->response($storedDocument, basename($storedDocument), [], 'inline');
    }

    /**
     * Show pending driver approvals.
     */
    public function pendingDrivers()
    {
        $drivers = Driver::where('approval_status', 'pending')->with('user')->paginate(15);
        return view('admin.pending-drivers', compact('drivers'));
    }

    /**
     * Show full driver details before approval or rejection.
     */
    public function driverDetail(Driver $driver)
    {
        $driver->load('user');
        return view('admin.driver-detail', compact('driver'));
    }

    /**
     * Save an administrator's driver vetting checklist.
     */
    public function saveDriverVettingChecks(Request $request, Driver $driver)
    {
        $validated = $request->validate([
            'checks' => [
                'nullable',
                'array',
                function ($attribute, $value, $fail) {
                    $unknownChecks = array_diff(array_keys($value), array_keys(Driver::VETTING_CHECKS));

                    if ($unknownChecks !== []) {
                        $fail('The submitted vetting checklist contains an invalid item.');
                    }
                },
            ],
            'checks.*' => ['accepted'],
        ]);

        $checks = array_fill_keys(array_keys(Driver::VETTING_CHECKS), false);

        foreach (array_keys($validated['checks'] ?? []) as $check) {
            $checks[$check] = true;
        }

        $driver->update(['vetting_checks' => $checks]);

        return redirect()->back()->with('success', 'Driver vetting checklist saved.');
    }

    /**
     * Show pending vehicle approvals.
     */
    public function pendingVehicles()
    {
        $vehicles = Vehicle::where('approval_status', 'pending')->with('user')->paginate(15);
        return view('admin.pending-vehicles', compact('vehicles'));
    }

    /**
     * Show full vehicle details before approval or rejection.
     */
    public function vehicleDetail(Vehicle $vehicle)
    {
        $vehicle->load('user');
        return view('admin.vehicle-detail', compact('vehicle'));
    }

    /**
     * Show pending recruitment approvals.
     */
    public function pendingRecruitments()
    {
        $recruitments = Recruitment::where('approval_status', 'pending')->with('user')->paginate(15);
        $requests = \App\Models\RecruitmentRequest::where('status', 'pending')->orderBy('created_at', 'desc')->paginate(15, ['*'], 'requests_page');

        return view('admin.pending-recruitments', compact('recruitments', 'requests'));
    }

    /**
     * Show full recruitment details before approval or rejection.
     */
    public function recruitmentDetail(Recruitment $recruitment)
    {
        $recruitment->load('user');
        return view('admin.recruitment-detail', compact('recruitment'));
    }

    /**
     * Show pending booking approvals.
     */
    public function pendingBookings()
    {
        $bookings = Booking::where('approval_status', 'pending')->with('user', 'driver', 'vehicle', 'recruitment')->paginate(15);
        return view('admin.pending-bookings', compact('bookings'));
    }

    /**
     * Show pending payment approvals.
     */
    public function pendingPayments()
    {
        $payments = Payment::where('approval_status', 'pending')->with('user')->paginate(15);
        return view('admin.pending-payments', compact('payments'));
    }

    /**
     * Approve driver profile.
     */
    public function approveDriver(Driver $driver)
    {
        $driver->update([
            'approval_status' => 'approved',
            'approved_at' => now(),
        ]);

        $this->notifyOwner(
            $driver,
            'Driver profile approved',
            'Your driver profile has been approved. It is available for admin review and will not be shown on the public website.'
        );

        return redirect()->back()
            ->with('success', 'Driver profile approved!');
    }

    /**
     * Reject driver profile.
     */
    public function rejectDriver(Request $request, Driver $driver)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $driver->update([
            'approval_status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        $this->notifyOwner(
            $driver,
            'Driver profile rejected',
            'Your driver profile has been rejected. Reason: ' . $validated['rejection_reason']
        );

        return redirect()->back()
            ->with('success', 'Driver profile rejected!');
    }

    /**
     * Approve vehicle.
     */
    public function approveVehicle(Vehicle $vehicle)
    {
        $vehicle->update([
            'approval_status' => 'approved',
            'approved_at' => now(),
        ]);

        $this->notifyOwner(
            $vehicle,
            'Vehicle approved',
            'Your vehicle listing has been approved and is now available for booking.'
        );

        return redirect()->back()
            ->with('success', 'Vehicle approved!');
    }

    /**
     * Reject vehicle.
     */
    public function rejectVehicle(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $vehicle->update([
            'approval_status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        $this->notifyOwner(
            $vehicle,
            'Vehicle rejected',
            'Your vehicle listing has been rejected. Reason: ' . $validated['rejection_reason']
        );

        return redirect()->back()
            ->with('success', 'Vehicle rejected!');
    }

    /**
     * Approve recruitment.
     */
    public function approveRecruitment(Recruitment $recruitment)
    {
        $recruitment->update([
            'approval_status' => 'approved',
            'approved_at' => now(),
        ]);

        $this->notifyOwner(
            $recruitment,
            'Recruitment approved',
            'Your recruitment posting has been approved and is now visible to job seekers.'
        );

        return redirect()->back()
            ->with('success', 'Recruitment posted!');
    }

    /**
     * Reject recruitment.
     */
    public function rejectRecruitment(Request $request, Recruitment $recruitment)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $recruitment->update([
            'approval_status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        $this->notifyOwner(
            $recruitment,
            'Recruitment rejected',
            'Your recruitment posting has been rejected. Reason: ' . $validated['rejection_reason']
        );

        return redirect()->back()
            ->with('success', 'Recruitment rejected!');
    }

    /**
     * Approve booking.
     */
    public function approveBooking(Booking $booking)
    {
        $booking->load(['driver', 'vehicle', 'recruitment', 'user']);
        
        $booking->update([
            'approval_status' => 'approved',
            'approved_at' => now(),
            'status' => 'confirmed',
        ]);

        $isRecruitmentApplication = $booking->booking_type === 'recruitment';
        $approvalTitle = $isRecruitmentApplication ? 'Recruitment application approved' : 'Booking approved';
        $approvalMessage = $isRecruitmentApplication
            ? 'Your recruitment application has been approved.'
            : 'Your booking request has been approved and confirmed. Please check your dashboard for details.';

        $this->notifyModelOwner(
            $booking,
            $approvalTitle,
            $approvalMessage
        );

        $emailSent = $this->sendBookingDecisionNotification($booking, true);

        $response = redirect()->back()->with('success', 'Booking approved!');

        if (!$emailSent) {
            $response->with('warning', 'The booking was approved, but the notification email could not be sent. Check the mail server certificate configuration and application logs.');
        }

        return $response;
    }

    /**
     * Reject booking.
     */
    public function rejectBooking(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $booking->update([
            'approval_status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
            'status' => 'cancelled',
        ]);

        $isRecruitmentApplication = $booking->booking_type === 'recruitment';
        $rejectionTitle = $isRecruitmentApplication ? 'Recruitment application rejected' : 'Booking rejected';
        $rejectionMessage = $isRecruitmentApplication
            ? 'Your recruitment application has been rejected. Reason: ' . $validated['rejection_reason']
            : 'Your booking request has been rejected. Reason: ' . $validated['rejection_reason'];

        $this->notifyModelOwner(
            $booking,
            $rejectionTitle,
            $rejectionMessage
        );

        $emailSent = $this->sendBookingDecisionNotification($booking, false, $validated['rejection_reason']);

        $response = redirect()->back()->with('success', 'Booking rejected!');

        if (!$emailSent) {
            $response->with('warning', 'The booking was rejected, but the notification email could not be sent. Check the mail server certificate configuration and application logs.');
        }

        return $response;
    }

    /**
     * Approve payment.
     */
    public function approvePayment(Payment $payment)
    {
        $payment->update([
            'approval_status' => 'approved',
            'approved_at' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'Payment approved!');
    }

    /**
     * Reject payment.
     */
    public function rejectPayment(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $payment->update([
            'approval_status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return redirect()->back()
            ->with('success', 'Payment rejected!');
    }

    /**
     * Delete user.
     */
    public function deleteUser(User $user)
    {
        // Prevent admin from deleting themselves
        if ($user->id === Auth::id()) {
            return redirect()->back()->with('error', 'You cannot delete your own account!');
        }

        // Delete associated records
        $user->driver()->delete();
        $user->vehicles()->delete();
        $user->recruitments()->delete();
        $user->bookings()->delete();
        $user->payments()->delete();

        $user->delete();

        return redirect()->back()->with('success', 'User deleted successfully!');
    }

    /**
     * Return user details for the admin details modal.
     */
    public function userDetails(User $user)
    {
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'address' => $user->address,
            'role' => $user->role,
            'onboarding_completed' => $user->onboarding_completed,
            'status' => $user->onboarding_completed ? 'onboarded' : 'pending',
            'created_at' => $user->created_at?->toIso8601String(),
        ]);
    }

    /**
     * Toggle the onboarding status of a user.
     */
    public function toggleUserStatus(User $user)
    {
        if ($user->id === Auth::id()) {
            return response()->json(['success' => false, 'message' => 'You cannot change your own onboarding status.'], 403);
        }

        $user->onboarding_completed = !$user->onboarding_completed;
        $user->onboarding_completed ? $user->onboarded_at = now() : $user->onboarded_at = null;
        $user->save();

        return response()->json(['success' => true, 'message' => 'User onboarding status updated successfully']);
    }

    /**
     * Change the role of selected users.
     */
    public function bulkChangeRole(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
            'role' => 'required|in:user,admin',
        ]);

        User::whereIn('id', $validated['user_ids'])
            ->where('id', '!=', Auth::id())
            ->update(['role' => $validated['role']]);

        return response()->json(['success' => true, 'message' => 'Roles updated successfully']);
    }

    /**
     * Toggle onboarding status for selected users.
     */
    public function bulkToggleStatus(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
        ]);

        $users = User::whereIn('id', $validated['user_ids'])->where('id', '!=', Auth::id())->get();
        foreach ($users as $user) {
            $user->onboarding_completed = !$user->onboarding_completed;
            $user->onboarding_completed ? $user->onboarded_at = now() : $user->onboarded_at = null;
            $user->save();
        }

        return response()->json(['success' => true, 'message' => 'User statuses toggled successfully']);
    }

    /**
     * Delete selected users.
     */
    public function bulkDeleteUsers(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
        ]);

        $users = User::whereIn('id', $validated['user_ids'])
            ->where('id', '!=', Auth::id())
            ->get();

        foreach ($users as $user) {
            $user->driver()->delete();
            $user->vehicles()->delete();
            $user->recruitments()->delete();
            $user->bookings()->delete();
            $user->payments()->delete();
            $user->delete();
        }

        return response()->json(['success' => true, 'message' => 'Selected users deleted successfully']);
    }

    /**
     * Send notification to a specific user.
     */
    public function notifyUser(Request $request, User $user)
    {
        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        Notification::create([
            'user_id' => $user->id,
            'title' => 'Admin Message',
            'message' => $request->message,
            'type' => 'admin_message'
        ]);

        return response()->json(['success' => true, 'message' => 'Notification sent successfully']);
    }

    /**
     * Send bulk notifications to selected users.
     */
    public function bulkNotifyUsers(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
            'message' => 'required|string|max:1000'
        ]);

        $count = 0;
        foreach ($request->user_ids as $userId) {
            Notification::create([
                'user_id' => $userId,
                'title' => 'Admin Message',
                'message' => $request->message,
                'type' => 'admin_message'
            ]);
            $count++;
        }

        return response()->json(['success' => true, 'message' => "Notification sent to {$count} users"]);
    }

    /**
     * Show unified recruitment review system.
     */
    public function recruitmentReview()
    {
        $applications = collect();

        // Get pending drivers
        $drivers = Driver::where('approval_status', 'pending')
            ->with('user')
            ->get()
            ->map(function ($driver) {
                return [
                    'id' => $driver->id,
                    'type' => 'driver',
                    'name' => $driver->user->name,
                    'email' => $driver->user->email,
                    'phone' => $driver->user->phone,
                    'role' => 'Driver',
                    'submitted_at' => $driver->created_at,
                    'documents' => [
                        'license' => $driver->license_file,
                        'ghana_card' => $driver->ghana_card_file,
                        'passport' => $driver->passport_file,
                        'guarantor_id' => $driver->guarantor_ghana_card_file,
                        'second_guarantor_id' => $driver->second_guarantor_ghana_card_file,
                        'profile_picture' => $driver->profile_picture,
                    ],
                    'details' => [
                        'license_number' => $driver->license_number,
                        'license_class' => $driver->license_class,
                        'experience_years' => $driver->experience_years,
                        'location' => $driver->user->address,
                    ],
                    'model' => $driver,
                ];
            });

        // Get pending vehicles
        $vehicles = Vehicle::where('approval_status', 'pending')
            ->with('user')
            ->get()
            ->map(function ($vehicle) {
                return [
                    'id' => $vehicle->id,
                    'type' => 'vehicle',
                    'name' => $vehicle->user->name,
                    'email' => $vehicle->user->email,
                    'phone' => $vehicle->user->phone,
                    'role' => 'Vehicle Owner',
                    'submitted_at' => $vehicle->created_at,
                    'documents' => [
                        'registration' => $vehicle->registration_document,
                        'insurance' => $vehicle->insurance_document,
                        'road_worthy' => $vehicle->road_worthy_certificate,
                        'ownership' => $vehicle->ownership_document,
                        'images' => $vehicle->imagePaths(),
                    ],
                    'details' => [
                        'make_model' => $vehicle->make . ' ' . $vehicle->model,
                        'year' => $vehicle->year,
                        'capacity' => $vehicle->capacity,
                        'location' => $vehicle->location,
                    ],
                    'model' => $vehicle,
                ];
            });

        // Get pending recruitments
        $recruitments = Recruitment::where('approval_status', 'pending')
            ->with('user')
            ->get()
            ->map(function ($recruitment) {
                return [
                    'id' => $recruitment->id,
                    'type' => 'recruitment',
                    'name' => $recruitment->user->name,
                    'email' => $recruitment->user->email,
                    'phone' => $recruitment->user->phone,
                    'role' => 'Employer',
                    'submitted_at' => $recruitment->created_at,
                    'documents' => [
                        'company_registration' => $recruitment->company_registration,
                        'tax_clearance' => $recruitment->tax_clearance,
                    ],
                    'details' => [
                        'company' => $recruitment->company_name,
                        'position' => $recruitment->position_type,
                        'salary_range' => $recruitment->salary_range,
                        'location' => $recruitment->location,
                    ],
                    'model' => $recruitment,
                ];
            });

        // Get pending bookings
        $bookings = Booking::where('approval_status', 'pending')
            ->with(['user', 'driver', 'vehicle', 'recruitment'])
            ->get()
            ->map(function ($booking) {
                $role = 'Applicant';
                $details = [];

                if ($booking->driver) {
                    $details = [
                        'service' => 'Driver Booking',
                        'driver_name' => $booking->driver->user->name,
                        'booking_date' => $booking->booking_date,
                        'location' => $booking->location,
                    ];
                } elseif ($booking->vehicle) {
                    $details = [
                        'service' => 'Vehicle Booking',
                        'vehicle' => $booking->vehicle->make . ' ' . $booking->vehicle->model,
                        'booking_date' => $booking->booking_date,
                        'location' => $booking->location,
                    ];
                } elseif ($booking->recruitment) {
                    $details = [
                        'service' => 'Job Application',
                        'position' => $booking->recruitment->position_type,
                        'company' => $booking->recruitment->company_name,
                        'application_date' => $booking->created_at,
                    ];
                }

                return [
                    'id' => $booking->id,
                    'type' => 'booking',
                    'name' => $booking->user->name,
                    'email' => $booking->user->email,
                    'phone' => $booking->user->phone,
                    'role' => $role,
                    'submitted_at' => $booking->created_at,
                    'documents' => ['application_documents' => $booking->application_documents ?? []],
                    'details' => $details,
                    'model' => $booking,
                ];
            });

        // Get pending payments
        $payments = Payment::where('approval_status', 'pending')
            ->with('user')
            ->get()
            ->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'type' => 'payment',
                    'name' => $payment->user->name,
                    'email' => $payment->user->email,
                    'phone' => $payment->user->phone,
                    'role' => ucfirst($payment->type) . ' Registration',
                    'submitted_at' => $payment->created_at,
                    'documents' => [],
                    'details' => [
                        'amount' => config('app.currency_label', 'GHC') . ' ' . number_format($payment->amount / 100, 2),
                        'reference' => $payment->reference,
                        'type' => ucfirst($payment->type),
                        'status' => ucfirst($payment->status),
                    ],
                    'model' => $payment,
                ];
            });

        // Combine all applications and sort by submission date
        $applications = $drivers->concat($vehicles)->concat($recruitments)->concat($bookings)->concat($payments)
            ->sortByDesc('submitted_at')
            ->values();

        return view('admin.recruitment-review', compact('applications'));
    }

    /**
     * AJAX: Approve application.
     */
    public function ajaxApprove(Request $request)
    {
        $type = $request->input('type');
        $id = $request->input('id');

        $model = null;
        $message = '';

        switch ($type) {
            case 'driver':
                $model = Driver::findOrFail($id);
                $model->update(['approval_status' => 'approved', 'approved_at' => now()]);
                $this->notifyModelOwner($model, 'Driver application approved', 'Your driver application has been approved and is now visible on the platform.');
                $this->sendOwnerDecisionEmail($model, 'Your driver application has been approved', 'Your driver application has been approved and is now visible on the platform.');
                $message = 'Driver application approved successfully!';
                break;
            case 'vehicle':
                $model = Vehicle::findOrFail($id);
                $model->update(['approval_status' => 'approved', 'approved_at' => now()]);
                $this->notifyModelOwner($model, 'Vehicle application approved', 'Your vehicle application has been approved and is now available for booking.');
                $this->sendOwnerDecisionEmail($model, 'Your vehicle application has been approved', 'Your vehicle application has been approved and is now available for booking.');
                $message = 'Vehicle application approved successfully!';
                break;
            case 'recruitment':
                $model = Recruitment::findOrFail($id);
                $model->update(['approval_status' => 'approved', 'approved_at' => now()]);
                $this->notifyModelOwner($model, 'Recruitment approved', 'Your recruitment posting has been approved and is now visible to job seekers.');
                $this->sendOwnerDecisionEmail($model, 'Your recruitment posting has been approved', 'Your recruitment posting has been approved and is now visible to job seekers.');
                $message = 'Recruitment posting approved successfully!';
                break;
            case 'booking':
                $model = Booking::findOrFail($id);
                $model->update(['approval_status' => 'approved', 'approved_at' => now(), 'status' => 'confirmed']);
                $isRecruitmentApplication = $model->booking_type === 'recruitment';
                $this->notifyModelOwner(
                    $model,
                    $isRecruitmentApplication ? 'Recruitment application approved' : 'Booking approved',
                    $isRecruitmentApplication
                        ? 'Your recruitment application has been approved.'
                        : 'Your booking request has been approved and confirmed.'
                );
                $this->sendBookingDecisionNotification($model, true);
                $message = 'Booking approved successfully!';
                break;
            case 'payment':
                $model = Payment::findOrFail($id);
                $model->update(['approval_status' => 'approved', 'approved_at' => now()]);
                $message = 'Payment approved successfully!';
                break;
            default:
                return response()->json(['success' => false, 'message' => 'Invalid application type']);
        }

        return response()->json(['success' => true, 'message' => $message]);
    }

    /**
     * AJAX: Reject application.
     */
    public function ajaxReject(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'id' => 'required|string|max:64',
            'reason' => 'required|string|max:500',
        ]);

        $type = $validated['type'];
        $id = $validated['id'];
        $reason = $validated['reason'];

        $model = null;
        $message = '';

        switch ($type) {
            case 'driver':
                $model = Driver::findOrFail($id);
                $model->update(['approval_status' => 'rejected', 'rejection_reason' => $reason]);
                $this->notifyModelOwner($model, 'Driver application rejected', 'Your driver application has been rejected. Reason: ' . $reason);
                $this->sendOwnerDecisionEmail($model, 'Your driver application has been rejected', 'Your driver application has been rejected. Reason: ' . $reason);
                $message = 'Driver application rejected!';
                break;
            case 'vehicle':
                $model = Vehicle::findOrFail($id);
                $model->update(['approval_status' => 'rejected', 'rejection_reason' => $reason]);
                $this->notifyModelOwner($model, 'Vehicle application rejected', 'Your vehicle application has been rejected. Reason: ' . $reason);
                $this->sendOwnerDecisionEmail($model, 'Your vehicle application has been rejected', 'Your vehicle application has been rejected. Reason: ' . $reason);
                $message = 'Vehicle application rejected!';
                break;
            case 'recruitment':
                $model = Recruitment::findOrFail($id);
                $model->update(['approval_status' => 'rejected', 'rejection_reason' => $reason]);
                $this->notifyModelOwner($model, 'Recruitment rejected', 'Your recruitment posting has been rejected. Reason: ' . $reason);
                $this->sendOwnerDecisionEmail($model, 'Your recruitment posting has been rejected', 'Your recruitment posting has been rejected. Reason: ' . $reason);
                $message = 'Recruitment posting rejected!';
                break;
            case 'booking':
                $model = Booking::findOrFail($id);
                $model->update(['approval_status' => 'rejected', 'rejection_reason' => $reason, 'status' => 'cancelled']);
                $isRecruitmentApplication = $model->booking_type === 'recruitment';
                $this->notifyModelOwner(
                    $model,
                    $isRecruitmentApplication ? 'Recruitment application rejected' : 'Booking rejected',
                    $isRecruitmentApplication
                        ? 'Your recruitment application has been rejected. Reason: ' . $reason
                        : 'Your booking request has been rejected. Reason: ' . $reason
                );
                $this->sendBookingDecisionNotification($model, false, $reason);
                $message = 'Booking rejected!';
                break;
            case 'payment':
                $model = Payment::findOrFail($id);
                $model->update(['approval_status' => 'rejected', 'rejection_reason' => $reason]);
                $message = 'Payment rejected!';
                break;
            default:
                return response()->json(['success' => false, 'message' => 'Invalid application type']);
        }

        return response()->json(['success' => true, 'message' => $message]);
    }

    /**
     * Show all vehicles.
     */
    public function vehicles()
    {
        $vehicles = Vehicle::with('user')->paginate(15);
        return view('admin.vehicles', compact('vehicles'));
    }

    /**
     * Moderate vehicle.
     */
    public function moderateVehicle(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'status' => 'required|in:available,unavailable,maintenance,on_duty',
            'action' => 'nullable|in:approve,reject',
            'tracking_status' => 'nullable|in:pending,assigned,in_transit,arrived,completed,cancelled',
            'tracking_location' => 'nullable|string|max:255',
            'tracking_notes' => 'nullable|string|max:1000',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $data = ['status' => $validated['status']];

        if (isset($validated['tracking_status'])) {
            $data['tracking_status'] = $validated['tracking_status'];
            $data['tracking_updated_at'] = now();
        }
        if (isset($validated['tracking_location'])) {
            $data['tracking_location'] = $validated['tracking_location'];
        }
        if (isset($validated['tracking_notes'])) {
            $data['tracking_notes'] = $validated['tracking_notes'];
        }
        if (isset($validated['latitude']) && isset($validated['longitude'])) {
            $data['latitude'] = $validated['latitude'];
            $data['longitude'] = $validated['longitude'];
            $data['last_location_update'] = now();
        }

        $vehicle->update($data);

        return redirect()->back()
            ->with('success', 'Vehicle moderated successfully!');
    }

    /**
     * Show all drivers.
     */
    public function drivers()
    {
        $drivers = Driver::with('user')->paginate(15);
        return view('admin.drivers', compact('drivers'));
    }

    /**
     * Moderate driver.
     */
    public function moderateDriver(Request $request, Driver $driver)
    {
        $validated = $request->validate([
            'status' => 'required|in:available,unavailable,on_duty',
            'action' => 'nullable|in:approve,reject',
            'tracking_status' => 'nullable|in:pending,assigned,in_transit,arrived,completed,cancelled',
            'tracking_location' => 'nullable|string|max:255',
            'tracking_notes' => 'nullable|string|max:1000',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $data = ['status' => $validated['status']];

        if (isset($validated['tracking_status'])) {
            $data['tracking_status'] = $validated['tracking_status'];
            $data['tracking_updated_at'] = now();
        }
        if (isset($validated['tracking_location'])) {
            $data['tracking_location'] = $validated['tracking_location'];
        }
        if (isset($validated['tracking_notes'])) {
            $data['tracking_notes'] = $validated['tracking_notes'];
        }
        if (isset($validated['latitude']) && isset($validated['longitude'])) {
            $data['latitude'] = $validated['latitude'];
            $data['longitude'] = $validated['longitude'];
            $data['last_location_update'] = now();
        }

        $driver->update($data);

        return redirect()->back()
            ->with('success', 'Driver moderated successfully!');
    }

    /**
     * Show all recruitment posts.
     */
    public function recruitments()
    {
        $recruitments = Recruitment::with('user')->paginate(15);
        return view('admin.recruitments', compact('recruitments'));
    }

    /**
     * Moderate recruitment post.
     */
    public function moderateRecruitment(Request $request, Recruitment $recruitment)
    {
        $validated = $request->validate([
            'status' => 'required|in:open,closed,filled',
            'action' => 'nullable|in:approve,reject',
            'tracking_status' => 'nullable|in:pending,assigned,in_transit,arrived,completed,cancelled',
            'tracking_location' => 'nullable|string|max:255',
            'tracking_notes' => 'nullable|string|max:1000',
        ]);

        $data = ['status' => $validated['status']];

        if (isset($validated['tracking_status'])) {
            $data['tracking_status'] = $validated['tracking_status'];
            $data['tracking_updated_at'] = now();
        }
        if (isset($validated['tracking_location'])) {
            $data['tracking_location'] = $validated['tracking_location'];
        }
        if (isset($validated['tracking_notes'])) {
            $data['tracking_notes'] = $validated['tracking_notes'];
        }

        $recruitment->update($data);

        return redirect()->back()
            ->with('success', 'Recruitment post moderated successfully!');
    }

    public function analytics()
    {
        $data = [
            'users_by_month' => User::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month')
                ->selectRaw('count(*) as count')
                ->groupBy('month')
                ->orderBy('month', 'desc')
                ->take(12)
                ->get(),
            'vehicles_by_type' => Vehicle::selectRaw('vehicle_type, count(*) as count')
                ->groupBy('vehicle_type')
                ->get(),
            'drivers_by_status' => Driver::selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->get(),
            'bookings_by_month' => Booking::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month')
                ->selectRaw('count(*) as count')
                ->groupBy('month')
                ->orderBy('month', 'desc')
                ->take(12)
                ->get(),
            'payments_by_status' => Payment::selectRaw('approval_status, count(*) as count')
                ->groupBy('approval_status')
                ->get(),
            'recent_activity' => [
                'new_users_today' => User::whereDate('created_at', today())->count(),
                'new_bookings_today' => Booking::whereDate('created_at', today())->count(),
                'pending_approvals' => Driver::where('approval_status', 'pending')->count()
                    + Vehicle::where('approval_status', 'pending')->count()
                    + Recruitment::where('approval_status', 'pending')->count()
                    + Booking::where('approval_status', 'pending')->count()
                    + Payment::where('approval_status', 'pending')->count(),
                'active_drivers' => Driver::where('status', 'available')->count(),
                'active_vehicles' => Vehicle::where('status', 'available')->count(),
            ]
        ];

        return response()->json($data);
    }

    public function analyticsPage()
    {
        try {
            $cmsSettings = CmsReportSettings::getInstance();
        } catch (\Exception $e) {
            $cmsSettings = null;
        }

        $stats = [
            'total_users' => User::count(),
            'total_vehicles' => Vehicle::count(),
            'total_drivers' => Driver::count(),
            'total_bookings' => Booking::count(),
            'open_recruitments' => Recruitment::where('status', 'open')->count(),
            'pending_drivers' => Driver::where('approval_status', 'pending')->count(),
            'pending_vehicles' => Vehicle::where('approval_status', 'pending')->count(),
            'pending_recruitments' => Recruitment::where('approval_status', 'pending')->count(),
            'pending_bookings' => Booking::where('approval_status', 'pending')->count(),
            'pending_payments' => Payment::where('approval_status', 'pending')->count(),
        ];

        $stats['pending_approvals'] = $stats['pending_drivers']
            + $stats['pending_vehicles']
            + $stats['pending_recruitments']
            + $stats['pending_bookings']
            + $stats['pending_payments'];

        return view('admin.analytics', compact('stats', 'cmsSettings'));
    }

    public function allBookings(Request $request)
    {
        $bookings = Booking::with(['user', 'driver.user', 'vehicle', 'recruitment', 'payment']);

        $guestFilter = false;

        if ($request->boolean('guest') || $request->query('guest') === '1') {
            $guestFilter = true;
            $bookings->where(function ($query) {
                $query->whereNotNull('guest_name')
                      ->orWhereNotNull('guest_email')
                      ->orWhereNotNull('guest_phone');
            });
        }

        $bookings = $bookings->orderBy('created_at', 'desc')
            ->paginate(12)
            ->withQueryString();

        return view('admin.bookings', compact('bookings', 'guestFilter'));
    }

    public function updateBookingTracking(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'tracking_status' => 'required|in:pending,assigned,in_transit,arrived,completed,cancelled',
            'tracking_location' => 'nullable|string|max:255',
            'tracking_notes' => 'nullable|string|max:1000',
        ]);

        $booking->update([
            'tracking_status' => $validated['tracking_status'],
            'tracking_location' => $validated['tracking_location'] ?? null,
            'tracking_notes' => $validated['tracking_notes'] ?? null,
            'tracking_updated_at' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'Booking tracking updated successfully.');
    }

    public function allApplications()
    {
        $applications = Booking::where('booking_type', 'recruitment')
            ->with(['user', 'recruitment'])
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('admin.applications', compact('applications'));
    }

    public function destroyRecruitment(Recruitment $recruitment)
    {
        $recruitment->delete();

        return redirect()->route('admin.recruitments')
            ->with('success', 'Recruitment post deleted successfully!');
    }

    /**
     * Show location tracker map view.
     */
    public function locationTracker()
    {
        $drivers = Driver::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->with('user')
            ->get()
            ->map(function ($driver) {
                return [
                    'id' => $driver->id,
                    'type' => 'driver',
                    'name' => $driver->user->name,
                    'latitude' => $driver->latitude,
                    'longitude' => $driver->longitude,
                    'status' => $driver->status,
                    'tracking_status' => $driver->tracking_status,
                    'last_update' => $driver->last_location_update?->diffForHumans(),
                    'location' => $driver->tracking_location,
                ];
            });

        $vehicles = Vehicle::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->with('user')
            ->get()
            ->map(function ($vehicle) {
                return [
                    'id' => $vehicle->id,
                    'type' => 'vehicle',
                    'name' => $vehicle->user->name . ' - ' . $vehicle->make . ' ' . $vehicle->model,
                    'latitude' => $vehicle->latitude,
                    'longitude' => $vehicle->longitude,
                    'status' => $vehicle->status,
                    'tracking_status' => $vehicle->tracking_status,
                    'last_update' => $vehicle->last_location_update?->diffForHumans(),
                    'location' => $vehicle->tracking_location,
                ];
            });

        return view('admin.location-tracker', compact('drivers', 'vehicles'));
    }

    /**
     * Get location data for map (API endpoint).
     */
    public function locationData()
    {
        $drivers = Driver::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->with('user')
            ->get()
            ->map(function ($driver) {
                return [
                    'id' => $driver->id,
                    'type' => 'driver',
                    'name' => $driver->user->name,
                    'latitude' => $driver->latitude,
                    'longitude' => $driver->longitude,
                    'status' => $driver->status,
                    'tracking_status' => $driver->tracking_status,
                    'last_update' => $driver->last_location_update?->diffForHumans(),
                    'location' => $driver->tracking_location,
                ];
            });

        $vehicles = Vehicle::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->with('user')
            ->get()
            ->map(function ($vehicle) {
                return [
                    'id' => $vehicle->id,
                    'type' => 'vehicle',
                    'name' => $vehicle->user->name . ' - ' . $vehicle->make . ' ' . $vehicle->model,
                    'latitude' => $vehicle->latitude,
                    'longitude' => $vehicle->longitude,
                    'status' => $vehicle->status,
                    'tracking_status' => $vehicle->tracking_status,
                    'last_update' => $vehicle->last_location_update?->diffForHumans(),
                    'location' => $vehicle->tracking_location,
                ];
            });

        return response()->json([
            'drivers' => $drivers,
            'vehicles' => $vehicles,
        ]);
    }

    /**
     * Edit driver from admin panel.
     */
    public function editDriverAdmin(Driver $driver)
    {
        return view('admin.driver-edit', compact('driver'));
    }

    /**
     * Update driver from admin panel.
     */
    public function updateDriverAdmin(Request $request, Driver $driver)
    {
        $validated = $request->validate([
            'license_number' => [
                'required',
                'string',
                'regex:/^ADB-\d{8}-\d{5}$/',
                'unique:drivers,license_number,' . $driver->id,
            ],
            'license_expiry' => 'required|date|after:today',
            'experience_years' => 'required|integer|min:0|max:60',
            'status' => 'required|in:available,unavailable,on_duty',
            'approval_status' => 'required|in:pending,approved,rejected',
        ]);

        $driver->update($validated);

        return redirect()->route('admin.drivers')
            ->with('success', 'Driver updated successfully!');
    }

    /**
     * Edit vehicle from admin panel.
     */
    public function editVehicleAdmin(Vehicle $vehicle)
    {
        return view('admin.vehicle-edit', compact('vehicle'));
    }

    /**
     * Update vehicle from admin panel.
     */
    public function updateVehicleAdmin(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'make' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'year' => 'required|integer|min:1900|max:' . date('Y'),
            'vehicle_type' => 'required|in:car,van,truck,motorcycle',
            'license_plate' => [
                'required',
                'string',
                'max:20',
                'unique:vehicles,license_plate,' . $vehicle->id,
                'regex:/^(?:\d{4}-[A-Z]{2}\d|[A-Z]{2}\s\d{4}-\d{2})$/i',
            ],
            'status' => 'required|in:available,unavailable,maintenance,on_duty',
            'approval_status' => 'required|in:pending,approved,rejected',
            'capacity' => 'nullable|integer|min:1|max:100',
        ], [
            'license_plate.regex' => 'Use a valid Ghana plate format, e.g., "2301-GR1" or "AS 2567-12".',
        ]);

        $vehicle->update($validated);

        return redirect()->route('admin.vehicles')
            ->with('success', 'Vehicle updated successfully!');
    }

    /**
     * Edit recruitment from admin panel.
     */
    public function editRecruitmentAdmin(Recruitment $recruitment)
    {
        return view('admin.recruitment-edit', compact('recruitment'));
    }

    /**
     * Show create recruitment form in admin panel.
     */
    public function createRecruitmentAdmin()
    {
        $recruitment = new Recruitment();
        return view('admin.recruitment-edit', compact('recruitment'));
    }

    /**
     * Store recruitment from admin panel.
     */
    public function storeRecruitmentAdmin(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'position_type' => 'required|in:driver,cleaner,gateman,vehicle_owner,security_guard,delivery_rider,maintenance_worker,office_assistant,housekeeper,warehouse_loader',
            'location' => 'required|string|max:255',
            'status' => 'required|in:open,closed,filled',
            'approval_status' => 'required|in:pending,approved,rejected',
            'salary_range_min' => 'nullable|numeric|min:0',
            'salary_range_max' => 'nullable|numeric|min:0',
        ]);

        $validated['user_id'] = auth()->id();

        Recruitment::create($validated);

        return redirect()->route('admin.recruitments')
            ->with('success', 'Recruitment created successfully!');
    }

    /**
     * Update recruitment from admin panel.
     */
    public function updateRecruitmentAdmin(Request $request, Recruitment $recruitment)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'position_type' => 'required|in:driver,cleaner,gateman,vehicle_owner,security_guard,delivery_rider,maintenance_worker,office_assistant,housekeeper,warehouse_loader',
            'location' => 'required|string|max:255',
            'status' => 'required|in:open,closed,filled',
            'approval_status' => 'required|in:pending,approved,rejected',
            'salary_range_min' => 'nullable|numeric|min:0',
            'salary_range_max' => 'nullable|numeric|min:0',
        ]);

        $recruitment->update($validated);

        return redirect()->route('admin.recruitments')
            ->with('success', 'Recruitment updated successfully!');
    }

    /**
     * Show booking details from admin panel.
     */
    public function showBookingAdmin(Booking $booking)
    {
        return view('admin.booking-detail', compact('booking'));
    }

    /**
     * Edit booking from admin panel.
     */
    public function editBookingAdmin(Booking $booking)
    {
        return view('admin.booking-edit', compact('booking'));
    }

    /**
     * Update booking from admin panel.
     */
    public function updateBookingAdmin(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,completed,cancelled',
            'approval_status' => 'required|in:pending,approved,rejected',
            'tracking_status' => 'nullable|in:pending,assigned,in_transit,arrived,completed,cancelled',
        ]);

        $booking->update($validated);

        return redirect()->route('admin.bookings')
            ->with('success', 'Booking updated successfully!');
    }

    /**
     * Show site settings page.
     */
    public function siteSettings()
    {
        $settings = \App\Models\SiteSetting::first();
        if (!$settings) {
            $settings = \App\Models\SiteSetting::create([
                'site_title' => 'Driver Recruitment Platform',
                'site_description' => 'Find and book professional drivers and vehicles for your needs.',
                'primary_color' => '#007bff',
                'secondary_color' => '#6c757d',
                'font_family' => 'Arial, sans-serif',
                'font_color' => '#000000',
                'background_color' => '#ffffff',
                'background_image' => null,
                'custom_css' => '',
                'home_page_content' => 'Welcome to our platform!',
                'footer_text' => '© 2024 Driver Recruitment Platform',
            ]);
        }
        return view('admin.site-settings', compact('settings'));
    }

    /**
     * Update site settings.
     */
    public function updateSiteSettings(Request $request)
    {
        $validated = $request->validate([
            'site_title' => 'required|string|max:255',
            'site_description' => 'nullable|string|max:500',
            'primary_color' => 'required|string|max:7',
            'secondary_color' => 'required|string|max:7',
            'font_family' => 'required|string|max:255',
            'font_color' => 'required|string|max:7',
            'background_color' => 'required|string|max:7',
            'background_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'custom_css' => 'nullable|string',
            'home_page_content' => 'nullable|string',
            'footer_text' => 'nullable|string|max:500',
        ]);

        $settings = \App\Models\SiteSetting::first();
        if (!$settings) {
            $settings = new \App\Models\SiteSetting();
        }

        if ($request->hasFile('background_image')) {
            $imageName = time() . '.' . $request->background_image->extension();
            $request->background_image->move(public_path('images'), $imageName);
            $validated['background_image'] = 'images/' . $imageName;
        }

        $settings->update($validated);

        return redirect()->back()->with('success', 'Site settings updated successfully!');
    }

    /**
     * Get advanced analytics data with filters.
     */
    public function analyticsDataAdvanced(Request $request)
    {
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date'))->startOfDay() : Carbon::now()->subMonths(12)->startOfDay();
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : Carbon::now()->endOfDay();
        $groupBy = $request->input('group_by', 'month'); // day, week, month

        $format = match($groupBy) {
            'day' => '%Y-%m-%d',
            'week' => '%Y-%W',
            'month' => '%Y-%m',
            default => '%Y-%m'
        };

        $data = [
            'users_by_period' => User::whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->selectRaw("DATE_FORMAT(created_at, '$format') as period")
                ->selectRaw('count(*) as count')
                ->groupBy('period')
                ->orderBy('period')
                ->get(),
            'revenue_by_period' => Payment::where('approval_status', 'approved')
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->selectRaw("DATE_FORMAT(created_at, '$format') as period")
                ->selectRaw('SUM(amount) / 100 as total_revenue, COUNT(*) as transaction_count')
                ->groupBy('period')
                ->orderBy('period')
                ->get(),
            'revenue_by_type' => Payment::where('approval_status', 'approved')
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->selectRaw('type, SUM(amount) / 100 as total_revenue, COUNT(*) as count')
                ->groupBy('type')
                ->get(),
            'bookings_by_type' => Booking::whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->selectRaw('booking_type, COUNT(*) as count, approval_status')
                ->groupBy('booking_type', 'approval_status')
                ->get(),
            'vehicle_types_count' => Vehicle::selectRaw('vehicle_type, COUNT(*) as count')
                ->groupBy('vehicle_type')
                ->get(),
            'recent_transactions' => Payment::where('approval_status', 'approved')
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($payment) {
                    return [
                        'id' => $payment->id,
                        'user' => $payment->user->name,
                        'type' => ucfirst($payment->type),
                        'amount' => config('app.currency_label', 'GHC') . ' ' . number_format($payment->amount / 100, 2),
                        'date' => $payment->created_at->format('M d, Y H:i'),
                    ];
                }),
            'recent_drivers' => Driver::with('user')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($driver) {
                    return [
                        'id' => $driver->id,
                        'name' => $driver->user->name,
                        'status' => ucfirst($driver->approval_status),
                        'availability' => ucfirst($driver->status),
                        'date' => $driver->created_at->format('M d, Y'),
                    ];
                }),
            'recent_vehicles' => Vehicle::with('user')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($vehicle) {
                    return [
                        'id' => $vehicle->id,
                        'name' => $vehicle->user->name . ' - ' . $vehicle->make . ' ' . $vehicle->model,
                        'status' => ucfirst($vehicle->approval_status),
                        'availability' => ucfirst($vehicle->status),
                        'date' => $vehicle->created_at->format('M d, Y'),
                    ];
                }),
            'recent_bookings' => Booking::with(['user', 'driver.user', 'vehicle.user'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($booking) {
                    return [
                        'id' => $booking->id,
                        'user' => $booking->user->name,
                        'type' => ucfirst($booking->booking_type),
                        'status' => ucfirst($booking->approval_status),
                        'date' => $booking->created_at->format('M d, Y'),
                    ];
                }),
            'summary' => [
                'total_revenue' => Payment::where('approval_status', 'approved')
                    ->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate)
                    ->sum('amount') / 100,
                'total_transactions' => Payment::where('approval_status', 'approved')
                    ->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate)
                    ->count(),
                'total_bookings' => Booking::whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate)
                    ->count(),
                'approved_drivers' => Driver::where('approval_status', 'approved')->count(),
                'approved_vehicles' => Vehicle::where('approval_status', 'approved')->count(),
                'pending_approvals' => Driver::where('approval_status', 'pending')->count()
                    + Vehicle::where('approval_status', 'pending')->count()
                    + Booking::where('approval_status', 'pending')->count()
                    + Payment::where('approval_status', 'pending')->count(),
            ]
        ];

        return response()->json($data);
    }

    /**
     * Export analytics data to CSV.
     */
    public function exportAnalyticsCSV(Request $request)
    {
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date'))->startOfDay() : Carbon::now()->subMonths(12)->startOfDay();
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : Carbon::now()->endOfDay();

        $transactions = Payment::where('approval_status', 'approved')
            ->with('user')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = 'analytics_' . date('Y-m-d') . '.csv';
        $handle = fopen('php://memory', 'w');

        // CSV Headers
        fputcsv($handle, ['Date', 'Type', 'User', 'Amount (' . config('app.currency_label', 'GHC') . ')', 'Status']);

        // CSV Data
        foreach ($transactions as $transaction) {
            fputcsv($handle, [
                $transaction->created_at->format('Y-m-d H:i:s'),
                ucfirst($transaction->type),
                $transaction->user->name,
                number_format($transaction->amount / 100, 2),
                ucfirst($transaction->approval_status),
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }

    /**
     * Export analytics data to PDF.
     */
    public function exportAnalyticsPDF(Request $request)
    {
        try {
            $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : Carbon::now()->subMonths(12);
            $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : Carbon::now();

            $data = [
                'title' => 'Analytics Report',
                'period' => $startDate->format('M d, Y') . ' - ' . $endDate->format('M d, Y'),
                'generated_at' => now()->format('M d, Y H:i:s'),
                'summary' => [
                    'total_revenue' => Payment::where('approval_status', 'approved')
                        ->whereDate('created_at', '>=', $startDate)
                        ->whereDate('created_at', '<=', $endDate)
                        ->sum('amount'),
                    'total_transactions' => Payment::where('approval_status', 'approved')
                        ->whereDate('created_at', '>=', $startDate)
                        ->whereDate('created_at', '<=', $endDate)
                        ->count(),
                    'total_bookings' => Booking::whereDate('created_at', '>=', $startDate)
                        ->whereDate('created_at', '<=', $endDate)
                        ->count(),
                ],
                'transactions' => Payment::where('approval_status', 'approved')
                    ->with('user')
                    ->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate)
                    ->orderBy('created_at', 'desc')
                    ->limit(50)
                    ->get(),
            ];

            $html = view('admin.reports.pdf-export', $data)->render();
            $filename = 'analytics_' . date('Y-m-d') . '.pdf';

            // Return as HTML that can be printed/saved as PDF
            return response($html, 200, [
                'Content-Type' => 'text/html; charset=utf-8',
                'Content-Disposition' => "inline; filename=\"$filename\"",
            ]);
        } catch (\Exception $e) {
            \Log::error('PDF Export Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Export analytics data to Excel (CSV-style content with XLSX extension).
     */
    public function exportAnalyticsExcel(Request $request)
    {
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date'))->startOfDay() : Carbon::now()->subMonths(12)->startOfDay();
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : Carbon::now()->endOfDay();

        $transactions = Payment::where('approval_status', 'approved')
            ->with('user')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = 'analytics_' . date('Y-m-d') . '.xlsx';
        $handle = fopen('php://memory', 'w');

        // CSV Headers
        fputcsv($handle, ['Date', 'Type', 'User', 'Amount (' . config('app.currency_label', 'GHC') . ')', 'Status']);

        // CSV Data
        foreach ($transactions as $transaction) {
            fputcsv($handle, [
                $transaction->created_at->format('Y-m-d H:i:s'),
                ucfirst($transaction->type),
                $transaction->user->name ?? 'N/A',
                number_format($transaction->amount / 100, 2),
                ucfirst($transaction->approval_status),
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Show report settings page.
     */
    public function reportSettings()
    {
        try {
            $settings = CmsReportSettings::getInstance();
        } catch (\Exception $e) {
            $settings = null;
        }
        return view('admin.report-settings', compact('settings'));
    }

    /**
     * Update report settings.
     */
    public function updateReportSettings(Request $request)
    {
        $validated = $request->validate([
            'page_title' => 'required|string|max:255',
            'page_description' => 'nullable|string|max:1000',
            'banner_image_url' => 'nullable|url',
            'banner_color' => 'nullable|string|max:7',
            'show_export_buttons' => 'boolean',
            'show_filters' => 'boolean',
            'show_charts' => 'boolean',
            'show_tables' => 'boolean',
            'auto_refresh' => 'boolean',
            'refresh_interval' => 'integer|min:5000|max:300000',
        ]);

        try {
            $settings = CmsReportSettings::getInstance();
            $settings->update($validated);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Database table not yet created. Please run migrations first.');
        }

        return redirect()->back()->with('success', 'Report settings updated successfully!');
    }

    /**
     * Approve a guest recruitment request and create a recruitment post.
     */
    public function approveRecruitmentRequest(\App\Models\RecruitmentRequest $request)
    {
        $request->update([
            'status' => 'approved',
        ]);

        // Create an approved Recruitment owned by the approving admin
        $recruitment = Recruitment::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
            'position_type' => $request->position_type,
            'location' => $request->location,
            'requirements' => $request->requirements,
            'status' => 'open',
            'approval_status' => 'approved',
            'posted_at' => now(),
        ]);

        // Notify guest by email
        $contactName = $request->guest_name;
        $contactEmail = $request->guest_email;

        if ($contactEmail) {
            try {
                Mail::raw(
                    "Hello {$contactName},\n\nYour recruitment request (ID: {$request->id}) has been approved and posted.\n\nTitle: {$request->title}\n\nThank you.",
                    function ($message) use ($contactEmail) {
                        $message->to($contactEmail)
                            ->subject('Your recruitment request has been approved');
                    }
                );
            } catch (\Throwable $e) {
                \Log::error('Failed to send recruitment request approval email', ['error' => $e->getMessage(), 'email' => $contactEmail]);
            }
        }

        // Create notification for newly created recruitment owner (admin)
        $this->notifyOwner($recruitment, 'Recruitment approved', 'Your recruitment posting has been approved and is now visible to job seekers.');

        return redirect()->back()->with('success', 'Recruitment request approved and posted.');
    }

    /**
     * Reject a guest recruitment request.
     */
    public function rejectRecruitmentRequest(Request $req, \App\Models\RecruitmentRequest $request)
    {
        $validated = $req->validate(['rejection_reason' => 'required|string|max:500']);

        $request->update([
            'status' => 'rejected',
        ]);

        $contactName = $request->guest_name;
        $contactEmail = $request->guest_email;

        if ($contactEmail) {
            try {
                Mail::raw(
                    "Hello {$contactName},\n\nWe are sorry to inform you that your recruitment request (ID: {$request->id}) has been rejected.\n\nReason: " . $validated['rejection_reason'],
                    function ($message) use ($contactEmail) {
                        $message->to($contactEmail)
                            ->subject('Your recruitment request has been rejected');
                    }
                );
            } catch (\Throwable $e) {
                \Log::error('Failed to send recruitment request rejection email', ['error' => $e->getMessage(), 'email' => $contactEmail]);
            }
        }

        return redirect()->back()->with('success', 'Recruitment request rejected.');
    }

}
