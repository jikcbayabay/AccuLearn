<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ai_feedback_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('competency_id')
                ->constrained('competencies')
                ->cascadeOnDelete();
            $table->text('feedback_text');
            $table->string('error_pattern')->nullable();
            $table->unsignedTinyInteger('lp_assigned')->nullable();
            $table->decimal('gi_score', 5, 4)->nullable();
            $table->decimal('cmi_score', 5, 4)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_feedback_logs');
    }
};
