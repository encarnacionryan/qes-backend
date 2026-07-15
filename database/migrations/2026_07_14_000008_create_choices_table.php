<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('choices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();

            $table->string('label');           // e.g. "Paris" (MCQ option / matching left side)
            $table->string('match_value')->nullable(); // matching right-side pair, null for MCQ
            $table->boolean('is_correct')->default(false); // used for MCQ only
            $table->unsignedInteger('order')->default(0);

            $table->timestamps();

            $table->index('question_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('choices');
    }
};
