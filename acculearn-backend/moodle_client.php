<?php
require_once __DIR__ . '/config.php';

class MoodleClient {

    private function call($function, $params = []) {
        $params['wstoken'] = MOODLE_TOKEN;
        $params['wsfunction'] = $function;
        $params['moodlewsrestformat'] = 'json';

        $url = MOODLE_URL . '/webservice/rest/server.php';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpcode !== 200) {
            throw new Exception("Moodle API HTTP error: $httpcode");
        }

        $data = json_decode($response, true);
        if (isset($data['exception'])) {
            throw new Exception("Moodle API error: " . $data['message']);
        }
        return $data;
    }

    public function getSiteInfo() {
        return $this->call('core_webservice_get_site_info');
    }

    public function getUserQuizAttempts($quizid, $userid) {
        return $this->call('mod_quiz_get_user_quiz_attempts', [
            'quizid' => $quizid,
            'userid' => $userid,
        ]);
    }

    public function getAttemptReview($attemptid) {
        return $this->call('mod_quiz_get_attempt_review', [
            'attemptid' => $attemptid,
        ]);
    }

    public function getAttemptData($attemptid, $page = 0) {
        return $this->call('mod_quiz_get_attempt_data', [
            'attemptid' => $attemptid,
            'page' => $page,
        ]);
    }
}
