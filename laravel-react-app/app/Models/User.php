<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Validation\Rule;

#[Fillable(['name', 'email', 'password', 'role', 'status', 'phone_number', 'address'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $primaryKey = 'user_id';

    // User role constants
    const ROLE_ADMIN = 'admin';

    const ROLE_EMPLOYEE = 'employee';

    const ROLE_CUSTOMER = 'customer';

    const ROLES = [self::ROLE_ADMIN, self::ROLE_EMPLOYEE, self::ROLE_CUSTOMER];

    // User status constants
    const STATUS_ACTIVE = 'active';

    const STATUS_INACTIVE = 'inactive';

    const STATUSES = [self::STATUS_ACTIVE, self::STATUS_INACTIVE];

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
        ];
    }

    /**
     * Get the orders for the user.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'user_id', 'user_id');
    }

    /**
     * Get the products in the user's cart.
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class, 'user_id', 'user_id');
    }

    /**
     * Get the activity log entries for the user.
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'user_id', 'user_id');
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Check if user is employee
     */
    public function isEmployee(): bool
    {
        return $this->role === self::ROLE_EMPLOYEE;
    }

    /**
     * Check if user is customer
     */
    public function isCustomer(): bool
    {
        return $this->role === self::ROLE_CUSTOMER;
    }

    /**
     * Check if the account is allowed to log in
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if user can manage products (admin and employee)
     */
    public function canManageProducts(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_EMPLOYEE]);
    }

    /**
     * Check if user can view all tables (admin only)
     */
    public function canViewAllTables(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Validation rules for registration and for the admin user form. Pass
     * the user's id when updating so their own email is not seen as taken.
     * Only include role and status from an admin-only form.
     *
     * @return array<string, array<int, mixed>>
     */
    public static function rules(?int $ignoreUserId = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($ignoreUserId, 'user_id'),
            ],
            'password' => [$ignoreUserId === null ? 'required' : 'nullable', 'string', 'min:8'],
            'role' => ['sometimes', Rule::in(self::ROLES)],
            'status' => ['sometimes', Rule::in(self::STATUSES)],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
