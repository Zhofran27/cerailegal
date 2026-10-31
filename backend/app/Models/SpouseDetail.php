<?php

namespace App\Models;

use Database\Factories\SpouseDetailFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpouseDetail extends Model
{
    /** @use HasFactory<SpouseDetailFactory> */
    use HasFactory;

    protected $fillable = [
        'case_id',
        'full_name',
        'nik',
        'birth_date',
        'birth_place',
        'gender',
        'religion',
        'occupation',
        'phone',
        'address',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
        ];
    }

    /**
     * Get the case that owns the spouse details (Inverse One-to-One).
     */
    public function case(): BelongsTo
    {
        return $this->belongsTo(LegalCase::class, 'case_id');
    }
}

