<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('learner_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();

            // Derived study characteristics
            $table->enum('learning_pace', ['fast', 'medium', 'slow'])->default('medium');
            $table->decimal('avg_mastery', 5, 2)->default(0);          // 0–100
            $table->decimal('confidence_alignment', 5, 4)->default(0); // 0–1, higher = better calibrated
            $table->unsignedInteger('attempts_count')->default(0);
            $table->unsignedInteger('lessons_completed')->default(0);
            $table->unsignedInteger('open_gaps')->default(0);
            $table->string('preferred_modality')->nullable();          // VARK, when available

            $table->timestamp('last_recomputed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learner_profiles');
    }
};
