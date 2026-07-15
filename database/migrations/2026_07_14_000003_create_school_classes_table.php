<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Named `school_classes` rather than `classes` to avoid clashing with the
// reserved PHP keyword `class` when referenced loosely in code/docs.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('subject')->nullable();
            $table->string('section')->nullable();

            // FR-2.2: unique join code students use to enroll.
            $table->string('join_code', 8)->unique();

            $table->boolean('is_archived')->default(false); // FR-2.5
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_classes');
    }
};
