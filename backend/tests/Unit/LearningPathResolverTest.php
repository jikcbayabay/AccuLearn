<?php

namespace Tests\Unit;

use App\Services\Mastery\LearningPathResolver;
use PHPUnit\Framework\TestCase;

/**
 * Pure-logic unit tests for the LP1–LP4 selector. No DB / app boot needed.
 */
class LearningPathResolverTest extends TestCase
{
    private LearningPathResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new LearningPathResolver();
    }

    public function test_prerequisite_not_met_yields_lp1_prerequisite_review(): void
    {
        // Even a perfect score routes to LP1 when the prerequisite is unmet.
        $this->assertSame(1, $this->resolver->resolve(95.0, false, 0.0, 0.0));
    }

    public function test_below_developing_threshold_yields_lp2_guided_remediation(): void
    {
        $this->assertSame(2, $this->resolver->resolve(60.0, true, 0.0, 0.0));
        $this->assertSame(2, $this->resolver->resolve(74.99, true, 0.0, 0.0));
    }

    public function test_developing_band_yields_lp3_reinforcement(): void
    {
        $this->assertSame(3, $this->resolver->resolve(75.0, true, 0.0, 0.0));
        $this->assertSame(3, $this->resolver->resolve(84.99, true, 0.0, 0.0));
    }

    public function test_mastery_with_high_guessing_index_downshifts_to_lp3(): void
    {
        // Mastered score but likely guessing → reinforce instead of advance.
        $this->assertSame(3, $this->resolver->resolve(90.0, true, 0.6, 0.0));
    }

    public function test_mastery_with_high_misconception_index_downshifts_to_lp2(): void
    {
        // Confidently wrong → guided remediation, even at a high score.
        $this->assertSame(2, $this->resolver->resolve(90.0, true, 0.0, 0.4));
    }

    public function test_clean_mastery_yields_lp4_advancement(): void
    {
        $this->assertSame(4, $this->resolver->resolve(90.0, true, 0.0, 0.0));
        $this->assertSame(4, $this->resolver->resolve(100.0, true, 0.5, 0.3));
    }
}
