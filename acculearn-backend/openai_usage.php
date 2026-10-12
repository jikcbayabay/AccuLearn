<?php
/**
 * Reads AccuLearn's OpenAI usage straight from OpenAI.
 *
 * WHY AN ADMIN KEY: the figures live under /v1/organization/usage/* and
 * /v1/organization/costs. Those endpoints reject a project key (sk-proj-*) with
 * HTTP 403 - verified against this install's own key. The older /v1/usage?date=
 * endpoint still answers 200 for a project key but returns an empty envelope on
 * every date, including days this site is known to have made calls, so it is not
 * a usable fallback. Only an organization ADMIN key (sk-admin-*) can read them.
 *
 * THIRTY DAYS, HARD: every request window is clamped to 30 days. The clamp lives
 * in one place (self::MAX_DAYS, applied in windowDays()) so no caller can widen
 * it by passing a larger number, and the page cannot offer a longer range.
 */

require_once(__DIR__ . '/config.php');

class OpenAIUsage {

    /** The only window this class will ever look at. */
    const MAX_DAYS = 30;

    /** Ranges the UI may offer. Nothing longer than MAX_DAYS appears here. */
    const ALLOWED_RANGES = [7, 14, 30];

    /** Responses are cached this long, so a page refresh is not an API call. */
    const CACHE_TTL = 900;   // 15 minutes

    const BASE = 'https://api.openai.com/v1';

    /** @var string */
    private $key;

    public function __construct() {
        $this->key = defined('OPENAI_ADMIN_KEY') ? trim(OPENAI_ADMIN_KEY) : '';
    }

    /** True when an admin key is present and looks like one. */
    public function isConfigured() {
        return $this->key !== '';
    }

    /**
     * Clamps any requested window into 1..MAX_DAYS.
     *
     * Deliberately silent rather than throwing: a stray ?days=365 in the address
     * bar should quietly become 30, not produce an error page.
     */
    public static function windowDays($days) {
        $days = (int) $days;
        if ($days < 1) {
            return self::MAX_DAYS;
        }
        return min($days, self::MAX_DAYS);
    }

    /**
     * Usage and cost for the window.
     *
     * @param int $days requested window, clamped to MAX_DAYS
     * @return array {
     *   configured: bool, error: string|null, days: int,
     *   requests: int, input_tokens: int, output_tokens: int, cost_usd: float|null,
     *   models: array<string,int>, daily: array<string,array>
     * }
     */
    public function summary($days = self::MAX_DAYS) {
        $days = self::windowDays($days);

        $out = [
            'configured'    => $this->isConfigured(),
            'error'         => null,
            'days'          => $days,
            'requests'      => 0,
            'input_tokens'  => 0,
            'output_tokens' => 0,
            'cost_usd'      => null,
            'models'        => [],
            'projects'      => [],
            'daily'         => [],
            'fetched_at'    => null,
        ];

        if (!$this->isConfigured()) {
            $out['error'] = 'no_admin_key';
            return $out;
        }

        $cached = $this->cacheGet($days);
        if ($cached !== null) {
            return $cached;
        }

        // Midnight UTC $days ago, so buckets line up with calendar days.
        $start = strtotime('today UTC') - (($days - 1) * 86400);

        $usage = $this->get('/organization/usage/completions', [
            'start_time'   => $start,
            'bucket_width' => '1d',
            'limit'        => $days,
            // Without group_by the API returns one undifferentiated result per
            // bucket and every row's model reads 'unknown'. project_id is asked
            // for as well so the page can say whether the figures are this
            // project's alone or the whole organisation's.
            'group_by'     => ['model', 'project_id'],
        ]);
        if (isset($usage['__error'])) {
            $out['error'] = $usage['__error'];
            return $out;
        }

        foreach (($usage['data'] ?? []) as $bucket) {
            $day = gmdate('Y-m-d', $bucket['start_time'] ?? $start);
            $drequests = 0; $din = 0; $dout = 0;
            foreach (($bucket['results'] ?? []) as $r) {
                $drequests += (int) ($r['num_model_requests'] ?? 0);
                $din       += (int) ($r['input_tokens'] ?? 0);
                $dout      += (int) ($r['output_tokens'] ?? 0);
                $model = $r['model'] ?? 'unknown';
                $out['models'][$model] = ($out['models'][$model] ?? 0)
                    + (int) ($r['num_model_requests'] ?? 0);
                if (!empty($r['project_id'])) {
                    $out['projects'][$r['project_id']] = ($out['projects'][$r['project_id']] ?? 0)
                        + (int) ($r['num_model_requests'] ?? 0);
                }
            }
            $out['requests']      += $drequests;
            $out['input_tokens']  += $din;
            $out['output_tokens'] += $dout;
            $out['daily'][$day] = [
                'requests' => $drequests, 'input' => $din, 'output' => $dout, 'cost' => 0.0,
            ];
        }

        // Cost is a separate endpoint. A failure here must not lose the usage
        // figures already gathered, so it is tolerated rather than fatal.
        $costs = $this->get('/organization/costs', [
            'start_time'   => $start,
            'bucket_width' => '1d',
            'limit'        => $days,
        ]);
        if (!isset($costs['__error'])) {
            $total = 0.0;
            foreach (($costs['data'] ?? []) as $bucket) {
                $day = gmdate('Y-m-d', $bucket['start_time'] ?? $start);
                $dc = 0.0;
                foreach (($bucket['results'] ?? []) as $r) {
                    $dc += (float) ($r['amount']['value'] ?? 0);
                }
                $total += $dc;
                if (isset($out['daily'][$day])) {
                    $out['daily'][$day]['cost'] = $dc;
                } else {
                    $out['daily'][$day] = ['requests' => 0, 'input' => 0, 'output' => 0, 'cost' => $dc];
                }
            }
            $out['cost_usd'] = $total;
        }

        ksort($out['daily']);
        arsort($out['models']);
        arsort($out['projects']);
        $out['fetched_at'] = time();
        $this->cachePut($days, $out);
        return $out;
    }

    // ---------------------------------------------------------------- http

    private function get($path, array $params) {
        // http_build_query() would encode a list as group_by%5B0%5D=model, which
        // the API ignores. It expects the key repeated: group_by=a&group_by=b.
        $pairs = [];
        foreach ($params as $k => $v) {
            foreach ((array) $v as $one) {
                $pairs[] = rawurlencode($k) . '=' . rawurlencode($one);
            }
        }
        $url = self::BASE . $path . '?' . implode('&', $pairs);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $this->key],
            CURLOPT_TIMEOUT        => 25,
        ]);
        $body = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $cerr = curl_error($ch);
        curl_close($ch);

        if ($body === false) {
            return ['__error' => 'network: ' . $cerr];
        }
        $json = json_decode($body, true);
        if ($code === 401 || $code === 403) {
            // Much the most likely misconfiguration: a project key pasted into
            // OPENAI_ADMIN_KEY. Say so rather than showing a bare 403.
            return ['__error' => 'forbidden'];
        }
        if ($code !== 200) {
            return ['__error' => 'http_' . $code];
        }
        return is_array($json) ? $json : ['__error' => 'bad_json'];
    }

    // --------------------------------------------------------------- cache
    // A plain file cache. This directory sits inside acculearn-backend, which
    // Apache is configured never to serve (acculearn-backend-deny.conf), so the
    // cached figures are not reachable over the web.

    private function cacheFile($days) {
        return __DIR__ . '/.cache/openai_usage_' . (int) $days . '.json';
    }

    private function cacheGet($days) {
        $f = $this->cacheFile($days);
        if (!is_readable($f)) {
            return null;
        }
        $data = json_decode(file_get_contents($f), true);
        if (!is_array($data) || empty($data['fetched_at'])) {
            return null;
        }
        if (time() - $data['fetched_at'] > self::CACHE_TTL) {
            return null;
        }
        return $data;
    }

    private function cachePut($days, array $data) {
        $dir = __DIR__ . '/.cache';
        if (!is_dir($dir)) {
            @mkdir($dir, 0770, true);
        }
        @file_put_contents($this->cacheFile($days), json_encode($data));
    }
}
