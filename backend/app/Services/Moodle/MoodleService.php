<?php

namespace App\Services\Moodle;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Thin client over the Moodle REST Web Services API.
 *
 * Production path: when a base URL and token are configured it POSTs to
 * {base_url}/webservice/rest/server.php with the configured token, retrying
 * transient failures with exponential backoff. When Moodle is not configured —
 * or a call fails — each method returns a deterministic sample payload of the
 * same shape, so callers (and the demo) keep working without a live LMS.
 */
class MoodleService
{
    public function getQuizAttempts(int $moodleUserId, int $moodleQuizId): array
    {
        return $this->callOrFallback(
            'mod_quiz_get_user_attempts',
            ['quizid' => $moodleQuizId, 'userid' => $moodleUserId],
            fn () => [
                'attempts' => [[
                    'id'         => 1001,
                    'quiz'       => $moodleQuizId,
                    'userid'     => $moodleUserId,
                    'attempt'    => 1,
                    'state'      => 'finished',
                    'timestart'  => 1714900000,
                    'timefinish' => 1714900900,
                    'sumgrades'  => 8.0,
                    'maxgrades'  => 10.0,
                ]],
            ],
        );
    }

    public function getGrades(int $moodleUserId, int $moodleCourseId): array
    {
        return $this->callOrFallback(
            'gradereport_user_get_grade_items',
            ['courseid' => $moodleCourseId, 'userid' => $moodleUserId],
            fn () => [
                'usergrades' => [[
                    'courseid'   => $moodleCourseId,
                    'userid'     => $moodleUserId,
                    'gradeitems' => [[
                        'id'             => 5001,
                        'itemname'       => 'Quiz: Financial Statements Basics',
                        'itemtype'       => 'mod',
                        'gradeformatted' => '8.00',
                        'graderaw'       => 8.0,
                        'grademax'       => 10.0,
                        'percentage'     => 80.0,
                    ]],
                ]],
            ],
        );
    }

    public function getCourseCompletion(int $moodleUserId, int $moodleCourseId): array
    {
        return $this->callOrFallback(
            'core_completion_get_course_completion_status',
            ['courseid' => $moodleCourseId, 'userid' => $moodleUserId],
            fn () => [
                'completionstatus' => [
                    'completed'   => false,
                    'aggregation' => 1,
                    'completions' => [
                        [
                            'type'          => 'activity',
                            'title'         => 'Lesson: Intro to Financial Statements',
                            'status'        => 'complete',
                            'complete'      => true,
                            'timecompleted' => 1714895000,
                        ],
                        [
                            'type'          => 'activity',
                            'title'         => 'Quiz: Financial Statements Basics',
                            'status'        => 'incomplete',
                            'complete'      => false,
                            'timecompleted' => null,
                        ],
                    ],
                ],
            ],
        );
    }

    /**
     * Run a Web Service call, or return the fallback payload when Moodle is
     * unconfigured or the call ultimately fails.
     *
     * @param  callable():array  $fallback
     */
    private function callOrFallback(string $wsFunction, array $params, callable $fallback): array
    {
        $baseUrl = rtrim((string) config('moodle.base_url', ''), '/');
        $token   = (string) config('moodle.token', '');

        if ($baseUrl === '' || $token === '') {
            return $fallback();
        }

        $result = $this->call($baseUrl, $token, $wsFunction, $params);

        return $result ?? $fallback();
    }

    /**
     * POST a Web Service function with retry + exponential backoff.
     * Returns the decoded array, or null on failure / Moodle exception.
     */
    private function call(string $baseUrl, string $token, string $wsFunction, array $params): ?array
    {
        $endpoint = $baseUrl . '/webservice/rest/server.php';
        $payload  = array_merge($params, [
            'wstoken'            => $token,
            'wsfunction'         => $wsFunction,
            'moodlewsrestformat' => (string) config('moodle.format', 'json'),
        ]);

        $timeout = (int) config('moodle.timeout', 30);
        $retries = (int) config('moodle.retries', 2);

        for ($attempt = 0; $attempt <= $retries; $attempt++) {
            try {
                $response = Http::asForm()->timeout($timeout)->post($endpoint, $payload);

                if ($response->successful()) {
                    $json = $response->json();

                    // Moodle reports application-level errors as a 200 with an
                    // "exception" key — treat that as a hard failure (no retry).
                    if (is_array($json) && array_key_exists('exception', $json)) {
                        Log::warning('Moodle WS exception', [
                            'function' => $wsFunction,
                            'errorcode' => $json['errorcode'] ?? null,
                        ]);
                        return null;
                    }

                    return is_array($json) ? $json : null;
                }

                if ($response->clientError()) {
                    // 4xx won't fix itself on retry.
                    Log::warning('Moodle WS client error', [
                        'function' => $wsFunction,
                        'status'   => $response->status(),
                    ]);
                    return null;
                }
            } catch (\Throwable $e) {
                Log::warning("Moodle WS attempt {$attempt} failed: " . $e->getMessage());
            }

            if ($attempt < $retries) {
                usleep((int) (250_000 * (2 ** $attempt))); // 250ms, 500ms, …
            }
        }

        return null;
    }
}
