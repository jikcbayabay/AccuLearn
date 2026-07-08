<?php

namespace Tests\Unit;

use App\Services\AI\OpenAIService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OpenAIServiceTest extends TestCase
{
    private OpenAIService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new OpenAIService();
    }

    public function test_falls_back_to_deterministic_feedback_when_no_api_key(): void
    {
        config(['openai.api_key' => '']);

        $result = $this->service->generateFeedback('The Accounting Equation', 92.0, null);

        $this->assertSame('fallback', $result['model']);
        $this->assertStringContainsString('The Accounting Equation', $result['feedback_text']);
        $this->assertStringContainsString('Excellent', $result['feedback_text']); // high-score band
    }

    public function test_fallback_text_varies_by_score_band(): void
    {
        config(['openai.api_key' => '']);

        $low  = $this->service->generateFeedback('Trial Balance', 30.0)['feedback_text'];
        $high = $this->service->generateFeedback('Trial Balance', 95.0)['feedback_text'];

        $this->assertNotSame($low, $high);
        $this->assertStringContainsString("Let's rebuild", $low);   // < 50 band
        $this->assertStringContainsString('Excellent', $high);      // >= 85 band
    }

    public function test_score_is_clamped_into_valid_range(): void
    {
        config(['openai.api_key' => '']);

        // 150 clamps to 100 → still the mastery band, and prints 100%.
        $text = $this->service->generateFeedback('Ledgers', 150.0)['feedback_text'];
        $this->assertStringContainsString('100%', $text);
    }

    public function test_control_characters_in_title_are_sanitized(): void
    {
        config(['openai.api_key' => '']);

        $text = $this->service->generateFeedback("Journal\n\tEntries", 80.0)['feedback_text'];

        // Newline/tab collapsed to a single space — not preserved literally.
        $this->assertStringContainsString('Journal Entries', $text);
        $this->assertStringNotContainsString("Journal\n", $text);
    }

    public function test_real_api_response_is_used_when_key_present_and_valid(): void
    {
        config(['openai.api_key' => 'sk-test', 'openai.model' => 'gpt-4o-mini']);

        Http::fake([
            '*/chat/completions' => Http::response([
                'choices' => [['message' => ['content' => 'You are doing great — review the worked examples and try two more practice items before retaking.']]],
            ], 200),
        ]);

        $result = $this->service->generateFeedback('Adjusting Entries', 78.0, 'missed_items');

        $this->assertSame('gpt-4o-mini', $result['model']);
        $this->assertStringContainsString('worked examples', $result['feedback_text']);
    }

    public function test_invalid_short_ai_output_triggers_fallback(): void
    {
        config(['openai.api_key' => 'sk-test']);

        Http::fake([
            '*/chat/completions' => Http::response([
                'choices' => [['message' => ['content' => 'ok']]], // too short (< 20 chars)
            ], 200),
        ]);

        $result = $this->service->generateFeedback('Closing Entries', 80.0);

        $this->assertSame('fallback', $result['model']);
    }

    public function test_api_failure_triggers_fallback(): void
    {
        config(['openai.api_key' => 'sk-test']);

        Http::fake(['*/chat/completions' => Http::response('error', 500)]);

        $result = $this->service->generateFeedback('Reversing Entries', 65.0);

        $this->assertSame('fallback', $result['model']);
        $this->assertStringContainsString('Reversing Entries', $result['feedback_text']);
    }
}
