<?php

namespace App\Models;

use Database\Factories\MarriageDetailFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarriageDetail extends Model
{
    /** @use HasFactory<MarriageDetailFactory> */
    use HasFactory;

    protected $fillable = [
        'case_id',
        'marriage_date',
        'marriage_certificate_number',
        'kua_or_civil_registry',
        'has_children',
        'children_count',
        'separation_date',
        'last_address',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'marriage_date' => 'date',
            'separation_date' => 'date',
            'has_children' => 'boolean',
            'children_count' => 'integer',
        ];
    }

    /**
     * Get the case that owns the marriage details (Inverse One-to-One).
     */
    public function case(): BelongsTo
    {
        return $this->belongsTo(LegalCase::class, 'case_id');
    }
}

