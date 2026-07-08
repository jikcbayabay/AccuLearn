<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_STUDENT = 'student';
    public const ROLE_TEACHER = 'teacher';
    public const ROLE_ADMIN   = 'admin';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'moodle_user_id',
        'active',
        'section',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'role'              => UserRole::class,
        'active'            => 'boolean',
    ];

    public function masteryRecords(): HasMany
    {
        return $this->hasMany(MasteryRecord::class);
    }

    public function aiFeedbackLogs(): HasMany
    {
        return $this->hasMany(AiFeedbackLog::class);
    }

    public function learningTracks(): HasMany
    {
        return $this->hasMany(LearningTrack::class);
    }

    public function usageLogs(): HasMany
    {
        return $this->hasMany(UsageLog::class);
    }
}
