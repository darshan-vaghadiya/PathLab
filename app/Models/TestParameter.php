<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasUlid;

class TestParameter extends Model
{
    use HasFactory;
    use HasUlid;

    protected $fillable = [
        'test_id',
        'name',
        'field_type',
        'unit',
        'options',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'options' => 'array',
        ];
    }

    // ── Relationships ────────────────────────────────────────

    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }

    public function ranges(): HasMany
    {
        return $this->hasMany(ParameterRange::class);
    }

    public function orderTestResults(): HasMany
    {
        return $this->hasMany(OrderTestResult::class);
    }

    public function templates(): HasMany
    {
        return $this->hasMany(ResultTemplate::class);
    }
}
