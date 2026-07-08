<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mastery_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('competency_id')
                ->constrained('competencies')
                ->cascadeOnDelete();
            $table->decimal('mastery_score', 5, 2)->default(0);
            $table->enum('mastery_level', ['mastered', 'developing', 'needs_improvement'])
                ->default('needs_improvement');
            $table->unsignedInteger('attempt_count')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'competency_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mastery_records');
    }
};
