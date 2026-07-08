<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

class MasteryEngine {

    public function computeCompetencyStats($userid, $competencyId) {
        $db = get_db();
        $sql = "
            SELECT
                qbc.confidence,
                CASE laststep.state
                    WHEN 'gradedright' THEN 1
                    WHEN 'gradedpartial' THEN 0
                    WHEN 'gradedwrong' THEN 0
                    ELSE 0
                END AS is_correct
            FROM mdl_qbehaviour_confidence qbc
            JOIN mdl_question_attempts qas ON qas.id = qbc.questionattemptid
            JOIN acculearn_question_competency qc ON qc.questionid = qas.questionid
            LEFT JOIN mdl_question_attempt_steps laststep
                ON laststep.id = (
                    SELECT id FROM mdl_question_attempt_steps
                    WHERE questionattemptid = qbc.questionattemptid
                    ORDER BY sequencenumber DESC LIMIT 1
                )
            WHERE qbc.userid = ? AND qc.competency_id = ?
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute([$userid, $competencyId]);
        $rows = $stmt->fetchAll();

        return $this->summarize($rows);
    }

    public function computeCompetencyStatsFromPretest($userid, $competencyId) {
        $db = get_db();
        $sql = "
            SELECT confidence, is_correct
            FROM acculearn_pretest_responses
            WHERE userid = ? AND competency_id = ?
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute([$userid, $competencyId]);
        $rows = $stmt->fetchAll();

        return $this->summarize($rows);
    }

    private function summarize($rows) {
        $total = count($rows);
        if ($total === 0) {
            return ['cm' => null, 'cmi' => null, 'gi' => null, 'attempted' => 0];
        }

        $correctCount = 0;
        $highConfidenceIncorrect = 0;
        $lowConfidenceCorrect = 0;

        foreach ($rows as $r) {
            $correct = (bool) $r->is_correct;
            if ($correct) $correctCount++;
            if (!$correct && $r->confidence == 3) $highConfidenceIncorrect++;
            if ($correct && $r->confidence == 1) $lowConfidenceCorrect++;
        }

        return [
            'cm' => round(($correctCount / $total) * 100, 1),
            'cmi' => round($highConfidenceIncorrect / $total, 3),
            'gi' => round($lowConfidenceCorrect / $total, 3),
            'attempted' => $total,
        ];
    }

    public function classifyMastery($cm) {
        if ($cm === null) return 'Not Attempted';
        if ($cm >= 85) return 'Mastered';
        if ($cm >= 75) return 'Developing';
        return 'Needs Improvement';
    }

    public function checkPrerequisite($userid, $competencyId, $source = 'quiz') {
        $db = get_db();
        $stmt = $db->prepare("SELECT prerequisite_id FROM acculearn_competencies WHERE id = ?");
        $stmt->execute([$competencyId]);
        $comp = $stmt->fetch();

        if (!$comp || !$comp->prerequisite_id) {
            return 1;
        }

        $prereqStats = ($source === 'pretest')
            ? $this->computeCompetencyStatsFromPretest($userid, $comp->prerequisite_id)
            : $this->computeCompetencyStats($userid, $comp->prerequisite_id);

        $prereqStatus = $this->classifyMastery($prereqStats['cm']);

        return ($prereqStatus === 'Mastered') ? 1 : 0;
    }

    public function computeLearningPathway($userid, $competencyId, $source = 'quiz') {
        $stats = ($source === 'pretest')
            ? $this->computeCompetencyStatsFromPretest($userid, $competencyId)
            : $this->computeCompetencyStats($userid, $competencyId);

        $pr = $this->checkPrerequisite($userid, $competencyId, $source);

        $cm = $stats['cm'];
        $cmi = $stats['cmi'];
        $gi = $stats['gi'];

        if ($pr === 0) {
            $lp = 1;
        } elseif ($cm === null) {
            $lp = null;
        } elseif ($cm < 75) {
            $lp = 2;
        } elseif ($cm >= 75 && $cm < 85) {
            $lp = 3;
        } elseif ($cm >= 85 && $gi >= 0.20) {
            $lp = 3;
        } elseif ($cm >= 85 && $cmi < 0.20) {
            $lp = 4;
        } else {
            $lp = 3;
        }

        return [
            'competency_id' => $competencyId,
            'cm' => $cm,
            'cmi' => $cmi,
            'gi' => $gi,
            'pr' => $pr,
            'mastery' => $this->classifyMastery($cm),
            'lp' => $lp,
            'lp_label' => $this->lpLabel($lp),
        ];
    }

    private function lpLabel($lp) {
        switch ($lp) {
            case 1: return 'Prerequisite Review';
            case 2: return 'Guided Remediation';
            case 3: return 'Guided Remediation';
            case 4: return 'Advancement to Next Competency';
            default: return 'Not Yet Assessed';
        }
    }

    public function computeFullTrack($userid, $source = 'quiz') {
        $db = get_db();
        $competencies = $db->query("SELECT * FROM acculearn_competencies ORDER BY sequence")->fetchAll();

        $track = [];
        foreach ($competencies as $c) {
            $result = $this->computeLearningPathway($userid, $c->id, $source);
            $result['competency_name'] = $c->name;
            $result['sequence'] = $c->sequence;
            $track[] = $result;
        }
        return $track;
    }
}
