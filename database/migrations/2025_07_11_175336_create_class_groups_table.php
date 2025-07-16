<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('class_groups', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Ej: MATH-2025-I-A
            $table->string('name');           // Ej: Math I - Virtual - Night - Group A
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('professor_id');
            $table->string('semester');       // Ej: 2025-I
            $table->string('group_code');     // A, B, C (no único globalmente)
            $table->string('modality')->default('Presential'); // New
            $table->string('shift')->default('Day');            // New
            $table->integer('capacity')->default(30);
            $table->timestamps();

            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
            $table->foreign('professor_id')->references('id')->on('users')->onDelete('cascade');
            // O puedes hacer una clave única compuesta si lo deseas:
            // $table->unique(['subject_id', 'semester', 'group_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_groups');
    }
};
