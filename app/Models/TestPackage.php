<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasUlid;

class TestPackage extends Model
{
    use HasFactory;
    use HasUlid;

    protected $fillable = [
        'name',
        'description',
        'price',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'price' => 'decimal:2',
        ];
    }

    // ── Relationships ────────────────────────────────────────

    public function items(): HasMany
    {
        return $this->hasMany(TestPackageItem::class);
    }

    public function tests(): BelongsToMany
    {
        return $this->belongsToMany(Test::class, 'test_package_items');
    }
}
