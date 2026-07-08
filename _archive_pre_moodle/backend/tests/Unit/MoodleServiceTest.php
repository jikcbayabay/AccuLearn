<?php

namespace Tests\Unit;

use App\Services\Moodle\MoodleService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MoodleServiceTest extends TestCase
{
    private MoodleService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new MoodleService();
    }

    public function test_returns_sample_payload_when_moodle_is_unconfigured(): void
    {
        config(['moodle.base_url' => '', 'moodle.token' => '']);

        $grades = $this->service->getGrades(1, 1);

        $this->assertArrayHasKey('usergrades', $grades);
        $this->assertNotEmpty($grades['usergrades']);
    }

    public function test_uses_real_web_service_response_when_configured(): void
    {
        config(['moodle.base_url' => 'https://moodle.test', 'moodle.token' => 'tok']);

        Http::fake([
            '*/webservice/rest/server.php' => Http::response([
                'usergrades' => [['courseid' => 7, 'userid' => 3, 'gradeitems' => []]],
            ], 200),
        ]);

        $grades = $this->service->getGrades(3, 7);

        $this->assertSame(7, $grades['usergrades'][0]['courseid']);
        $this->assertSame(3, $grades['usergrades'][0]['userid']);
    }

    public function test_moodle_exception_payload_falls_back(): void
    {
        config(['moodle.base_url' => 'https://moodle.test', 'moodle.token' => 'tok']);

        // Moodle reports app-level errors as HTTP 200 with an "exception" key.
        Http::fake([
            '*/webservice/rest/server.php' => Http::response([
                'exception' => 'invalid_parameter_exception',
                'errorcode' => 'invalidparameter',
            ], 200),
        ]);

        $attempts = $this->service->getQuizAttempts(1, 1);

        // Falls back to the sample payload shape.
        $this->assertArrayHasKey('attempts', $attempts);
        $this->assertNotEmpty($attempts['attempts']);
    }

    public function test_server_error_falls_back_after_retries(): void
    {
        config([
            'moodle.base_url' => 'https://moodle.test',
            'moodle.token'    => 'tok',
            'moodle.retries'  => 1,
        ]);

        Http::fake(['*/webservice/rest/server.php' => Http::response('boom', 500)]);

        $completion = $this->service->getCourseCompletion(1, 1);

        $this->assertArrayHasKey('completionstatus', $completion);
    }
}
