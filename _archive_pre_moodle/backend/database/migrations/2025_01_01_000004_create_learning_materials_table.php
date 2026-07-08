<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('learning_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competency_id')
                ->constrained('competencies')
                ->cascadeOnDelete();
            $table->string('title');
            $table->enum('type', ['video', 'text', 'activity']);
            $table->string('content_url')->nullable();
            $table->string('vark_type')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_materials');
    }
};
