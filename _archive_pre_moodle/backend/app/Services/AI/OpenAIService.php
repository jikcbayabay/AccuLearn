<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Generates Grade 11 ABM tutor feedback for quiz attempts.
 *
 * Production path: calls the OpenAI Chat Completions API when an API key is
 * configured. When no key is set — or on any timeout/error/invalid response —
 * it returns a grounded, deterministic fallback so the learning flow never
 * breaks. This satisfies the thesis "AI Grounding & Output Control" requirement:
 * inputs are sanitized, the system prompt forbids fabricating quiz facts, and
 * outputs are validated before use.
 */
class OpenAIService
{
    /**
     * @return array{feedback_text:string, error_pattern:string, model:string}
     */
    public function generateFeedback(
        string $competencyTitle,
        float $masteryScore,
        ?string $errorPattern = null
    ): array {
        // ── Input sanitization ──────────────────────────────────────────────
        $title  = $this->sanitize($competencyTitle, 160) ?: 'this competency';
        $score  = max(0.0, min(100.0, $masteryScore));
        $errPat = $errorPattern ? $this->sanitize($errorPattern, 120) : null;

        $apiKey = (string) config('openai.api_key', '');

        // No key configured → grounded deterministic feedback.
        if ($apiKey === '') {
            return $this->fallback($title, $score, $errPat);
        }

        try {
            $endpoint = rtrim((string) config('openai.base_url', 'https://api.openai.com/v1'), '/')
                . '/chat/completions';

            $response = Http::withToken($apiKey)
                ->timeout((int) config('openai.timeout', 20))
                ->acceptJson()
                ->post($endpoint, [
                    'model'       => config('openai.model', 'gpt-4o-mini'),
                    'temperature' => 0.4,
                    'max_tokens'  => 220,
                    'messages'    => [
                        ['role' => 'system', 'content' => $this->systemPrompt()],
                        ['role' => 'user',   'content' => $this->userPrompt($title, $score, $errPat)],
                    ],
                ]);

            if (! $response->successful()) {
                Log::warning('OpenAI feedback request failed', ['status' => $response->status()]);
                return $this->fallback($title, $score, $errPat);
            }

            $text = trim((string) data_get($response->json(), 'choices.0.message.content', ''));

            // ── Output validation ──────────────────────────────────────────
            if (! $this->isValidOutput($text)) {
                Log::warning('OpenAI feedback failed validation; using fallback');
                return $this->fallback($title, $score, $errPat);
            }

            return [
                'feedback_text' => $text,
                'error_pattern' => $errPat ?? 'none',
                'model'         => (string) config('openai.model', 'gpt-4o-mini'),
            ];
        } catch (\Throwable $e) {
            Log::warning('OpenAI feedback exception: ' . $e->getMessage());
            return $this->fallback($title, $score, $errPat);
        }
    }

    private function systemPrompt(): string
    {
        return implode(' ', [
            'You are a supportive Grade 11 ABM tutor for Fundamentals of Accountancy, Business and Management 1 (FABM 1) in the Philippines.',
            'Write 2-3 short sentences of encouraging, actionable feedback on a quiz attempt.',
            'Ground your advice ONLY in the competency and score provided — never invent specific quiz questions, numbers, or facts you were not given.',
            'Do not claim the student answered a particular item; speak generally about the topic.',
            'Use plain, warm language a 16-year-old can act on. No markdown, no headings, no emojis.',
        ]);
    }

    private function userPrompt(string $title, float $score, ?string $errPat): string
    {
        $lines = [
            "Competency: {$title}",
            'Score: ' . (int) round($score) . '%',
        ];
        if ($errPat) {
            $lines[] = "Observed difficulty: {$errPat}";
        }
        $lines[] = 'Give brief, specific next steps to improve or extend mastery.';

        return implode("\n", $lines);
    }

    /** Grounded deterministic feedback — used when no key is set or on any failure. */
    private function fallback(string $title, float $score, ?string $errPat): array
    {
        $pct = (int) round($score);

        $body = match (true) {
            $score >= 85 => "Excellent work on \"{$title}\" — {$pct}% shows strong mastery. Stretch yourself by applying these ideas to new Philippine business scenarios, and review any edge cases to keep the concepts sharp.",
            $score >= 75 => "Good effort on \"{$title}\" — {$pct}% means you're on track. Re-read the lesson section covering the items you missed and work through a few more practice problems before your next attempt.",
            $score >= 50 => "Fair attempt on \"{$title}\" ({$pct}%). Go back to the lesson reading, take notes on the key definitions, and focus on the steps where accounting entries differ from a simple cash transaction.",
            default      => "Let's rebuild the basics of \"{$title}\" ({$pct}%). Revisit the full lesson, write each key term in your own words, and ask your teacher to walk through the parts that felt unclear before retaking the quiz.",
        };

        return [
            'feedback_text' => $body,
            'error_pattern' => $errPat ?? 'none',
            'model'         => 'fallback',
        ];
    }

    private function sanitize(string $value, int $max): string
    {
        $clean = preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $value) ?? $value;
        $clean = trim(preg_replace('/\s+/u', ' ', $clean) ?? $clean);

        return mb_substr($clean, 0, $max);
    }

    private function isValidOutput(string $text): bool
    {
        $len = mb_strlen($text);

        return $len >= 20 && $len <= 1200;
    }
}
