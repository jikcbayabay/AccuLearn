<?php

namespace App\Models;

use App\Enums\MasteryLevel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MasteryRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'competency_id',
        'mastery_score',
        'mastery_level',
        'attempt_count',
    ];

    protected $casts = [
        'mastery_score' => 'decimal:2',
        'mastery_level' => MasteryLevel::class,
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
