<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('subject_enrollments', function (Blueprint $table) {
            // 1. Eliminar FK previa sobre class_group_id
            $table->dropForeign(['class_group_id']);
        });

        Schema::table('subject_enrollments', function (Blueprint $table) {
            // 2. Cambiar a NOT NULL
            $table->unsignedBigInteger('class_group_id')->nullable(false)->change();

            // 3. Crear FK nueva (ya no SET NULL, sino CASCADE o RESTRICT)
            $table->foreign('class_group_id')
                ->references('id')->on('class_groups')
                ->cascadeOnDelete();

            // Auditoría básica
            $table->unsignedBigInteger('enrolled_by')->nullable()->after('updated_at');
            $table->unsignedBigInteger('cancelled_by')->nullable()->after('enrolled_by');
            $table->timestamp('cancelled_at')->nullable()->after('cancelled_by');

            // FKs opcionales
            $table->foreign('enrolled_by')
                ->references('id')->on('users')
                ->nullOnDelete();

            $table->foreign('cancelled_by')
                ->references('id')->on('users')
                ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('subject_enrollments', function (Blueprint $table) {
            // Revertir: eliminar nuevas FKs
            $table->dropForeign(['class_group_id']);
            $table->dropForeign(['enrolled_by']);
            $table->dropForeign(['cancelled_by']);

            // Volver a nullable
            $table->unsignedBigInteger('class_group_id')->nullable()->change();

            // Restaurar FK con SET NULL
            $table->foreign('class_group_id')
                ->references('id')->on('class_groups')
                ->nullOnDelete();

            // Eliminar columnas de auditoría
            $table->dropColumn(['enrolled_by', 'cancelled_by', 'cancelled_at']);
        });
    }
};
