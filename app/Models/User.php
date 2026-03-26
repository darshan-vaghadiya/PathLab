<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\HasUlid;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;
    use HasUlid;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // ── Relationships ────────────────────────────────────────

    public function createdOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'created_by');
    }

    public function collectedSamples(): HasMany
    {
        return $this->hasMany(OrderTest::class, 'sample_collected_by');
    }

    public function enteredResults(): HasMany
    {
        return $this->hasMany(OrderTest::class, 'result_entered_by');
    }

    public function approvedTests(): HasMany
    {
        return $this->hasMany(OrderTest::class, 'approved_by');
    }

    public function approvedReports(): HasMany
    {
        return $this->hasMany(Report::class, 'approved_by');
    }

    // ── Helper Methods ───────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isReceptionist(): bool
    {
        return $this->role === 'receptionist';
    }

    public function isTechnician(): bool
    {
        return $this->role === 'technician';
    }

    public function isDoctor(): bool
    {
        return $this->role === 'doctor';
    }
}
