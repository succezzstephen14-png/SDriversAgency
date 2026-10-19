<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Concerns\HasAlphanumericId;
use App\Models\Role;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasAlphanumericId;

    public $incrementing = false;

    protected $keyType = 'string';

    protected function getIdPrefix(): string
    {
        return 'USR';
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // Keep for backward compatibility
        'phone',
        'address',
        'onboarding_completed',
        'onboarded_at',
        'tracking_location',
        'latitude',
        'longitude',
        'last_location_update',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'last_location_update' => 'datetime',
        ];
    }

    /**
     * Ensure the default system and CMS admin accounts exist.
     */
    public static function ensureDefaultAdminAccounts(): void
    {
        $accounts = [
            config('admin.system.email') => [
                'name' => 'System Administrator',
                'password' => config('admin.system.password'),
                'role' => 'admin',
                'legacy_emails' => ['system.admin@dashboard.local'],
            ],
            config('admin.cms.email') => [
                'name' => 'CMS Administrator',
                'password' => config('admin.cms.password'),
                'role' => 'cms_admin',
                'legacy_emails' => ['site.admin@dashboard.local'],
            ],
        ];

        foreach ($accounts as $email => $data) {
            if (! is_string($data['password']) || $data['password'] === '') {
                throw new \RuntimeException("Missing seeding password for admin account {$email}. Set the corresponding environment variable.");
            }

            $user = User::where('email', $email)->first();
            $legacyUser = User::whereIn('email', $data['legacy_emails'])->first();

            if ($user && $legacyUser && $user->isNot($legacyUser)) {
                throw new \RuntimeException("Both the configured and legacy admin accounts exist for {$email}; resolve the duplicate accounts before seeding.");
            }

            $user ??= $legacyUser ?? new User();

            $user->fill([
                'name' => $data['name'],
                'email' => $email,
                'password' => Hash::make($data['password']),
                'role' => $data['role'],
            ])->save();
        }
    }

    /**
     * Get the vehicles associated with the user.
     */
    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }

    /**
     * Get the driver profile associated with the user.
     */
    public function driver()
    {
        return $this->hasOne(Driver::class);
    }

    /**
     * Get the recruitment posts by the user.
     */
    public function recruitments()
    {
        return $this->hasMany(Recruitment::class);
    }

    /**
     * Get all bookings made by the user.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get all payments made by the user.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get all alert notifications for the user.
     */
    public function alerts()
    {
        return $this->hasMany(Notification::class)->latest();
    }

    /**
     * Get the roles that belong to the user.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    /**
     * Check if the user has a specific role.
     */
    public function hasRole(string|array $roles): bool
    {
        if (is_string($roles)) {
            return $this->roles()->where('name', $roles)->exists();
        }

        return $this->roles()->whereIn('name', $roles)->exists();
    }

    /**
     * Check if the user has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        // Super admin has all permissions
        if ($this->hasRole('super_admin')) {
            return true;
        }

        // Check through roles
        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permission) {
                $query->where('name', $permission);
            })
            ->exists();
    }

    /**
     * Check if the user has any of the given permissions.
     */
    public function hasAnyPermission(array $permissions): bool
    {
        if ($this->hasRole('super_admin')) {
            return true;
        }

        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permissions) {
                $query->whereIn('name', $permissions);
            })
            ->exists();
    }

    /**
     * Check if the user has all of the given permissions.
     */
    public function hasAllPermissions(array $permissions): bool
    {
        if ($this->hasRole('super_admin')) {
            return true;
        }

        $userPermissions = $this->roles()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->pluck('name')
            ->unique()
            ->toArray();

        return count(array_intersect($permissions, $userPermissions)) === count($permissions);
    }

    /**
     * Assign a role to the user.
     */
    public function assignRole(string|Role $role): void
    {
        $roleId = $role instanceof Role ? $role->id : Role::where('name', $role)->first()?->id;

        if ($roleId && !$this->roles()->where('role_id', $roleId)->exists()) {
            $this->roles()->attach($roleId);
        }
    }

    /**
     * Remove a role from the user.
     */
    public function revokeRole(string|Role $role): void
    {
        $roleId = $role instanceof Role ? $role->id : Role::where('name', $role)->first()?->id;

        if ($roleId) {
            $this->roles()->detach($roleId);
        }
    }

    /**
     * Sync roles for the user.
     */
    public function syncRoles(array $roles): void
    {
        $roleIds = collect($roles)->map(function ($role) {
            return $role instanceof Role ? $role->id : Role::where('name', $role)->first()?->id;
        })->filter()->toArray();

        $this->roles()->sync($roleIds);
    }

    /**
     * Check if the user is an admin (backward compatibility + new role system).
     */
    public function isAdmin(): bool
    {
        // Check new role system first
        if ($this->hasRole(['super_admin', 'admin'])) {
            return true;
        }

        // Fallback to old role column for backward compatibility
        return in_array($this->role, ['admin', 'cms_admin']);
    }

    /**
     * Check if the user is a CMS admin (backward compatibility).
     */
    public function isCmsAdmin(): bool
    {
        // Check new role system first
        if ($this->hasRole(['super_admin', 'admin', 'editor', 'moderator'])) {
            return true;
        }

        // Fallback to old role column
        return $this->role === 'cms_admin';
    }

}
