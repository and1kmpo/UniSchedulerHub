<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('student_subject_professor');
    }

    public function down(): void
    {
        // Legacy pivot intentionally not recreated. Official assignments use
        // subject_enrollments linked to class_groups.
    }
};
