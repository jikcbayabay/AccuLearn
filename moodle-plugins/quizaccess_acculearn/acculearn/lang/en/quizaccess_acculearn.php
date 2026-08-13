<?php
defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'AccuLearn prerequisite gating';
$string['prerequisiteblocked'] = 'This competency is currently locked. Your AccuLearn learning track indicates you should complete "Prerequisite Review" for an earlier competency before attempting this quiz. Check "My Learning Track" for details.';
$string['restudyprompt'] = 'You have used all {$a->attempts} attempts for this quiz but have not yet passed it. Please restudy <strong>{$a->competency}</strong>, then ask your teacher to reopen this quiz for you. Visit <a href="{$a->trackurl}">My Learning Track</a> for your personalized guidance.';
$string['summativeblocked'] = 'The Summative Quiz for <strong>{$a->competency}</strong> is locked until you have passed every formative quiz in this module. Still to pass: <strong>{$a->outstanding}</strong>. Visit <a href="{$a->trackurl}">My Learning Track</a> to see where you stand.';
$string['lessonblocked'] = 'Work through the lesson for this topic before you attempt the quiz. Open <a href="{$a->lessonurl}"><strong>{$a->lesson}</strong></a> and go through it to the end — this quiz unlocks as soon as the lesson records you as finished.';
