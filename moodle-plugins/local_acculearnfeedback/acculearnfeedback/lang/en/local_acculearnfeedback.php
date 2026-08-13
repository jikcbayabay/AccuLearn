<?php
defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'AccuLearn Feedback';
$string['feedbacktitle'] = 'Your AI-Assisted Feedback';
$string['notfound'] = 'No feedback found for this attempt yet. It may still be generating — please check again in a minute.';
$string['notyours'] = 'You do not have permission to view this feedback.';

// Capabilities.
$string['acculearnfeedback:reviewaioutput'] = 'Review and flag AI-generated feedback and learning tracks';
$string['acculearnfeedback:revealstudentnames'] = 'Reveal real student names in AI-output review screens';
$string['acculearnfeedback:managesequence'] = 'Manage competency sequence, prerequisites and gating';
$string['acculearnfeedback:viewusagelogs'] = 'View AccuLearn system usage/engagement logs';

// Privacy metadata.
$string['privacy:metadata:openai'] = 'To generate formative feedback and personalized learning track narratives, anonymized performance data (mastery percentages, confidence indices, and the text of incorrectly answered questions) is sent to the OpenAI API.';
$string['privacy:metadata:openai:summary'] = 'Quiz performance data is sent to this service to generate AI feedback and learning-track narratives.';

$string['privacy:metadata:acculearn_feedback'] = 'AI-generated formative feedback for a single quiz attempt.';
$string['privacy:metadata:acculearn_feedback:attemptid'] = 'The ID of the quiz attempt this feedback was generated for.';
$string['privacy:metadata:acculearn_feedback:userid'] = 'The ID of the student this feedback is about.';
$string['privacy:metadata:acculearn_feedback:quizname'] = 'The name of the quiz.';
$string['privacy:metadata:acculearn_feedback:cm'] = 'Competency Mastery percentage computed for this attempt.';
$string['privacy:metadata:acculearn_feedback:cmi'] = 'Confidence Misconception Index computed for this attempt.';
$string['privacy:metadata:acculearn_feedback:gi'] = 'Guessing Index computed for this attempt.';
$string['privacy:metadata:acculearn_feedback:feedback_json'] = 'The AI-generated feedback text (summary, misconceptions, corrective guidance, encouragement).';
$string['privacy:metadata:acculearn_feedback:teacher_note'] = 'An optional note a teacher attached when reviewing this feedback.';
$string['privacy:metadata:acculearn_feedback:timecreated'] = 'The time this feedback was generated.';

$string['privacy:metadata:acculearn_learning_tracks'] = 'A student\'s current personalized learning track.';
$string['privacy:metadata:acculearn_learning_tracks:userid'] = 'The ID of the student this track belongs to.';
$string['privacy:metadata:acculearn_learning_tracks:track_json'] = 'The per-competency mastery status, pathway assignment, and AI-generated narrative overview.';
$string['privacy:metadata:acculearn_learning_tracks:teacher_note'] = 'An optional note a teacher attached when reviewing this track.';
$string['privacy:metadata:acculearn_learning_tracks:generated_at'] = 'The time this track was last generated.';

$string['privacy:metadata:acculearn_pretest_responses'] = 'A single paper pre-test question response, manually entered by a teacher or administrator.';
$string['privacy:metadata:acculearn_pretest_responses:userid'] = 'The ID of the student who answered this question.';
$string['privacy:metadata:acculearn_pretest_responses:questionid'] = 'The ID of the question answered.';
$string['privacy:metadata:acculearn_pretest_responses:competency_id'] = 'The competency this question is mapped to.';
$string['privacy:metadata:acculearn_pretest_responses:confidence'] = 'The confidence level recorded for this response (1 = low, 2 = medium, 3 = high).';
$string['privacy:metadata:acculearn_pretest_responses:is_correct'] = 'Whether the response was correct.';
$string['privacy:metadata:acculearn_pretest_responses:entered_at'] = 'The time this response was entered into the system.';

$string['privacy:metadata:acculearn_posttest_responses'] = 'A single paper post-test question response, manually entered by a teacher or administrator. Uses the same questions as the pre-test so the two can be compared.';
$string['privacy:metadata:acculearn_posttest_responses:userid'] = 'The ID of the student who answered this question.';
$string['privacy:metadata:acculearn_posttest_responses:questionid'] = 'The ID of the question answered.';
$string['privacy:metadata:acculearn_posttest_responses:competency_id'] = 'The competency this question is mapped to.';
$string['privacy:metadata:acculearn_posttest_responses:confidence'] = 'The confidence level recorded for this response (1 = low, 2 = medium, 3 = high).';
$string['privacy:metadata:acculearn_posttest_responses:is_correct'] = 'Whether the response was correct.';
$string['privacy:metadata:acculearn_posttest_responses:entered_at'] = 'The time this response was entered into the system.';

$string['privacy:metadata:acculearn_review_log'] = 'A record of a teacher approving, flagging, or overriding an AI-generated output.';
$string['privacy:metadata:acculearn_review_log:target_userid'] = 'The ID of the student whose feedback/track/grade was reviewed.';
$string['privacy:metadata:acculearn_review_log:reviewer_userid'] = 'The ID of the teacher who performed the review action.';
$string['privacy:metadata:acculearn_review_log:entitytype'] = 'Whether the reviewed item was a feedback record or a learning track.';
$string['privacy:metadata:acculearn_review_log:action'] = 'The action taken (approve, flag, or grade override).';
$string['privacy:metadata:acculearn_review_log:note'] = 'An optional note left by the reviewing teacher.';
$string['privacy:metadata:acculearn_review_log:timecreated'] = 'The time this review action was recorded.';

// Usage logging.
$string['usagelogstitle'] = 'AccuLearn Usage Logs';
