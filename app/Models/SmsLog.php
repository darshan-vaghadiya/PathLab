<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasUlid;

class SmsLog extends Model
{
    use HasFactory;
    use HasUlid;

    protected $fillable = [
        'phone',
        'message',
        'type',
        'status',
        'gateway_response',
        'report_id',
        'order_id',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    // ── Relationships ────────────────────────────────────────

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
