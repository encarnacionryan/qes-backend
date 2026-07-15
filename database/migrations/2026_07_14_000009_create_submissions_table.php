<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();

            $table->dateTime('started_at');
            $table->dateTime('submitted_at')->nullable();

            $table->enum('status', ['in_progress', 'submitted', 'graded'])->default('in_progress');

            // FR-4.4: attempt number so a retake (if allowed) doesn't collide
            // with the unique constraint below.
            $table->unsignedInteger('attempt_number')->default(1);

            $table->timestamps();

            $table->unique(['exam_id', 'student_id', 'attempt_number']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
