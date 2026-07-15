<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();

            // Student's raw response. JSON so it can hold a single value
            // (MCQ/true-false/identification) or a set of pairs (matching).
            $table->json('response')->nullable();

            // Null until graded (FR-5.4: pending manual review for
            // non-auto-gradable types in a future version).
            $table->boolean('is_correct')->nullable();
            $table->decimal('points_earned', 6, 2)->default(0);

            $table->timestamps();

            $table->unique(['submission_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('answers');
    }
};
