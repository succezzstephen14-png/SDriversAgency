<?php

namespace App\Http\Controllers;

use App\Models\AssignmentActivity;
use App\Models\Booking;
use App\Models\Driver;
use App\Models\Notification;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AssignmentController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            abort_unless(Auth::check(), 403);

            return $next($request);
        });
    }

    public function adminIndex()
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $serviceRequests = ServiceRequest::query()
            ->where(function ($query) {
                $query->whereNotNull('assigned_driver_id')->orWhereNotNull('assigned_vehicle_id');
            })
            ->with([
                'user',
                'assignedDriver.user',
                'assignedVehicle',
                'assignmentActivities.user',
            ])
            ->latest()
            ->paginate(15, ['*'], 'service_requests_page');

        $bookings = Booking::query()
            ->whereIn('booking_type', ['driver', 'vehicle'])
            ->where(function ($query) {
                $query->whereNotNull('driver_id')->orWhereNotNull('vehicle_id');
            })
            ->with(['user', 'driver.user', 'vehicle', 'assignmentActivities.user'])
            ->latest()
            ->paginate(15, ['*'], 'bookings_page');

        return view('admin.assignments.index', compact('serviceRequests', 'bookings'));
    }

    public function driverIndex()
    {
        $driver = Auth::user()->driver;
        abort_unless($driver, 403, 'A driver profile is required to view assignments.');

        $serviceRequests = ServiceRequest::query()
            ->where('assigned_driver_id', $driver->id)
            ->with(['user', 'assignedVehicle', 'assignmentActivities.user'])
            ->latest()
            ->paginate(10, ['*'], 'service_requests_page');
        $bookings = Booking::query()
            ->where('driver_id', $driver->id)
            ->where('booking_type', 'driver')
            ->where('approval_status', 'approved')
            ->where('payment_status', 'paid')
            ->where('status', '!=', 'cancelled')
            ->with(['user', 'vehicle', 'assignmentActivities.user'])
            ->latest()
            ->paginate(10, ['*'], 'bookings_page');

        return view('drivers.assignments', compact('serviceRequests', 'bookings'));
    }

    public function storeActivity(Request $request, string $type, string $id)
    {
        $assignment = $this->resolveAssignment($type, $id);
        $user = Auth::user();
        $isAdmin = $user->isAdmin();
        $isCustomer = $this->isCustomer($assignment, $user);
        $isAssignedDriver = $this->isAssignedDriver($assignment, $user);
        $isActiveAssignment = $this->isActiveAssignment($assignment);

        $validated = $request->validate([
            'entry_type' => ['required', Rule::in([
                'progress',
                'customer_report',
                'driver_report',
                'driver_assessment',
                'admin_feedback',
            ])],
            'status' => ['required_if:entry_type,progress', 'nullable', Rule::in(['scheduled', 'in_progress', 'completed'])],
            'rating' => ['required_if:entry_type,driver_assessment', 'nullable', 'integer', 'min:1', 'max:5'],
            'message' => ['required', 'string', 'min:3', 'max:3000'],
        ]);

        $entryType = $validated['entry_type'];
        abort_unless(
            match ($entryType) {
                'progress' => $isAdmin || ($isAssignedDriver && $isActiveAssignment),
                'customer_report' => $isCustomer && $isActiveAssignment && $this->hasAssignedDriver($assignment),
                'driver_report' => $isAssignedDriver && $isActiveAssignment,
                'driver_assessment' => $isAdmin && $this->hasAssignedDriver($assignment),
                'admin_feedback' => $isAdmin,
            },
            403,
            'You are not authorized to add this assignment update.'
        );

        DB::transaction(function () use ($assignment, $user, $entryType, $validated) {
            $activity = $assignment->assignmentActivities()->create([
                'user_id' => $user->id,
                'entry_type' => $entryType,
                'status' => $validated['status'] ?? null,
                'rating' => $validated['rating'] ?? null,
                'message' => $validated['message'],
            ]);

            if ($entryType === 'customer_report' || $entryType === 'driver_report') {
                User::where('role', 'admin')->get()->each(function (User $admin) use ($assignment, $activity) {
                    Notification::create([
                        'user_id' => $admin->id,
                        'title' => 'Assignment report submitted',
                        'message' => ucfirst(str_replace('_', ' ', $activity->entry_type))
                            . ' for assignment #' . $assignment->getKey() . ' requires review.',
                        'type' => 'assignment_report',
                        'status' => 'unread',
                    ]);
                });

                return;
            }

            if ($entryType === 'admin_feedback') {
                foreach ($this->participantIds($assignment) as $participantId) {
                    Notification::create([
                        'user_id' => $participantId,
                        'title' => 'Admin feedback on your assignment',
                        'message' => 'The administrator added feedback for assignment #' . $assignment->getKey() . '.',
                        'type' => 'assignment_feedback',
                        'status' => 'unread',
                    ]);
                }
            }

            if ($entryType === 'driver_assessment') {
                $driver = $assignment instanceof Booking ? $assignment->driver : $assignment->assignedDriver;
                if ($driver) {
                    Notification::create([
                        'user_id' => $driver->user_id,
                        'title' => 'Driver assessment recorded',
                        'message' => 'An administrator recorded a practical assessment for assignment #' . $assignment->getKey() . '.',
                        'type' => 'assignment_feedback',
                        'status' => 'unread',
                    ]);
                }
            }
        });

        return back()->with('success', 'Assignment update saved.');
    }

    private function resolveAssignment(string $type, string $id): Model
    {
        return match ($type) {
            'booking' => Booking::with(['driver', 'user'])->findOrFail($id),
            'service-request' => ServiceRequest::with(['assignedDriver', 'user'])->findOrFail($id),
            default => abort(404),
        };
    }

    private function hasAssignedDriver(Model $assignment): bool
    {
        return $assignment instanceof Booking
            ? $assignment->driver_id !== null
            : $assignment->assigned_driver_id !== null;
    }

    private function isCustomer(Model $assignment, User $user): bool
    {
        return (string) $assignment->user_id === (string) $user->id;
    }

    private function isAssignedDriver(Model $assignment, User $user): bool
    {
        $driverId = $assignment instanceof Booking ? $assignment->driver_id : $assignment->assigned_driver_id;

        return $driverId !== null && Driver::whereKey($driverId)->where('user_id', $user->id)->exists();
    }

    private function isActiveAssignment(Model $assignment): bool
    {
        if ($assignment instanceof ServiceRequest) {
            return in_array($assignment->status, ['paid', 'completed'], true);
        }

        return $assignment->approval_status === 'approved'
            && $assignment->payment_status === 'paid'
            && $assignment->status !== 'cancelled';
    }

    private function participantIds(Model $assignment): array
    {
        $ids = [(string) $assignment->user_id];
        $driver = $assignment instanceof Booking ? $assignment->driver : $assignment->assignedDriver;

        if ($driver) {
            $ids[] = (string) $driver->user_id;
        }

        return array_unique($ids);
    }
}
