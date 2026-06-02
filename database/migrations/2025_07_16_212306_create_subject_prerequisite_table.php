<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('subject_prerequisites')) {
            return;
        }

        Schema::create('subject_prerequisites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('prerequisite_subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->enum('logic', ['ALL', 'ANY'])->default('ALL');
            $table->decimal('min_grade', 3, 1)->default(3.0);
            $table->timestamps();

            $table->unique(['subject_id', 'prerequisite_subject_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subject_prerequisites');
    }
};
