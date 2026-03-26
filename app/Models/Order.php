<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Traits\HasUlid;
use App\Models\User;

class Order extends Model
{
    use HasFactory;
    use HasUlid;

    protected $fillable = [
        'order_number',
        'patient_id',
        'referring_doctor_id',
        'b2b_client_id',
        'source',
        'home_visit_address',
        'home_visit_charge',
        'total_amount',
        'discount',
        'net_amount',
        'payment_status',
        'payment_mode',
        'amount_paid',
        'notes',
        'created_by',
        'status',
        'cancelled_at',
        'cancelled_by',
        'cancellation_reason',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'discount' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'home_visit_charge' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'cancelled_at' => 'datetime',
        ];
    }

    // ── Boot ─────────────────────────────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Order $order) {
            if (empty($order->order_number)) {
                $today = now()->format('Ymd');
                $prefix = 'ORD-' . $today . '-';

                $last = static::query()
                    ->where('order_number', 'like', $prefix . '%')
                    ->orderByRaw('CAST(SUBSTRING(order_number, ' . (strlen($prefix) + 1) . ') AS UNSIGNED) DESC')
                    ->value('order_number');

                $nextNumber = $last ? (int) substr($last, strlen($prefix)) + 1 : 1;

                $order->order_number = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    // ── Relationships ────────────────────────────────────────

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function referringDoctor(): BelongsTo
    {
        return $this->belongsTo(ReferringDoctor::class);
    }

    public function b2bClient(): BelongsTo
    {
        return $this->belongsTo(B2bClient::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function orderTests(): HasMany
    {
        return $this->hasMany(OrderTest::class);
    }

    public function report(): HasOne
    {
        return $this->hasOne(Report::class);
    }

    public function cancelledByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    // ── Helpers ────────────────────────────────────────────────

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
}
