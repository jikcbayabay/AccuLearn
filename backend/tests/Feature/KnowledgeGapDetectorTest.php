<?php

namespace Tests\Feature;

use App\Models\AiFeedbackLog;
use App\Models\Competency;
use App\Models\KnowledgeGap;
use App\Models\Module;
use App\Models\User;
use App\Services\Analytics\KnowledgeGapDetector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KnowledgeGapDetectorTest extends TestCase
{
    use RefreshDatabase;

    private KnowledgeGapDetector $detector;
    private int $userId;
    private int $competencyId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->detector = new KnowledgeGapDetector();

        $user   = User::factory()->create(['role' => 'student']);
        $module = Module::create(['title' => 'M1', 'order' => 1]);
        $comp   = Competency::create(['module_id' => $module->id, 'title' => 'C1', 'order' => 1]);

        $this->userId       = $user->id;
        $this->competencyId = $comp->id;
    }

    private function log(float $score, float $cmi = 0.0): void
    {
        AiFeedbackLog::create([
            'user_id' => $this->userId, 'competency_id' => $this->competencyId, 'quiz_id' => 1,
            'feedback_text' => 't', 'error_pattern' => 'missed_items', 'lp_assigned' => 2,
            'gi_score' => 0.1, 'cmi_score' => $cmi, 'score' => $score,
            'total_questions' => 10, 'correct_count' => (int) round($score / 10),
            'status' => 'pending', 'mistakes' => [], 'suggestions' => [],
        ]);
    }

    public function test_three_failing_attempts_open_a_repeated_difficulty_gap(): void
    {
        $this->log(40);
        $this->log(55);
        $this->log(60);

        $open = $this->detector->detect($this->userId, $this->competencyId);

        $this->assertGreaterThanOrEqual(1, $open);
        $gap = KnowledgeGap::where('user_id', $this->userId)
            ->where('gap_type', 'repeated_difficulty')->first();
        $this->assertNotNull($gap);
        $this->assertSame('open', $gap->status);
        $this->assertSame(3, $gap->occurrences);
    }

    public function test_high_cmi_opens_a_confident_misconception_gap(): void
    {
        // Single attempt: below mastery, high confidence-miscalibration.
        $this->log(70, 0.45);

        $this->detector->detect($this->userId, $this->competencyId);

        $gap = KnowledgeGap::where('user_id', $this->userId)
            ->where('gap_type', 'confident_misconception')->first();
        $this->assertNotNull($gap);
        $this->assertSame('open', $gap->status);
    }

    public function test_two_attempts_below_threshold_do_not_open_repeated_difficulty(): void
    {
        $this->log(50);
        $this->log(60);

        $this->detector->detect($this->userId, $this->competencyId);

        $this->assertDatabaseMissing('knowledge_gaps', [
            'user_id' => $this->userId,
            'gap_type' => 'repeated_difficulty',
        ]);
    }

    public function test_mastery_level_attempt_resolves_open_gaps(): void
    {
        // Open a gap first.
        $this->log(40);
        $this->log(55);
        $this->log(60);
        $this->detector->detect($this->userId, $this->competencyId);
        $this->assertSame(1, KnowledgeGap::where('status', 'open')->count());

        // Now a mastery attempt arrives.
        $this->log(90);
        $open = $this->detector->detect($this->userId, $this->competencyId);

        $this->assertSame(0, $open);
        $this->assertSame(0, KnowledgeGap::where('status', 'open')->count());
        $this->assertSame(1, KnowledgeGap::where('status', 'resolved')->count());
    }
}
