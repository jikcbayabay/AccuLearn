<?php

namespace Tests\Feature;

use App\Models\AiFeedbackLog;
use App\Models\Competency;
use App\Models\LearnerProfile;
use App\Models\LessonCompletion;
use App\Models\MasteryRecord;
use App\Models\Module;
use App\Models\User;
use App\Services\Analytics\LearnerProfileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LearnerProfileServiceTest extends TestCase
{
    use RefreshDatabase;

    private LearnerProfileService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new LearnerProfileService();
    }

    private function competencies(int $count): array
    {
        $module = Module::create(['title' => 'M1', 'order' => 1]);

        $ids = [];
        for ($i = 1; $i <= $count; $i++) {
            $ids[] = Competency::create([
                'module_id' => $module->id,
                'title'     => "C{$i}",
                'order'     => $i,
            ])->id;
        }

        return $ids;
    }

    public function test_strong_low_effort_learner_is_fast_paced(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        [$c1, $c2] = $this->competencies(2);

        foreach ([[$c1, 90], [$c2, 95]] as [$cid, $score]) {
            MasteryRecord::create([
                'user_id' => $user->id, 'competency_id' => $cid,
                'mastery_score' => $score, 'mastery_level' => 'mastered', 'attempt_count' => 1,
            ]);
        }
        LessonCompletion::create(['user_id' => $user->id, 'competency_id' => $c1]);

        $profile = $this->service->recompute($user->id);

        $this->assertSame('fast', $profile->learning_pace);
        $this->assertEqualsWithDelta(92.5, (float) $profile->avg_mastery, 0.01);
        $this->assertSame(2, $profile->attempts_count);
        $this->assertSame(1, $profile->lessons_completed);
    }

    public function test_struggling_learner_is_slow_paced(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        [$c1] = $this->competencies(1);

        MasteryRecord::create([
            'user_id' => $user->id, 'competency_id' => $c1,
            'mastery_score' => 45, 'mastery_level' => 'needs_improvement', 'attempt_count' => 4,
        ]);

        $profile = $this->service->recompute($user->id);

        $this->assertSame('slow', $profile->learning_pace);
    }

    public function test_confidence_alignment_is_inverse_of_average_cmi(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        [$c1] = $this->competencies(1);

        MasteryRecord::create([
            'user_id' => $user->id, 'competency_id' => $c1,
            'mastery_score' => 80, 'mastery_level' => 'developing', 'attempt_count' => 2,
        ]);
        // Two attempts with CMI 0.2 and 0.4 → avg 0.3 → alignment 0.7
        foreach ([0.2, 0.4] as $cmi) {
            AiFeedbackLog::create([
                'user_id' => $user->id, 'competency_id' => $c1, 'quiz_id' => 1,
                'feedback_text' => 't', 'error_pattern' => 'none', 'lp_assigned' => 3,
                'gi_score' => 0.0, 'cmi_score' => $cmi, 'score' => 80,
                'total_questions' => 10, 'correct_count' => 8, 'status' => 'pending',
                'mistakes' => [], 'suggestions' => [],
            ]);
        }

        $profile = $this->service->recompute($user->id);

        $this->assertEqualsWithDelta(0.7, (float) $profile->confidence_alignment, 0.0001);
    }

    public function test_recompute_is_idempotent_and_updates_existing_row(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        [$c1] = $this->competencies(1);
        MasteryRecord::create([
            'user_id' => $user->id, 'competency_id' => $c1,
            'mastery_score' => 80, 'mastery_level' => 'developing', 'attempt_count' => 1,
        ]);

        $this->service->recompute($user->id);
        $this->service->recompute($user->id);

        $this->assertSame(1, LearnerProfile::where('user_id', $user->id)->count());
    }
}
