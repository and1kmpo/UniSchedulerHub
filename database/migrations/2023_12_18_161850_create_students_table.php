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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('document', 20)->unique();
            $table->string('email')->unique();
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->string('phone', 15);
            $table->string('address', 255);
            $table->string('city', 50);
            $table->integer('semester');
            $table->foreignId('program_id')->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
