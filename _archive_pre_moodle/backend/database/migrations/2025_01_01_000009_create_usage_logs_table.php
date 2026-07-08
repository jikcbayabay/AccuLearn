<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->string('action');
            $table->foreignId('module_id')
                ->nullable()
                ->constrained('modules')
                ->nullOnDelete();
            $table->foreignId('competency_id')
                ->nullable()
                ->constrained('competencies')
                ->nullOnDelete();
            $table->timestamp('logged_at')->useCurrent();

            $table->index(['user_id', 'logged_at']);
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usage_logs');
    }
};
