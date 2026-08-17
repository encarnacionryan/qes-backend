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
            $table->unsignedInteger('total_points')->default(0); 
            $table->enum('status', ['draft', 'published', 'closed'])->default('draft');
            $table->boolean('show_score_immediately')->default(true);
            $table->boolean('allow_retake')->default(false);
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
