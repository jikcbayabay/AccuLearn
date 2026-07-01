<?php

namespace App\Services\Moodle;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Arr;
use RuntimeException;

class MoodleService
{
    protected function request(string $wsFunction, array $params = []): array
    {
        $baseUrl = rtrim(config('moodle.base_url', ''), '/');

        if (! $baseUrl || ! config('moodle.token')) {
            throw new RuntimeException(
                'Moodle base URL and token must be configured in backend/.env.'
            );
        }

        $response = Http::baseUrl($baseUrl)
            ->get('webservice/rest/server.php', array_merge($params, [
                'wstoken' => config('moodle.token'),
                'wsfunction' => $wsFunction,
                'moodlewsrestformat' => config('moodle.format', 'json'),
            ]));

        if ($response->failed()) {
            throw new RuntimeException(
                "Moodle request failed ({$wsFunction}): " . $response->body()
            );
        }

        $payload = $response->json();

        if (! is_array($payload)) {
            throw new RuntimeException(
                "Moodle response for {$wsFunction} was not valid JSON."
            );
        }

        if (Arr::get($payload, 'exception')) {
            $message = Arr::get($payload, 'message', 'Unknown Moodle exception');

            throw new RuntimeException(
                "Moodle error for {$wsFunction}: {$message}"
            );
        }

        return $payload;
    }

    public function getCourses(): array
    {
        return $this->request('core_course_get_courses');
    }

    public function getCategories(): array
    {
        return $this->request('core_course_get_categories');
    }

    public function getUserCourses(int $moodleUserId): array
    {
        return $this->request('core_enrol_get_users_courses', [
            'userid' => $moodleUserId,
        ]);
    }

    public function getGrades(int $moodleUserId, int $moodleCourseId): array
    {
        return $this->request('gradereport_user_get_grade_items', [
            'userid' => $moodleUserId,
            'courseid' => $moodleCourseId,
        ]);
    }

    public function getCourseCompletion(
        int $moodleUserId,
        int $moodleCourseId
    ): array {
        return $this->request(
            'core_completion_get_course_completion_status',
            [
                'userid' => $moodleUserId,
                'courseid' => $moodleCourseId,
            ]
        );
    }

    public function getQuizAttempts(
        int $moodleUserId,
        int $moodleQuizId
    ): array {
        return $this->request('mod_quiz_get_user_attempts', [
            'quizid' => $moodleQuizId,
            'userid' => $moodleUserId,
        ]);
    }

    public function getSiteInfo(): array
    {
        return $this->request('core_webservice_get_site_info');
    }

    public function getUsers(array $criteria): array
    {
        return $this->request('core_user_get_users', [
            'criteria' => $criteria,
        ]);
    }
    public function createUser(array $userData): array
    {
        return $this->request('core_user_create_users', [
            'users' => [$userData],
        ]);
    }

    public function updateUser(array $userData): array
    {
        return $this->request('core_user_update_users', [
            'users' => [$userData],
        ]);
    }
}