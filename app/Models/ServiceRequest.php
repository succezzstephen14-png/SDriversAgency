<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    use HasFactory;

    public const DRIVER_SERVICES = [
        'long_journey_driver' => 'Long journey driver',
        'personal_driver' => 'Personal driver',
        'daily_sales_driver' => 'Daily sales driver',
    ];

    public const VEHICLE_SERVICES = [
        'daily_sales' => 'Daily sales',
        'work_and_pay' => 'Work and pay',
        'comfi_troski' => 'COMFI - TROSKI',
        'contract_drivers' => 'Contract drivers',
    ];

    protected $fillable = [
        'user_id',
        'request_type',
        'service_type',
        'vehicle_type',
        'location',
        'preferred_start_date',
        'duration',
        'details',
        'status',
        'assigned_driver_id',
        'assigned_vehicle_id',
        'quoted_amount',
        'admin_message',
        'payment_reference',
        'reviewed_at',
        'paid_at',
    ];

    protected $casts = [
        'preferred_start_date' => 'date',
        'reviewed_at' => 'datetime',
        'paid_at' => 'datetime',
        'quoted_amount' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignedDriver()
    {
        return $this->belongsTo(Driver::class, 'assigned_driver_id');
    }

    public function assignedVehicle()
    {
        return $this->belongsTo(Vehicle::class, 'assigned_vehicle_id');
    }

    public function getServiceLabelAttribute(): string
    {
        $services = $this->request_type === 'driver'
            ? self::DRIVER_SERVICES
            : self::VEHICLE_SERVICES;

        return $services[$this->service_type] ?? ucfirst(str_replace('_', ' ', $this->service_type));
    }
}
