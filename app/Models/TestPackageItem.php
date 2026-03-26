<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestPackageItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_package_id',
        'test_id',
    ];

    // ── Relationships ────────────────────────────────────────

    public function package(): BelongsTo
    {
        return $this->belongsTo(TestPackage::class, 'test_package_id');
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }
}
