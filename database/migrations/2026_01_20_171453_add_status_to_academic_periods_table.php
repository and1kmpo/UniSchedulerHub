<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('academic_periods', function (Blueprint $table) {
            $table->foreignId('academic_period_status_id')
                ->nullable()
                ->constrained('academic_period_statuses');

            $table->timestamp('status_changed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('academic_periods', function (Blueprint $table) {
            $table->dropForeign(['academic_period_status_id']);
            $table->dropColumn([
                'academic_period_status_id',
                'status_changed_at'
            ]);
        });
    }
};
