<?php

// MOCK: Replace with real Moodle Web Services calls when token is available.
// Real implementation will use Http::baseUrl(config('moodle.base_url'))
// and call functions like mod_quiz_get_user_attempts, gradereport_user_get_grade_items,
// core_completion_get_course_completion_status — passing the token from config('moodle.token').

namespace App\Services\Moodle;

class MoodleService
{
    public function getQuizAttempts(int $moodleUserId, int $moodleQuizId): array
    {
        return [
            'attempts' => [
                [
                    'id'             => 1001,
                    'quiz'           => $moodleQuizId,
                    'userid'         => $moodleUserId,
                    'attempt'        => 1,
                    'state'          => 'finished',
                    'timestart'      => 1714900000,
                    'timefinish'     => 1714900900,
                    'sumgrades'      => 8.0,
                    'maxgrades'      => 10.0,
                ],
            ],
        ];
    }

    public function getGrades(int $moodleUserId, int $moodleCourseId): array
    {
        return [
            'usergrades' => [
                [
                    'courseid'   => $moodleCourseId,
                    'userid'     => $moodleUserId,
                    'gradeitems' => [
                        [
                            'id'             => 5001,
                            'itemname'       => 'Quiz: Financial Statements Basics',
                            'itemtype'       => 'mod',
                            'gradeformatted' => '8.00',
                            'graderaw'       => 8.0,
                            'grademax'       => 10.0,
                            'percentage'     => 80.0,
                        ],
                    ],
                ],
            ],
        ];
    }

    public function getCourseCompletion(int $moodleUserId, int $moodleCourseId): array
    {
        return [
            'completionstatus' => [
                'completed'  => false,
                'aggregation' => 1,
                'completions' => [
                    [
                        'type'       => 'activity',
                        'title'      => 'Lesson: Intro to Financial Statements',
                        'status'     => 'complete',
                        'complete'   => true,
                        'timecompleted' => 1714895000,
                    ],
                    [
                        'type'       => 'activity',
                        'title'      => 'Quiz: Financial Statements Basics',
                        'status'     => 'incomplete',
                        'complete'   => false,
                        'timecompleted' => null,
                    ],
                ],
            ],
        ];
    }
}
