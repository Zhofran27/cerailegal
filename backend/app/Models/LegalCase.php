<?php

namespace App\Models;

use Database\Factories\LegalCaseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LegalCase extends Model
{
    /** @use HasFactory<LegalCaseFactory> */
    use HasFactory, HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cases';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'court_id',
        'case_number',
        'reason',
        'status',
        'applicant_detail',
    ];

    protected function casts(): array
    {
        return ['applicant_detail' => 'array'];
    }

    /**
     * Get the user that owns the case.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the court handling the case.
     */
    public function court(): BelongsTo
    {
        return $this->belongsTo(Court::class, 'court_id');
    }

    /**
     * Get the spouse details for the case (One-to-One).
     */
    public function spouseDetail(): HasOne
    {
        return $this->hasOne(SpouseDetail::class, 'case_id');
    }

    /**
     * Get the marriage details for the case (One-to-One).
     */
    public function marriageDetail(): HasOne
    {
        return $this->hasOne(MarriageDetail::class, 'case_id');
    }

    /**
     * Get the uploaded documents for the case (One-to-Many).
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'case_id');
    }

    /**
     * Get the status change logs for the case (One-to-Many).
     */
    public function statusLogs(): HasMany
    {
        return $this->hasMany(CaseStatusLog::class, 'case_id');
    }
}

