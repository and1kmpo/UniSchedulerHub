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
        Schema::create('enrollment_overrides', function (Blueprint $table) {
            $table->id();

            $table->foreignId('subject_enrollment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('admin_id')->constrained('users');

            $table->string('reason');
            $table->string('blocked_code');
            $table->json('meta')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollment_overrides');
    }
};
