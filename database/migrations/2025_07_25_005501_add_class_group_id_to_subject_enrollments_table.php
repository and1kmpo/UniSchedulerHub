<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('subject_enrollments', function (Blueprint $table) {
            $table->foreignId('class_group_id')->nullable()->after('subject_id')->constrained()->nullOnDelete();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subject_enrollments', function (Blueprint $table) {
            //
        });
    }
};
