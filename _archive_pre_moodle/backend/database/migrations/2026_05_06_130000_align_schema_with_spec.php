<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Default `order` from 0 → 1
        DB::statement("ALTER TABLE `modules` MODIFY `order` INT UNSIGNED NOT NULL DEFAULT 1");
        DB::statement("ALTER TABLE `competencies` MODIFY `order` INT UNSIGNED NOT NULL DEFAULT 1");

        // vark_type: varchar (nullable) → enum NOT NULL
        DB::statement(
            "ALTER TABLE `learning_materials` MODIFY `vark_type` " .
            "ENUM('visual','auditory','readwrite','kinesthetic') NOT NULL"
        );

        // passing_score: tinyint unsigned default 75 → decimal(5,2) default 75.00
        DB::statement(
            "ALTER TABLE `assessments` MODIFY `passing_score` " .
            "DECIMAL(5,2) NOT NULL DEFAULT 75.00"
        );

        // attempt_count default 0 → 1
        DB::statement(
            "ALTER TABLE `mastery_records` MODIFY `attempt_count` " .
            "INT UNSIGNED NOT NULL DEFAULT 1"
        );

        // ai_feedback_logs: lp_assigned / gi_score / cmi_score nullable → NOT NULL
        DB::statement("ALTER TABLE `ai_feedback_logs` MODIFY `lp_assigned` TINYINT UNSIGNED NOT NULL");
        DB::statement("ALTER TABLE `ai_feedback_logs` MODIFY `gi_score`    DECIMAL(5,4) NOT NULL");
        DB::statement("ALTER TABLE `ai_feedback_logs` MODIFY `cmi_score`   DECIMAL(5,4) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `modules` MODIFY `order` INT UNSIGNED NOT NULL DEFAULT 0");
        DB::statement("ALTER TABLE `competencies` MODIFY `order` INT UNSIGNED NOT NULL DEFAULT 0");

        DB::statement("ALTER TABLE `learning_materials` MODIFY `vark_type` VARCHAR(255) NULL");

        DB::statement(
            "ALTER TABLE `assessments` MODIFY `passing_score` " .
            "TINYINT UNSIGNED NOT NULL DEFAULT 75"
        );

        DB::statement(
            "ALTER TABLE `mastery_records` MODIFY `attempt_count` " .
            "INT UNSIGNED NOT NULL DEFAULT 0"
        );

        DB::statement("ALTER TABLE `ai_feedback_logs` MODIFY `lp_assigned` TINYINT UNSIGNED NULL");
        DB::statement("ALTER TABLE `ai_feedback_logs` MODIFY `gi_score`    DECIMAL(5,4) NULL");
        DB::statement("ALTER TABLE `ai_feedback_logs` MODIFY `cmi_score`   DECIMAL(5,4) NULL");
    }
};
