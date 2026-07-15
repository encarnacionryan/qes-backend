<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();

            // FR-3.2: the four supported auto-gradable question types.
            $table->enum('type', ['mcq', 'true_false', 'matching', 'identification']);

            $table->text('prompt');
            $table->unsignedInteger('points')->default(1);
            $table->unsignedInteger('order')->default(0);

            // FR-3.3: answer key for auto-gradable types that don't need a
            // separate `choices` row (true_false, identification).
            // e.g. {"answer": "true"} or {"answer": "Mitochondria"}
            $table->json('answer_key')->nullable();

            $table->timestamps();

            $table->index(['exam_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
