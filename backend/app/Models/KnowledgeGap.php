<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KnowledgeGap extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'competency_id',
        'gap_type',
        'detail',
        'occurrences',
        'status',
        'last_detected_at',
        'resolved_at',
    ];

    protected $casts = [
        'occurrences'      => 'integer',
        'last_detected_at' => 'datetime',
        'resolved_at'      => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function competency(): BelongsTo
    {
        return $this->belongsTo(Competency::class);
    }
}
