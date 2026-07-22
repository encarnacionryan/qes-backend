<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// New: an ExamSession is a live, hostable instance of an Exam that any
// logged-in student can browse server-wide (per updated requirement —
// class enrollment no longer gates exam access; visibility + password do).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();

            $table->enum('visibility', ['public', 'private'])->default('public');

            // Only set when visibility = private. Hashed like a password,
            // never returned to clients.
            $table->string('password_hash')->nullable();

            // Teacher-controlled lifecycle: students can only browse/join
            // while status = open.
            $table->enum('status', ['open', 'closed'])->default('open');

            $table->timestamps();

            $table->index(['status', 'visibility']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_sessions');
    }
};
