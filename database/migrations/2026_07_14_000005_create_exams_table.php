<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('time_limit_minutes');
            $table->unsignedInteger('total_points')->default(0); // recomputed from questions

            // FR-3.5: draft until explicitly published.
            $table->enum('status', ['draft', 'published', 'closed'])->default('draft');

            // FR-5.3: teacher may delay score release until the exam window closes.
            $table->boolean('show_score_immediately')->default(true);

            // FR-4.4: retakes disallowed unless the teacher explicitly enables it.
            $table->boolean('allow_retake')->default(false);

            // FR-6.4: leaderboard display toggle.
            $table->boolean('anonymize_leaderboard')->default(false);

            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
