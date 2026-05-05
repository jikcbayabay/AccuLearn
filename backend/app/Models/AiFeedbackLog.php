<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiFeedbackLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'competency_id',
        'feedback_text',
        'error_pattern',
        'lp_assigned',
        'gi_score',
        'cmi_score',
    ];

    protected $casts = [
        'gi_score'  => 'decimal:4',
        'cmi_score' => 'decimal:4',
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
