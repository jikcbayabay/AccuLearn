<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'competency_id',
        'title',
        'type',
        'content_url',
        'vark_type',
    ];

    public function competency(): BelongsTo
    {
        return $this->belongsTo(Competency::class);
    }
}
