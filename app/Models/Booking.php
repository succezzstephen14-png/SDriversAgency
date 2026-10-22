<?php

namespace App\Models;

use App\Models\Concerns\HasAlphanumericId;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory, HasAlphanumericId;

    public $incrementing = false;

    protected $keyType = 'string';

    protected function getIdPrefix(): string
    {
        return 'BKNG';
    }

    protected $fillable = [
        'user_id',
        'driver_id',
        'vehicle_id',
        'recruitment_id',
        'booking_type',
        'status',
        'approval_status',
        'booking_date',
        'start_time',
        'end_time',
        'description',
        'location',
        'amount',
        'payment_status',
        'payment_reference',
        'payment_date',
        'application_role',
        'application_documents',
        'guest_name',
        'guest_email',
        'guest_phone',
        'tracking_status',
        'tracking_location',
        'tracking_notes',
        'tracking_updated_at',
        'rejection_reason',
        'approved_at',
    ];

    protected $casts = [
        'booking_date' => 'datetime',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'payment_date' => 'datetime',
        'approved_at' => 'datetime',
        'tracking_updated_at' => 'datetime',
        'amount' => 'decimal:2',
        'application_documents' => 'array',
    ];

    /**
     * Get the user that made the booking.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the driver associated with the booking.
     */
    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    /**
     * Get the vehicle associated with the booking.
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the recruitment associated with the booking.
     */
    public function recruitment()
    {
        return $this->belongsTo(Recruitment::class);
    }

    /**
     * Get the payment associated with the booking.
     */
    public function payment()
    {
        return $this->hasOne(Payment::class, 'reference', 'payment_reference');
    }

    public function assignmentActivities()
    {
        return $this->morphMany(AssignmentActivity::class, 'assignable')->latest();
    }

    /**
     * Scope to get pending bookings for admin approval.
     */
    public function scopePending($query)
    {
        return $query->where('approval_status', 'pending');
    }

    /**
     * Scope to get approved bookings.
     */
    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'approved');
    }

    /**
     * Check if booking is awaiting payment.
     */
    public function isPendingPayment(): bool
    {
        return $this->payment_status === 'pending' && $this->amount > 0;
    }
}
