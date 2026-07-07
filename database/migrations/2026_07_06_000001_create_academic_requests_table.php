<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('subject_enrollment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('class_group_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 60);
            $table->string('status', 40)->default('submitted');
            $table->string('title');
            $table->text('description');
            $table->text('decision_reason')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'status']);
            $table->index(['type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_requests');
    }
};
