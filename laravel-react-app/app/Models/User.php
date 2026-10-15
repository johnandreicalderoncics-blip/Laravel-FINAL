<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'username', 'email', 'password', 'user_role', 'phone_number', 'address', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    protected $primaryKey = 'user_id';

    // User role constants
    const ROLE_ADMIN = 'admin';
    const ROLE_EMPLOYEE = 'employee';
    const ROLE_CUSTOMER = 'customer';

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
            'is_active' => 'boolean',
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
     * Get the audit log entries created by the user.
     */
    public function logs(): HasMany
    {
        return $this->hasMany(Log::class, 'user_id', 'user_id');
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->user_role === self::ROLE_ADMIN;
    }

    /**
     * Check if user is employee
     */
    public function isEmployee(): bool
    {
        return $this->user_role === self::ROLE_EMPLOYEE;
    }

    /**
     * Check if user is customer
     */
    public function isCustomer(): bool
    {
        return $this->user_role === self::ROLE_CUSTOMER;
    }

    /**
     * Check if user can manage products (admin and employee)
     */
    public function canManageProducts(): bool
    {
        return in_array($this->user_role, [self::ROLE_ADMIN, self::ROLE_EMPLOYEE]);
    }

    /**
     * Check if user can view all tables (admin only)
     */
    public function canViewAllTables(): bool
    {
        return $this->user_role === self::ROLE_ADMIN;
    }
}
