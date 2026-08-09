<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

/**
 * Fixed-window rate limiter for outbound OpenAI calls, backed by a small
 * table in the same database acculearn-backend already connects to.
 *
 * Uses an unprefixed table name (acculearn_rate_limit), matching the
 * existing convention of the other acculearn_* tables this backend reads
 * and writes directly via raw PDO (acculearn_feedback, acculearn_learning_tracks,
 * etc. - see the schema-drift note in local_acculearnfeedback/db/install.xml
 * for why these aren't Moodle-prefixed).
 *
 * A single shared bucket ("openai") is used site-wide rather than
 * per-student, since the concern being guarded against is total API spend
 * for the whole pilot, not any individual student's usage.
 */
class RateLimiter {

    /** @var bool set once the table has been verified in this PHP process. */
    private static $tableready = false;

    public function __construct() {
        $this->ensureTable();
    }

    /**
     * Creates the bucket table if absent.
     *
     * Guarded by a per-process static because this previously ran a DDL
     * statement on EVERY instantiation - i.e. twice per quiz submission.
     * In MySQL, DDL triggers an implicit commit, so issuing it routinely
     * alongside the transactional counter below is both wasteful and a
     * needless way to interact with in-flight transactions.
     */
    private function ensureTable() {
        if (self::$tableready) {
            return;
        }
        $db = get_db();
        $db->exec("
            CREATE TABLE IF NOT EXISTS acculearn_rate_limit (
                bucket VARCHAR(64) PRIMARY KEY,
                window_start INT NOT NULL,
                count INT NOT NULL
            )
        ");
        self::$tableready = true;
    }

    /**
     * Returns true and records the call if under the limit for the current
     * window; returns false (and records nothing) if the bucket is already
     * at or above the limit for the window currently in progress.
     *
     * @param string $bucket e.g. 'openai'
     * @param int $maxPerWindow
     * @param int $windowSeconds
     * @return bool
     */
    public function allow($bucket, $maxPerWindow, $windowSeconds) {
        $db = get_db();
        $now = time();
        $windowStart = $now - ($now % $windowSeconds);

        $db->beginTransaction();
        try {
            $stmt = $db->prepare("SELECT window_start, count FROM acculearn_rate_limit WHERE bucket = ? FOR UPDATE");
            $stmt->execute([$bucket]);
            $row = $stmt->fetch();

            if (!$row || (int)$row->window_start !== $windowStart) {
                $db->prepare("
                    INSERT INTO acculearn_rate_limit (bucket, window_start, count)
                    VALUES (?, ?, 1)
                    ON DUPLICATE KEY UPDATE window_start = VALUES(window_start), count = 1
                ")->execute([$bucket, $windowStart]);
                $db->commit();
                return true;
            }

            if ((int)$row->count >= $maxPerWindow) {
                $db->commit();
                return false;
            }

            $db->prepare("UPDATE acculearn_rate_limit SET count = count + 1 WHERE bucket = ?")->execute([$bucket]);
            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }
}
