<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'make',
        'model',
        'year',
        'license_plate',
        'registration_number',
        'color',
        'vin',
        'vehicle_type',
        'condition',
        'fuel_type',
        'transmission',
        'engine_size',
        'status',
        'availability_status',
        'approval_status',
        'payment_status',
        'payment_reference',
        'rejection_reason',
        'pending_application_changes',
        'suspension_reason',
        'description',
        'image_url',
        'capacity',
        'number_of_seats',
        'mileage',
        'registration_date',
        'insurance_expiry',
        'inspection_expiry',
        'registration_expiry',
        'registration_document',
        'insurance_document',
        'roadworthiness_certificate',
        'tracking_status',
        'tracking_location',
        'tracking_notes',
        'tracking_updated_at',
        'latitude',
        'longitude',
        'last_location_update',
    ];

    protected $casts = [
        'year' => 'integer',
        'capacity' => 'integer',
        'registration_date' => 'date',
        'registration_expiry' => 'date',
        'insurance_expiry' => 'date',
        'inspection_expiry' => 'date',
        'image_url' => 'array',
        'pending_application_changes' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'last_location_update' => 'datetime',
        'tracking_updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the vehicle.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all available vehicles (admin-set status).
     */
    public static function available()
    {
        return self::where('availability_status', 'available');
    }

    /**
     * Scope a query to only vehicles with 'approved' approval status.
     */
    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'approved');
    }

    /**
     * Get the full vehicle name.
     */
    public function getFullNameAttribute()
    {
        return "{$this->year} {$this->make} {$this->model}";
    }

    public function imagePaths(): array
    {
        $value = $this->getAttributeFromArray('image_url');

        if (is_array($value)) {
            return array_values(array_filter($value, 'is_string'));
        }

        if (!is_string($value) || trim($value) === '') {
            return [];
        }

        $decoded = json_decode($value, true);
        if (is_array($decoded)) {
            return array_values(array_filter($decoded, 'is_string'));
        }

        return [is_string($decoded) ? $decoded : $value];
    }

    /**
     * Get the vehicle condition.
     */
    public function getConditionLabelAttribute()
    {
        return ucfirst($this->condition ?? 'Not specified');
    }

    /**
     * Backward-compatible accessor for legacy inspection certificate key.
     */
    public function getInspectionCertificateAttribute()
    {
        return $this->attributes['roadworthiness_certificate'] ?? null;
    }
}
