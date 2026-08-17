<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_class', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained()->cascadeOnDelete();
            $table->dateTime('opens_at');
            $table->dateTime('closes_at');
            $table->timestamps();

            $table->unique(['exam_id', 'school_class_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_class');
    }
};
