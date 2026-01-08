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
        Schema::table('class_groups', function (Blueprint $table) {
            $table->unsignedBigInteger('academic_period_id')->after('professor_id')->nullable();

            $table->foreign('academic_period_id')
                ->references('id')
                ->on('academic_periods')
                ->onDelete('cascade');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('class_groups', function (Blueprint $table) {
            $table->dropForeign(['academic_period_id']);
            $table->dropColumn('academic_period_id');
        });
    }
};
