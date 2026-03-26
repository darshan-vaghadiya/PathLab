<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasUlid;

class Patient extends Model
{
    use HasFactory;
    use HasUlid;

    protected $fillable = [
        'patient_id',
        'name',
        'age',
        'age_unit',
        'gender',
        'phone',
        'email',
        'address',
    ];

    // ── Boot ─────────────────────────────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Patient $patient) {
            if (empty($patient->patient_id)) {
                $last = static::query()
                    ->orderByRaw('CAST(SUBSTRING(patient_id, 4) AS UNSIGNED) DESC')
                    ->value('patient_id');

                $nextNumber = $last ? (int) substr($last, 3) + 1 : 1;

                $patient->patient_id = 'PL-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    // ── Relationships ────────────────────────────────────────

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
