<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('class_groups', function (Blueprint $table) {
            if (! Schema::hasColumn('class_groups', 'status')) {
                $table->string('status', 20)->default('published')->after('shift');
            }
        });

        Schema::table('class_schedules', function (Blueprint $table) {
            if (! Schema::hasColumn('class_schedules', 'status')) {
                $table->string('status', 20)->default('published')->after('classroom_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('class_schedules', function (Blueprint $table) {
            if (Schema::hasColumn('class_schedules', 'status')) {
                $table->dropColumn('status');
            }
        });

        Schema::table('class_groups', function (Blueprint $table) {
            if (Schema::hasColumn('class_groups', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
