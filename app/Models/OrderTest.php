<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasUlid;

class OrderTest extends Model
{
    use HasFactory;
    use HasUlid;

    protected $fillable = [
        'order_id',
        'test_id',
        'price',
        'status',
        'result_remarks',
        'sample_collected_at',
        'sample_collected_by',
        'result_entered_at',
        'result_entered_by',
        'approved_at',
        'approved_by',
        'rejected_at',
        'rejected_by',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'sample_collected_at' => 'datetime',
            'result_entered_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    // ── Relationships ────────────────────────────────────────

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }

    public function sampleCollector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sample_collected_by');
    }

    public function resultEnterer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'result_entered_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function results(): HasMany
    {
        return $this->hasMany(OrderTestResult::class);
    }

    // ── Helpers ────────────────────────────────────────────

    /**
     * Check if all active parameters for this test have results entered.
     */
    public function allResultsFilled(): bool
    {
        $activeParameterIds = $this->test->parameters()
            ->where('is_active', true)
            ->pluck('id');

        if ($activeParameterIds->isEmpty()) {
            return false;
        }

        $filledCount = $this->results()
            ->whereIn('test_parameter_id', $activeParameterIds)
            ->whereNotNull('result_value')
            ->where('result_value', '!=', '')
            ->count();

        return $filledCount >= $activeParameterIds->count();
    }
}
