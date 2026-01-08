<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('subject_prerequisites');
    }

    public function down(): void
    {
        Schema::create('subject_prerequisites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')
                ->constrained('subjects')
                ->cascadeOnDelete();

            $table->foreignId('id')
                ->constrained('subjects')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['subject_id', 'id']);
        });
    }
};
