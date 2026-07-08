<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('learning_tracks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('competency_id')
                ->constrained('competencies')
                ->cascadeOnDelete();
            $table->unsignedTinyInteger('lp');
            $table->boolean('prerequisite_status')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'competency_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_tracks');
    }
};
