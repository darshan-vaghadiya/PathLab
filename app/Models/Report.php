<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use App\Traits\HasUlid;

class Report extends Model
{
    use HasFactory;
    use HasUlid;

    protected $fillable = [
        'order_id',
        'report_number',
        'approved_by',
        'approved_at',
        'pdf_path',
        'notes',
        'public_token',
        'token_expires_at',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'token_expires_at' => 'datetime',
        ];
    }

    // ── Boot ─────────────────────────────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Report $report) {
            if (empty($report->report_number)) {
                $today = now()->format('Ymd');
                $prefix = 'RPT-' . $today . '-';

                $last = static::query()
                    ->where('report_number', 'like', $prefix . '%')
                    ->orderByRaw('CAST(SUBSTRING(report_number, ' . (strlen($prefix) + 1) . ') AS UNSIGNED) DESC')
                    ->value('report_number');

                $nextNumber = $last ? (int) substr($last, strlen($prefix)) + 1 : 1;

                $report->report_number = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
            }

            if (empty($report->public_token)) {
                $report->public_token = Str::random(64);
                $report->token_expires_at = now()->addDays(90);
            }
        });
    }

    // ── Helpers ────────────────────────────────────────────

    public function getPublicUrl(): string
    {
        return url("/report/{$this->public_token}");
    }

    public function isTokenValid(): bool
    {
        return $this->token_expires_at === null || $this->token_expires_at->isFuture();
    }

    // ── Relationships ────────────────────────────────────────

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
