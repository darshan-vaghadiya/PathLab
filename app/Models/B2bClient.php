<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasUlid;

class B2bClient extends Model
{
    use HasFactory;
    use HasUlid;

    protected $fillable = [
        'name',
        'contact_person',
        'phone',
        'email',
        'address',
        'payment_terms',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // ── Relationships ────────────────────────────────────────

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function testPrices(): HasMany
    {
        return $this->hasMany(B2bTestPrice::class);
    }

    public function packagePrices(): HasMany
    {
        return $this->hasMany(B2bPackagePrice::class);
    }
}
