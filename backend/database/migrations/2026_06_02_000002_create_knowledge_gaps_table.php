<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('knowledge_gaps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('competency_id')->constrained('competencies')->cascadeOnDelete();

            // What kind of gap was detected
            $table->enum('gap_type', ['repeated_difficulty', 'confident_misconception']);
            $table->string('detail')->nullable();
            $table->unsignedInteger('occurrences')->default(1);
            $table->enum('status', ['open', 'resolved'])->default('open');

            $table->timestamp('last_detected_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            // One live record per (student, competency, kind)
            $table->unique(['user_id', 'competency_id', 'gap_type']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_gaps');
    }
};
