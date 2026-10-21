<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class DriverController extends Controller
{
    /**
     * Display a listing of drivers.
     */
    public function index()
    {
        abort(404);
    }

    /**
     * Show the current user's driver profile.
     */
    public function myProfile()
    {
        $driver = Auth::user()->driver;
        return view('drivers.profile', compact('driver'));
    }

    /**
     * Show the form for creating a new driver profile.
     */
    public function create()
    {
        if (Auth::user()->driver) {
            return redirect()->route('drivers.profile')
                ->with('info', 'You already have a driver profile.');
        }
        return view('drivers.create');
    }

    /**
     * Store a newly created driver profile in database.
     */
    public function store(Request $request)
    {
        if (Auth::user()->driver) {
            return redirect()->route('drivers.profile')
                ->with('info', 'You already have a driver profile.');
        }

        $validated = $request->validate([
            // Personal Information
            'dob' => 'nullable|date|before:today',
            'emergency_person' => 'required|string|max:255',
            'emergency_phone' => 'required|string|max:20',
            'bio' => 'required|string|max:1000',
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            
            // License Information
            'license_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('drivers', 'license_number'),
                function ($attribute, $value, $fail) {
                    // Prevent a driver license number from matching any vehicle license plate
                    if (Vehicle::where('license_plate', $value)->exists()) {
                        $fail('The driver license number cannot match an existing vehicle license plate.');
                    }
                },
            ],
            'license_expiry' => 'required|date|after:today',
            'license_class' => 'required|in:A,B,C,D,E',
            'license_file' => 'required|file|mimes:jpeg,png,jpg,gif,webp,pdf|max:5120',
            
            // Experience
            'experience_years' => 'required|integer|min:0|max:60',
            
            // Identity Documents
            'ghana_card_no' => 'required|string|regex:/^GHA-\d{9}-\d$/',
            'ghana_card_file' => 'required|file|mimes:jpeg,png,jpg,gif,webp,pdf|max:5120',
            'passport_file' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,pdf|max:5120',
            
            // Guarantor Information
            'guarantor' => 'required|string|max:255',
            'guarantor_phone' => 'required|string|max:20',
            'g_ghana_card_no' => 'required|string|regex:/^GHA-\d{9}-\d$/',
            'guarantor_ghana_card_file' => 'required|file|mimes:jpeg,png,jpg,gif,webp,pdf|max:5120',
            'guarantor_passport_file' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'relationship' => 'required|in:family,employer,friend,colleague',
            'second_guarantor' => 'required|string|max:255',
            'second_guarantor_phone' => 'required|string|max:20',
            'second_g_ghana_card_no' => 'required|string|regex:/^GHA-\d{9}-\d$/',
            'second_guarantor_ghana_card_file' => 'required|file|mimes:jpeg,png,jpg,gif,webp,pdf|max:5120',
            'second_guarantor_passport_file' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'second_relationship' => 'required|in:family,employer,friend,colleague',
        ]);

        // Forward the original request to preserve uploaded files and use the shared payment initiation logic.
        return app(\App\Http\Controllers\PaymentController::class)
            ->initiateDriverPayment($request);
    }

    /**
     * Display the specified driver.
     */
    public function show(Driver $driver)
    {
        abort(404);
    }

    /**
     * Show the form for editing the driver profile.
     */
    public function edit()
    {
        $driver = Auth::user()->driver;
        if (!$driver) {
            return redirect()->route('drivers.create')->with('info', 'Please create a driver profile first.');
        }

        return view('drivers.edit', compact('driver'));
    }

    /**
     * Update the specified driver profile in database.
     */
    public function update(Request $request)
    {
        $driver = Auth::user()->driver;
        if (!$driver) {
            abort(404, 'Driver profile not found.');
        }

        $requiresReview = $driver->approval_status === 'approved' && !Auth::user()->isAdmin();
        if ($requiresReview && $driver->pending_application_changes) {
            return redirect()->route('drivers.edit')
                ->withErrors(['application' => 'Your driver application changes are awaiting admin review.']);
        }

        $validated = $request->validate([
            'license_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('drivers', 'license_number')->ignore($driver->id),
                function ($attribute, $value, $fail) {
                    if (Vehicle::where('license_plate', $value)->exists()) {
                        $fail('The driver license number cannot match an existing vehicle license plate.');
                    }
                },
            ],
            'license_expiry' => 'required|date|after:today',
            'license_class' => 'required|in:A,B,C,D,E',
            'experience_years' => 'required|string|max:50',
            'status' => 'required|in:available,unavailable,on_duty',
            'bio' => 'nullable|string|max:1000',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            if (!$requiresReview && $driver->profile_picture && Storage::disk('public')->exists($driver->profile_picture)) {
                Storage::disk('public')->delete($driver->profile_picture);
            }
            $file = $request->file('profile_picture');
            $disk = $requiresReview ? 'local' : 'public';
            $path = $file->store('drivers/profiles', $disk);
            $validated['profile_picture'] = $requiresReview ? 'private:' . $path : $path;
        }

        if ($requiresReview) {
            $changes = array_filter(
                $validated,
                function ($value, $field) use ($driver) {
                    $current = $driver->getAttribute($field);
                    if ($current instanceof \DateTimeInterface) {
                        $current = $current->format('Y-m-d');
                    }

                    return $value != $current;
                },
                ARRAY_FILTER_USE_BOTH
            );

            if ($changes === []) {
                return redirect()->route('drivers.profile')
                    ->with('success', 'No driver application changes were submitted.');
            }

            $driver->pending_application_changes = $changes;
            $driver->save();

            return redirect()->route('drivers.profile')
                ->with('success', 'Your driver application changes have been submitted for admin review. Your approved details remain active until approval.');
        }

        $driver->update($validated);

        return redirect()->route('drivers.profile')
            ->with('success', 'Driver profile updated successfully!');
    }

    /**
     * Remove the specified driver profile from database.
     */
    public function destroy()
    {
        abort(403, 'Driver profiles cannot be deleted after creation.');
    }

    /**
     * Get all available drivers (API endpoint).
     */
    public function getAvailable()
    {
        abort(404);
    }

    /**
     * Update driver rating.
     */
    public function rateDriver(Request $request, Driver $driver)
    {
        $validated = $request->validate([
            'rating' => 'required|numeric|min:1|max:5',
        ]);

        $driver->updateRating($validated['rating']);

        return redirect()->back()
            ->with('success', 'Driver rated successfully!');
    }

    /**
     * Search drivers.
     */
    public function search(Request $request)
    {
        abort(404);
    }
}
