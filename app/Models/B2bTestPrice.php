<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasUlid;

class B2bTestPrice extends Model
{
    use HasFactory;
    use HasUlid;

    protected $fillable = [
        'b2b_client_id',
        'test_id',
        'price',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    // ── Relationships ────────────────────────────────────────

    public function client(): BelongsTo
    {
        return $this->belongsTo(B2bClient::class, 'b2b_client_id');
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }
}
