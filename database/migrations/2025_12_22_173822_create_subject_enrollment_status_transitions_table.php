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
        Schema::create('subject_enrollment_status_transitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_status_id')
                ->constrained('subject_enrollment_statuses')
                ->cascadeOnDelete();

            $table->foreignId('to_status_id')
                ->constrained('subject_enrollment_statuses')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(
                ['from_status_id', 'to_status_id'],
                'ses_transitions_from_to_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subject_enrollment_status_transitions');
    }
};
