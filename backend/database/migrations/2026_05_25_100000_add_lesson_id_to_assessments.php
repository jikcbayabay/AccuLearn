<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            $table->unsignedBigInteger('lesson_id')->nullable()->after('competency_id');
            $table->foreign('lesson_id')
                  ->references('id')
                  ->on('learning_materials')
                  ->nullOnDelete();
        });

        // Populate lesson_id: link each assessment to the text-type learning material
        // for its competency.
        DB::statement("
            UPDATE assessments a
            INNER JOIN learning_materials lm
                ON lm.competency_id = a.competency_id
               AND lm.type = 'text'
            SET a.lesson_id = lm.id
        ");
    }

    public function down(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            $table->dropForeign(['lesson_id']);
            $table->dropColumn('lesson_id');
        });
    }
};
