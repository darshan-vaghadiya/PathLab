<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasUlid;

class ParameterRange extends Model
{
    use HasFactory;
    use HasUlid;

    protected $fillable = [
        'test_parameter_id',
        'range_group',
        'age_min',
        'age_max',
        'min_value',
        'max_value',
        'text_value',
    ];

    protected function casts(): array
    {
        return [
            'min_value' => 'decimal:2',
            'max_value' => 'decimal:2',
        ];
    }

    // ── Relationships ────────────────────────────────────────

    public function parameter(): BelongsTo
    {
        return $this->belongsTo(TestParameter::class, 'test_parameter_id');
    }

    // ── Helpers ────────────────────────────────────────────

    /**
     * Return a formatted display string for this range.
     * Numeric ranges: "13.00 - 17.00", single-bound: "> 5.00" / "< 10.00"
     * Text ranges: returns text_value as-is.
     */
    public function getDisplayRange(): string
    {
        if ($this->text_value) {
            return $this->text_value;
        }

        if (! is_null($this->min_value) && ! is_null($this->max_value)) {
            return "{$this->min_value} - {$this->max_value}";
        }

        if (! is_null($this->min_value)) {
            return "> {$this->min_value}";
        }

        if (! is_null($this->max_value)) {
            return "< {$this->max_value}";
        }

        return '';
    }
}
