<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasUlid;

class ResultTemplate extends Model
{
    use HasFactory;
    use HasUlid;

    protected $fillable = [
        'test_parameter_id',
        'name',
        'content',
    ];

    // ── Relationships ────────────────────────────────────────

    public function parameter(): BelongsTo
    {
        return $this->belongsTo(TestParameter::class, 'test_parameter_id');
    }
}
