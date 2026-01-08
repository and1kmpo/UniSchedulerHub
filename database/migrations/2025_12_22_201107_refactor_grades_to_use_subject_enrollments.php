<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            if (!Schema::hasColumn('grades', 'subject_enrollment_id')) {
                $table->foreignId('subject_enrollment_id')
                    ->after('id')
                    ->constrained()
                    ->cascadeOnDelete();
            }

            // Solo agregar la nueva unicidad
            $table->unique('subject_enrollment_id');
        });
    }

    public function down(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->dropUnique(['subject_enrollment_id']);
        });
    }
};
