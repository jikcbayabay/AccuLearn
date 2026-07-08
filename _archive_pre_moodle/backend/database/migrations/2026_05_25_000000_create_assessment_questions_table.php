<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('assessment_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')
                ->constrained('assessments')
                ->cascadeOnDelete();
            $table->text('question_text');
            $table->string('question_type', 50)->default('multiple_choice');
            $table->unsignedTinyInteger('sequence_order')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_questions');
    }
};
