<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasUlid;

class ReferringDoctor extends Model
{
    use HasFactory;
    use HasUlid;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'clinic_name',
        'address',
        'commission_type',
        'commission_value',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'commission_value' => 'decimal:2',
        ];
    }

    // ── Relationships ────────────────────────────────────────

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
