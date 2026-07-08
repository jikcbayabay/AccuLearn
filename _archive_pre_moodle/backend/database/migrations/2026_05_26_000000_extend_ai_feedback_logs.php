<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ai_feedback_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('quiz_id')->nullable()->after('competency_id');
            $table->decimal('score', 5, 2)->nullable()->after('cmi_score');
            $table->unsignedSmallInteger('total_questions')->nullable()->after('score');
            $table->unsignedSmallInteger('correct_count')->nullable()->after('total_questions');
            $table->string('status', 20)->default('pending')->after('correct_count');
            $table->json('mistakes')->nullable()->after('status');
            $table->json('suggestions')->nullable()->after('mistakes');
        });
    }

    public function down(): void
    {
        Schema::table('ai_feedback_logs', function (Blueprint $table) {
            $table->dropColumn(['quiz_id', 'score', 'total_questions', 'correct_count', 'status', 'mistakes', 'suggestions']);
        });
    }
};
