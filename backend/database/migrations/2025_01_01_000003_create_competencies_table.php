<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('competencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')
                ->constrained('modules')
                ->cascadeOnDelete();
            $table->string('title');
            $table->string('deped_code')->nullable();
            $table->foreignId('prerequisite_competency_id')
                ->nullable()
                ->constrained('competencies')
                ->nullOnDelete();
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competencies');
    }
};
