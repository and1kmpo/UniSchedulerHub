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
        Schema::table('academic_periods', function (Blueprint $table) {
            $table->date('unenrollment_deadline')->nullable()->after('end_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('academic_periods', function (Blueprint $table) {
            $table->dropColumn('unenrollment_deadline');
        });
    }
};
