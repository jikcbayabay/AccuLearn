<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningTrack extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'competency_id',
        'lp',
        'prerequisite_status',
    ];

    protected $casts = [
        'lp'                  => 'integer',
        'prerequisite_status' => 'boolean',
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
