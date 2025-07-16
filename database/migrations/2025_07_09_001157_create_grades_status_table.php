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
        Schema::create('grade_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // e.g., passed, failed
            $table->string('label'); // e.g., Aprobado, Reprobado
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_statuses');
    }
};
