<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearnerProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'learning_pace',
        'avg_mastery',
        'confidence_alignment',
        'attempts_count',
        'lessons_completed',
        'open_gaps',
        'preferred_modality',
        'last_recomputed_at',
    ];

    protected $casts = [
        'avg_mastery'          => 'decimal:2',
        'confidence_alignment' => 'decimal:4',
        'attempts_count'       => 'integer',
        'lessons_completed'    => 'integer',
        'open_gaps'            => 'integer',
        'last_recomputed_at'   => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
