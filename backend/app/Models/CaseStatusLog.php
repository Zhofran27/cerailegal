<?php

namespace App\Models;

use Database\Factories\CaseStatusLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaseStatusLog extends Model
{
    /** @use HasFactory<CaseStatusLogFactory> */
    use HasFactory;

    protected $fillable = [
        'case_id',
        'status',
        'notes',
        'changed_by_admin_id',
    ];

    /**
     * Get the case that this status log belongs to.
     */
    public function case(): BelongsTo
    {
        return $this->belongsTo(LegalCase::class, 'case_id');
    }

    /**
     * Get the admin user who changed the case status.
     */
    public function changedByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_admin_id');
    }
}

