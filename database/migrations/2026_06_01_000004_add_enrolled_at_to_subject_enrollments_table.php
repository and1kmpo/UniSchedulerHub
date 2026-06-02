<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subject_enrollments', function (Blueprint $table) {
            if (! Schema::hasColumn('subject_enrollments', 'enrolled_at')) {
                $table->timestamp('enrolled_at')->nullable()->after('status_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('subject_enrollments', function (Blueprint $table) {
            if (Schema::hasColumn('subject_enrollments', 'enrolled_at')) {
                $table->dropColumn('enrolled_at');
            }
        });
    }
};
