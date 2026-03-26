<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasUlid;

class Test extends Model
{
    use HasFactory;
    use HasUlid;

    protected $fillable = [
        'test_category_id',
        'name',
        'short_name',
        'description',
        'report_type',
        'price',
        'method',
        'sample_type',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'price' => 'decimal:2',
        ];
    }

    // ── Relationships ────────────────────────────────────────

    public function category(): BelongsTo
    {
        return $this->belongsTo(TestCategory::class, 'test_category_id');
    }

    public function packageItems(): HasMany
    {
        return $this->hasMany(TestPackageItem::class);
    }

    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(TestPackage::class, 'test_package_items');
    }

    public function orderTests(): HasMany
    {
        return $this->hasMany(OrderTest::class);
    }

    public function b2bPrices(): HasMany
    {
        return $this->hasMany(B2bTestPrice::class);
    }

    public function parameters(): HasMany
    {
        return $this->hasMany(TestParameter::class)->orderBy('sort_order');
    }
}
