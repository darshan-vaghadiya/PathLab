<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderTestResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_test_id',
        'test_parameter_id',
        'result_value',
    ];

    // ── Relationships ────────────────────────────────────────

    public function orderTest(): BelongsTo
    {
        return $this->belongsTo(OrderTest::class);
    }

    public function parameter(): BelongsTo
    {
        return $this->belongsTo(TestParameter::class, 'test_parameter_id');
    }

    // ── Helpers ────────────────────────────────────────────

    /**
     * Check if the result value is abnormal based on the applicable range.
     *
     * @param  string|null  $gender  e.g. 'male', 'female'
     * @param  int|null     $age     age in years
     * @return bool|null    true = abnormal, false = normal, null = cannot determine
     */
    public function isAbnormal(?string $gender = null, ?int $age = null): ?bool
    {
        if (! is_numeric($this->result_value)) {
            return null;
        }

        $query = $this->parameter->ranges();

        // Filter by gender/range_group if provided
        if ($gender) {
            $query->where(function ($q) use ($gender) {
                $q->where('range_group', $gender)
                  ->orWhereNull('range_group');
            });
        }

        // Filter by age if provided
        if (! is_null($age)) {
            $query->where(function ($q) use ($age) {
                $q->where(function ($inner) use ($age) {
                    $inner->where('age_min', '<=', $age)
                          ->where('age_max', '>=', $age);
                })->orWhere(function ($inner) {
                    $inner->whereNull('age_min')
                          ->whereNull('age_max');
                });
            });
        }

        $range = $query->first();

        if (! $range || (is_null($range->min_value) && is_null($range->max_value))) {
            return null;
        }

        $value = (float) $this->result_value;

        if (! is_null($range->min_value) && $value < (float) $range->min_value) {
            return true;
        }

        if (! is_null($range->max_value) && $value > (float) $range->max_value) {
            return true;
        }

        return false;
    }
}
