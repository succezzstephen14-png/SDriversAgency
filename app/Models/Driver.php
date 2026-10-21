<?php

namespace App\Models;

use App\Models\Concerns\HasAlphanumericId;
use App\Models\Concerns\HasApplicationId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Driver extends Model
{
    use HasFactory, HasAlphanumericId, HasApplicationId;

    public const VETTING_CHECKS = [
        'application_form' => 'Completed driver application form',
        'ghana_card_verified' => 'Copy of Ghana Card and identity verification',
        'license_verified' => "Valid driver's licence and licence verification",
        'passport_photo' => 'Recent passport-size photograph',
        'contact_details' => 'Current residential address and contact details',
        'references' => 'Two referees and completed reference checks',
        'employment' => 'Previous employment details and verification',
        'police_check' => 'Police criminal background check, with consent',
        'emergency_contact' => 'Emergency contact details',
        'code_of_conduct' => 'Signed driver code of conduct',
        'confidentiality' => 'Signed confidentiality and client-protection undertaking',
        'driving_assessment' => 'Practical driving assessment report',
        'screening_consent' => 'Signed consent and privacy notice for screening',
    ];

    public $incrementing = false;

    protected $keyType = 'string';

    protected function getIdPrefix(): string
    {
        return 'DRV';
    }

    protected function getApplicationIdPrefix(): string
    {
        return 'DRV-APP';
    }

    protected $fillable = [
        'application_id',
        'user_id',
        'dob',
        'license_number',
        'license_file',
        'license_expiry',
        'license_class',
        'ghana_card_no',
        'ghana_card_file',
        'passport_file',
        'experience_years',
        'guarantor',
        'guarantor_phone',
        'g_ghana_card_no',
        'guarantor_ghana_card_file',
        'guarantor_passport_file',
        'relationship',
        'second_guarantor',
        'second_guarantor_phone',
        'second_g_ghana_card_no',
        'second_guarantor_ghana_card_file',
        'second_guarantor_passport_file',
        'second_relationship',
        'emergency_person',
        'emergency_phone',
        'status',
        'approval_status',
        'approved_at',
        'rejection_reason',
        'vetting_checks',
        'pending_application_changes',
        'bio',
        'profile_picture',
        'rating',
        'total_trips',
        'payment_status',
        'payment_reference',
        'tracking_status',
        'tracking_location',
        'tracking_notes',
        'tracking_updated_at',
        'latitude',
        'longitude',
        'last_location_update',
    ];

    protected $casts = [
        'license_expiry' => 'date',
        'approved_at' => 'datetime',
        'vetting_checks' => 'array',
        'pending_application_changes' => 'array',
        'rating' => 'float',
        'total_trips' => 'integer',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'last_location_update' => 'datetime',
        'tracking_updated_at' => 'datetime',
    ];

    /**
     * Get the user that is the driver.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get profile picture URL with fallback.
     */
    public function getProfilePictureUrl(): ?string
    {
        $path = $this->profile_picture ?: $this->profile_image;

        if ($path && Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->url($path);
        }

        return null;
    }

    /**
     * Check if driver has a profile picture.
     */
    public function hasProfilePicture(): bool
    {
        return $this->getProfilePictureUrl() !== null;
    }

    /**
     * Get all available drivers.
     */
    public static function available()
    {
        return self::where('status', 'available');
    }

    /**
     * Scope a query to only approved drivers.
     */
    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'approved');
    }

    /**
     * Check if driver license is expired.
     */
    public function isLicenseExpired(): bool
    {
        return $this->license_expiry < now();
    }

    /**
     * Update driver rating.
     */
    public function updateRating($newRating)
    {
        $this->rating = ($this->rating + $newRating) / 2;
        $this->save();
    }
}
