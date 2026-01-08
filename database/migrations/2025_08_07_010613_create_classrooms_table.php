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
        Schema::create('classrooms', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Ej: "Aula 201", "LAB-C3"
            $table->foreignId('building_id')->nullable()->constrained()->onDelete('set null');
            $table->integer('floor')->nullable(); // Piso 1, 2, 3...
            $table->integer('capacity')->nullable(); // Número máximo de estudiantes
            $table->text('description')->nullable(); // Info adicional (accesibilidad, recursos, etc.)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classrooms');
    }
};
