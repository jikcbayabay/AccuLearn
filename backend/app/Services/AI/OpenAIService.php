<?php

// MOCK: Replace with real GPT-4o mini API call when key is available.
// Real implementation will POST to OpenAI's /v1/chat/completions endpoint
// using config('openai.api_key') and config('openai.model'), with a system
// prompt instructing the model to act as a Grade 11 ABM tutor.

namespace App\Services\AI;

class OpenAIService
{
    public function generateFeedback(
        string $competencyTitle,
        float $masteryScore,
        ?string $errorPattern = null
    ): array {
        return [
            'feedback_text' => "Nice effort on \"{$competencyTitle}\"! "
                . "You scored {$masteryScore}% on this attempt. "
                . "Focus on reviewing the worked examples in your handout, "
                . "especially the parts where the steps differ from a simple cash transaction. "
                . "Try the practice set again tomorrow — repetition will help the concepts stick.",
            'error_pattern' => $errorPattern ?? 'Confuses similar accounting concepts',
            'model'         => 'mock',
        ];
    }
}
