<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subject_prerequisites', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subject_id');        // materia principal
            $table->unsignedBigInteger('id');   // su prerrequisito
            $table->timestamps();

            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
            $table->foreign('id')->references('id')->on('subjects')->onDelete('cascade');

            $table->unique(['subject_id', 'id']);
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
