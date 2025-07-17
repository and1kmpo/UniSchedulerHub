<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subject_prerequisite', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subject_id');        // materia principal
            $table->unsignedBigInteger('prerequisite_id');   // su prerrequisito
            $table->timestamps();

            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
            $table->foreign('prerequisite_id')->references('id')->on('subjects')->onDelete('cascade');

            $table->unique(['subject_id', 'prerequisite_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subject_prerequisite');
    }
};
