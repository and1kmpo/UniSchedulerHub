<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('curriculum_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curriculum_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('semester_recommended');
            $table->unsignedTinyInteger('credits');
            $table->enum('type', ['required', 'elective']);
            $table->foreignId('area_id')->nullable()->constrained('subject_areas')->nullOnDelete();

            $table->timestamps();

            $table->unique(['curriculum_id', 'subject_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curriculum_subjects');
    }
};
