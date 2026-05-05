<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Competency extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_id',
        'title',
        'deped_code',
        'prerequisite_competency_id',
        'order',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function prerequisite(): BelongsTo
    {
        return $this->belongsTo(self::class, 'prerequisite_competency_id');
    }

    public function learningMaterials(): HasMany
    {
        return $this->hasMany(LearningMaterial::class);
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }

    public function masteryRecords(): HasMany
    {
        return $this->hasMany(MasteryRecord::class);
    }
}
