<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leaderboard_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('score_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('rank');
            $table->unsignedInteger('completion_seconds');
            $table->timestamps();
            $table->unique(['exam_id', 'student_id']);
            $table->index(['exam_id', 'rank']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leaderboard_entries');
    }
};
