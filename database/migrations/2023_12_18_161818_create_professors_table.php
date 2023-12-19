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
        Schema::create('professors', function (Blueprint $table) {
            $table->id();
            $table->string('document', 20)->unique();
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->string('phone', 15);
            $table->string('email', 100)->unique();
            $table->string('address', 255);
            $table->string('city', 50);
            $table->longText('picture');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('professors');
    }
};
