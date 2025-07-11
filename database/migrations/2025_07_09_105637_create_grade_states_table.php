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
        Schema::create('grade_states', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // ej: passed, failed
            $table->string('label'); // ej: Aprobado, Reprobado
            $table->timestamps();
        });

        Schema::table('grades', function (Blueprint $table) {
            $table->unsignedBigInteger('grade_state_id')->nullable()->after('final_grade');
            $table->foreign('grade_state_id')->references('id')->on('grade_states');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grade_states');
    }
};
