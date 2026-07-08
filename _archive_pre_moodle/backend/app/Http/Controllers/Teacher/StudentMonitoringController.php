<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class StudentMonitoringController extends Controller
{
    public function index(): JsonResponse
    {
        $students = User::where('role', 'student')
            ->with([
                'masteryRecords:id,user_id,competency_id,mastery_score,mastery_level,attempt_count,updated_at',
                'learningTracks' => fn ($q) => $q->latest('updated_at'),
            ])
            ->get();

        $payload = $students->map(function ($s) {
            $records = $s->masteryRecords;
            $count   = $records->count();

            $overall = $count > 0 ? round($records->avg('mastery_score'), 2) : 0.0;

            $level = match (true) {
                $count === 0     => 'not_started',
                $overall >= 85   => 'mastered',
                $overall >= 75   => 'developing',
                default          => 'needs_improvement',
            };

            $mastered = $records->filter(function ($r) {
                $v = $r->mastery_level instanceof \BackedEnum ? $r->mastery_level->value : (string) $r->mastery_level;
                return $v === 'mastered';
            })->count();

            $latestLp = optional($s->learningTracks->first())->lp;

            $lastActive = $count > 0
                ? optional($records->sortByDesc('updated_at')->first())->updated_at
                : null;

            return [
                'id'                    => $s->id,
                'name'                  => $s->name,
                'email'                 => $s->email,
                'section'               => $s->section,
                'overall_mastery'       => $overall,
                'mastery_level'         => $level,
                'lp_assigned'           => $latestLp,
                'competencies_mastered' => $mastered,
                'last_active'           => $lastActive,
            ];
        })->values();

        return response()->json(['students' => $payload]);
    }

    public function show(int $id): JsonResponse
    {
        $student = User::where('role', 'student')
            ->where('id', $id)
            ->with([
                'masteryRecords.competency.module',
            ])
            ->first();

        if (! $student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        $records = $student->masteryRecords->map(function ($r) {
            $level = $r->mastery_level instanceof \BackedEnum ? $r->mastery_level->value : (string) $r->mastery_level;

            return [
                'id'             => $r->id,
                'competency_id'  => $r->competency_id,
                'mastery_score'  => (float) $r->mastery_score,
                'mastery_level'  => $level,
                'attempt_count'  => (int) $r->attempt_count,
                'updated_at'     => $r->updated_at,
                'competency'     => $r->competency ? [
                    'id'         => $r->competency->id,
                    'title'      => $r->competency->title,
                    'deped_code' => $r->competency->deped_code,
                ] : null,
                'module'         => $r->competency && $r->competency->module ? [
                    'id'    => $r->competency->module->id,
                    'title' => $r->competency->module->title,
                ] : null,
            ];
        });

        return response()->json([
            'student' => [
                'id'    => $student->id,
                'name'  => $student->name,
                'email' => $student->email,
                'role'  => $student->role instanceof \BackedEnum ? $student->role->value : $student->role,
            ],
            'mastery_records' => $records,
        ]);
    }
}
