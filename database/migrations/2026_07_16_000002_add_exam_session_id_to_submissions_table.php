<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('submissions', 'exam_session_id')) {
            Schema::table('submissions', function (Blueprint $table) {
                $table->foreignId('exam_session_id')->nullable()->after('exam_id')
                    ->constrained()->cascadeOnDelete();
            });
        }

        $indexes = collect(Schema::getIndexes('submissions'))->pluck('name');

        if (! $indexes->contains('submissions_exam_id_index')) {
            Schema::table('submissions', function (Blueprint $table) {
                $table->index('exam_id');
            });
            $indexes->push('submissions_exam_id_index');
        }

        if ($indexes->contains('submissions_exam_id_student_id_attempt_number_unique')) {
            Schema::table('submissions', function (Blueprint $table) {
                $table->dropUnique(['exam_id', 'student_id', 'attempt_number']);
            });
        }

        if (! $indexes->contains('submissions_exam_session_id_student_id_attempt_number_unique')) {
            Schema::table('submissions', function (Blueprint $table) {
                $table->unique(['exam_session_id', 'student_id', 'attempt_number']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropUnique(['exam_session_id', 'student_id', 'attempt_number']);
            $table->unique(['exam_id', 'student_id', 'attempt_number']);
            $table->dropIndex(['exam_id']);
            $table->dropConstrainedForeignId('exam_session_id');
        });
    }
};
