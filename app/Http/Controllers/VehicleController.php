<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
    /**
     * Display a listing of vehicles.
     */
    public function index()
    {
        $vehicles = Vehicle::with('user')
            ->where('status', 'available')
            ->where('approval_status', 'approved')
            ->paginate(12);
        return view('vehicles.index', compact('vehicles'));
    }

    /**
     * Show user's approved vehicles.
     */
    public function myVehicles()
    {
        $vehicles = Vehicle::where('user_id', Auth::id())
            ->where('approval_status', 'approved')
            ->paginate(10);

        return view('vehicles.my-vehicles', compact('vehicles'));
    }

    /**
     * Show user's pending vehicles.
     */
    public function myPendingVehicles()
    {
        $vehicles = Vehicle::where('user_id', Auth::id())
            ->where('approval_status', 'pending')
            ->paginate(10);

        return view('vehicles.my-pending-vehicles', compact('vehicles'));
    }

    /**
     * Show the form for creating a new vehicle.
     */
    public function create()
    {
        return view('vehicles.create');
    }

    /**
     * Store a newly created vehicle in database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Basic Information
            'make' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'year' => 'required|integer|min:1900|max:' . date('Y'),
            'vehicle_type' => 'required|in:car,van,truck,motorcycle',
            'color' => 'nullable|string|max:50',
            
            // License & Registration
            'license_plate' => [
                'required',
                'string',
                'max:20',
                Rule::unique('vehicles', 'license_plate'),
                function ($attribute, $value, $fail) {
                    // Prevent a vehicle license plate matching any driver license number
                    if (Driver::where('license_number', $value)->exists()) {
                        $fail('The vehicle license plate cannot match an existing driver license number.');
                    }
                    // Accept common Ghana formats such as "2301-GR1" and "AS 2567-12".
                    if (!preg_match('/^(?:\d{4}-[A-Z]{2}\d|[A-Z]{2}\s\d{4}-\d{2})$/i', $value)) {
                        $fail('Use a valid Ghana plate format, e.g., "2301-GR1" or "AS 2567-12".');
                    }
                },
            ],
            'registration_number' => 'nullable|string|max:100',
            'vin' => ['nullable', 'string', 'size:17', 'regex:/^[A-HJ-NPR-Z0-9]{17}$/', Rule::unique('vehicles', 'vin')],
            
            // Technical Specifications
            'fuel_type' => 'nullable|in:petrol,diesel,hybrid,electric,gas',
            'transmission' => 'nullable|in:manual,automatic',
            'engine_size' => 'nullable|string|max:20',
            'capacity' => 'nullable|integer|min:1|max:100',
            'number_of_seats' => 'nullable|integer|min:1|max:100',
            'mileage' => 'nullable|integer|min:0',
            
            // Vehicle Condition (Owner-provided)
            'condition' => 'required|in:excellent,very_good,good,fair,needs_maintenance',
            
            // Documentation & Dates (Required)
            'registration_date' => 'required|date',
            'registration_expiry' => 'required|date',
            'insurance_expiry' => 'required|date',
            'inspection_expiry' => 'required|date',
            'registration_document' => 'required|file|mimes:jpeg,png,jpg,gif,webp,pdf|max:5120',
            'insurance_document' => 'required|file|mimes:jpeg,png,jpg,gif,webp,pdf|max:5120',
            'roadworthiness_certificate' => 'required|file|mimes:jpeg,png,jpg,gif,webp,pdf|max:5120',
            
            // Images & Description
            'image_url' => 'required|array|min:1',
            'image_url.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'description' => 'nullable|string',
        ]);

        // Forward the original request (including uploaded files) to the payment initiation flow.
        return app(\App\Http\Controllers\PaymentController::class)
            ->initiateVehiclePayment($request);
    }

    /**
     * Display the specified vehicle.
     */
    public function show(Vehicle $vehicle)
    {
        abort_unless($vehicle->approval_status === 'approved', 404);

        return view('vehicles.show', compact('vehicle'));
    }

    public function showImage(Vehicle $vehicle, int $index)
    {
        if ($vehicle->approval_status !== 'approved') {
            abort_unless(
                Auth::check() &&
                ((string) Auth::id() === (string) $vehicle->user_id || Auth::user()->isAdmin()),
                404
            );
        }

        $imagePath = $vehicle->imagePaths()[$index] ?? null;
        abort_unless($imagePath && Storage::disk('public')->exists($imagePath), 404);

        $mimeType = Storage::disk('public')->mimeType($imagePath);
        abort_unless(is_string($mimeType) && str_starts_with($mimeType, 'image/'), 404);

        return response()->file(Storage::disk('public')->path($imagePath), [
            'Content-Disposition' => 'inline',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * Show the form for editing the vehicle.
     */
    public function edit(Vehicle $vehicle)
    {
        if (Auth::id() !== $vehicle->user_id && !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        return view('vehicles.edit', compact('vehicle'));
    }

    /**
     * Update the specified vehicle in database.
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        if (Auth::id() !== $vehicle->user_id && !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $requiresReview = Auth::id() === $vehicle->user_id
            && !Auth::user()->isAdmin()
            && $vehicle->approval_status === 'approved';
        if ($requiresReview && $vehicle->pending_application_changes) {
            return redirect()->route('vehicles.edit', $vehicle)
                ->withErrors(['application' => 'Your vehicle application changes are awaiting admin review.']);
        }

        $validated = $request->validate([
            // Basic Information
            'make' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'year' => 'required|integer|min:1900|max:' . date('Y'),
            'vehicle_type' => 'required|in:car,van,truck,motorcycle',
            'color' => 'nullable|string|max:50',
            
            // License & Registration
            'license_plate' => [
                'required',
                'string',
                'max:20',
                Rule::unique('vehicles', 'license_plate')->ignore($vehicle->id),
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
            'vin' => ['nullable', 'string', 'size:17', 'regex:/^[A-HJ-NPR-Z0-9]{17}$/', Rule::unique('vehicles', 'vin')->ignore($vehicle->id)],
            
            // Technical Specifications
            'fuel_type' => 'nullable|in:petrol,diesel,hybrid,electric,gas',
            'transmission' => 'nullable|in:manual,automatic',
            'engine_size' => 'nullable|string|max:20',
            'capacity' => 'nullable|integer|min:1|max:100',
            'number_of_seats' => 'nullable|integer|min:1|max:100',
            'mileage' => 'nullable|integer|min:0',
            
            // Documentation & Dates
            'status' => 'required|in:available,unavailable,maintenance,on_duty',
            'registration_date' => 'required|date',
            'registration_expiry' => 'required|date',
            'insurance_expiry' => 'required|date',
            'inspection_expiry' => 'required|date',
            'registration_document' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,pdf|max:5120',
            'insurance_document' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,pdf|max:5120',
            'roadworthiness_certificate' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,pdf|max:5120',
            
            // Images & Description
            'image_url' => empty($vehicle->imagePaths()) ? 'required|array|min:1' : 'nullable|array|min:1',
            'image_url.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'description' => 'nullable|string',
        ]);

        // Handle file uploads
        $fileFields = ['registration_document', 'insurance_document', 'roadworthiness_certificate'];
        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $disk = $requiresReview ? 'local' : 'public';
                $path = $request->file($field)->store('documents', $disk);
                $validated[$field] = $requiresReview ? 'private:' . $path : $path;
            }
        }

        // Handle multiple image uploads
        if ($request->hasFile('image_url')) {
            $imagePaths = [];
            foreach ($request->file('image_url') as $file) {
                $disk = $requiresReview ? 'local' : 'public';
                $path = $file->store('documents', $disk);
                $imagePaths[] = $requiresReview ? 'private:' . $path : $path;
            }
            $validated['image_url'] = $imagePaths;
        }

        if ($requiresReview) {
            $changes = array_filter(
                $validated,
                function ($value, $field) use ($vehicle) {
                    $current = $vehicle->getAttribute($field);
                    if ($current instanceof \DateTimeInterface) {
                        $current = $current->format('Y-m-d');
                    }

                    return $value != $current;
                },
                ARRAY_FILTER_USE_BOTH
            );

            if ($changes === []) {
                return redirect()->route('vehicles.show', $vehicle)
                    ->with('success', 'No vehicle application changes were submitted.');
            }

            $vehicle->pending_application_changes = $changes;
            $vehicle->save();

            return redirect()->route('vehicles.show', $vehicle)
                ->with('success', 'Your vehicle application changes have been submitted for admin review. Your approved details remain active until approval.');
        }

        $vehicle->update($validated);

        return redirect()->route('vehicles.show', $vehicle)
            ->with('success', 'Vehicle updated successfully!');
    }

    /**
     * Remove the specified vehicle from database.
     */
    public function destroy(Vehicle $vehicle)
    {
        if (Auth::id() !== $vehicle->user_id && !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $vehicle->delete();

        return redirect()->route('vehicles.my-vehicles')
            ->with('success', 'Vehicle deleted successfully!');
    }

    /**
     * Get all available vehicles (API endpoint).
     */
    public function getAvailable()
    {
        $vehicles = Vehicle::where('status', 'available')
            ->where('approval_status', 'approved')
            ->with('user')
            ->get();
        return response()->json($vehicles);
    }

    /**
     * Search vehicles by type.
     */
    public function search(Request $request)
    {
        $query = Vehicle::where('approval_status', 'approved');

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', 'available');
        }

        if ($request->has('type') && $request->type) {
            $query->where('vehicle_type', $request->type);
        }

        if ($request->has('make') && $request->make) {
            $query->where('make', 'like', '%' . $request->make . '%');
        }

        $vehicles = $query->with('user')->paginate(12);
        return view('vehicles.index', compact('vehicles'));
    }
}
