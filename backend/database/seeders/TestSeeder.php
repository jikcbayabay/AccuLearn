<?php

namespace Database\Seeders;

use App\Enums\MasteryLevel;
use App\Models\Assessment;
use App\Models\Competency;
use App\Models\LearningMaterial;
use App\Models\MasteryRecord;
use App\Models\Module;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestSeeder extends Seeder
{
    public function run(): void
    {
        // ----- Users -----
        User::updateOrCreate(
            ['email' => 'admin@acculearn.test'],
            [
                'name'     => 'Admin User',
                'password' => 'password123',
                'role'     => User::ROLE_ADMIN,
                'active'   => true,
                'section'  => null,
            ]
        );

        User::updateOrCreate(
            ['email' => 'teacher@acculearn.test'],
            [
                'name'     => 'Teacher User',
                'password' => 'password123',
                'role'     => User::ROLE_TEACHER,
                'active'   => true,
                'section'  => 'ABM 11-A',
            ]
        );

        $student1 = User::updateOrCreate(
            ['email' => 'student1@acculearn.test'],
            [
                'name'     => 'Student One',
                'password' => 'password123',
                'role'     => User::ROLE_STUDENT,
                'active'   => true,
                'section'  => 'ABM 11-A',
            ]
        );

        User::updateOrCreate(
            ['email' => 'student2@acculearn.test'],
            [
                'name'     => 'Student Two',
                'password' => 'password123',
                'role'     => User::ROLE_STUDENT,
                'active'   => true,
                'section'  => 'ABM 11-A',
            ]
        );

        User::updateOrCreate(
            ['email' => 'student3@acculearn.test'],
            [
                'name'     => 'Student Three',
                'password' => 'password123',
                'role'     => User::ROLE_STUDENT,
                'active'   => true,
                'section'  => 'ABM 11-B',
            ]
        );

        // ----- Modules (FABM 1) -----
        $module1 = Module::updateOrCreate(
            ['title' => 'Introduction to Accounting'],
            [
                'description' => 'The nature and purpose of accounting, its users, and its branches.',
                'order'       => 1,
            ]
        );

        $module2 = Module::updateOrCreate(
            ['title' => 'Accounting Concepts and Principles'],
            [
                'description' => 'GAAP and how core accounting concepts apply to real business transactions.',
                'order'       => 2,
            ]
        );

        $module3 = Module::updateOrCreate(
            ['title' => 'The Accounting Equation'],
            [
                'description' => 'Assets = Liabilities + Equity, transaction effects, and account classification.',
                'order'       => 3,
            ]
        );

        // ----- Competencies (with deped_code + prerequisite chain) -----
        $competencyDefs = [
            ['module' => $module1, 'roman' => 'I',   'titles' => [
                'Define accounting and describe its nature and purpose',
                'Identify the users of accounting information',
                'Distinguish between branches of accounting',
            ]],
            ['module' => $module2, 'roman' => 'II',  'titles' => [
                'Identify and explain generally accepted accounting principles (GAAP)',
                'Apply accounting concepts to business transactions',
            ]],
            ['module' => $module3, 'roman' => 'III', 'titles' => [
                'Explain the accounting equation (Assets = Liabilities + Equity)',
                'Analyze the effects of transactions on the accounting equation',
                'Classify accounts as assets, liabilities, or equity',
            ]],
        ];

        $allCompetencies = collect();

        foreach ($competencyDefs as $def) {
            $previousId = null;
            foreach ($def['titles'] as $idx => $title) {
                $orderNum = $idx + 1;
                $competency = Competency::updateOrCreate(
                    ['module_id' => $def['module']->id, 'title' => $title],
                    [
                        'deped_code'                => "ABM_FABM11-{$def['roman']}-{$orderNum}",
                        'order'                     => $orderNum,
                        'prerequisite_competency_id' => $previousId,
                    ]
                );
                $allCompetencies->push($competency);
                $previousId = $competency->id;
            }
        }

        // ----- Learning materials: 3 per competency (video / text / activity) -----
        foreach ($allCompetencies as $competency) {
            LearningMaterial::updateOrCreate(
                ['competency_id' => $competency->id, 'type' => 'video'],
                [
                    'title'       => "Video: {$competency->title}",
                    'content_url' => "https://placeholder.test/video/{$competency->id}",
                    'vark_type'   => 'visual',
                ]
            );

            LearningMaterial::updateOrCreate(
                ['competency_id' => $competency->id, 'type' => 'text'],
                [
                    'title'       => "Reading: {$competency->title}",
                    'content_url' => "https://placeholder.test/reading/{$competency->id}",
                    'vark_type'   => 'readwrite',
                ]
            );

            LearningMaterial::updateOrCreate(
                ['competency_id' => $competency->id, 'type' => 'activity'],
                [
                    'title'       => "Activity: {$competency->title}",
                    'content_url' => "https://placeholder.test/activity/{$competency->id}",
                    'vark_type'   => 'kinesthetic',
                ]
            );
        }

        // ----- Assessments (1 per competency) -----
        foreach ($allCompetencies as $competency) {
            Assessment::updateOrCreate(
                ['competency_id' => $competency->id],
                [
                    'title'          => "Assessment: {$competency->title}",
                    'passing_score'  => 75.00,
                    'moodle_quiz_id' => null,
                ]
            );
        }

        // ----- Mastery records for student1 (first three competencies) -----
        $progress = [
            ['mastery_score' => 90.00, 'mastery_level' => MasteryLevel::MASTERED->value,          'attempt_count' => 1],
            ['mastery_score' => 78.00, 'mastery_level' => MasteryLevel::DEVELOPING->value,        'attempt_count' => 2],
            ['mastery_score' => 60.00, 'mastery_level' => MasteryLevel::NEEDS_IMPROVEMENT->value, 'attempt_count' => 1],
        ];

        foreach ($progress as $idx => $record) {
            MasteryRecord::updateOrCreate(
                ['user_id' => $student1->id, 'competency_id' => $allCompetencies[$idx]->id],
                $record
            );
        }
    }
}
