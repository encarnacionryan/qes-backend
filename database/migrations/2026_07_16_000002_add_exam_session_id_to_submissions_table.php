<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// A submission now happens within a specific session join (so a teacher
// re-hosting the same Exam in two sessions produces separate, trackable
// attempts). exam_id is kept alongside for convenient querying/reporting.
return new class extends Migration
{
    public function up(): void
    {
        // Guarded with hasColumn/existing-index checks so this migration
        // is safe to re-run if a previous attempt failed partway through
        // (e.g. after the column was added but before the constraint swap).
        if (! Schema::hasColumn('submissions', 'exam_session_id')) {
            Schema::table('submissions', function (Blueprint $table) {
                $table->foreignId('exam_session_id')->nullable()->after('exam_id')
                    ->constrained()->cascadeOnDelete();
            });
        }

        $indexes = collect(\Illuminate\Support\Facades\DB::select("SHOW INDEX FROM submissions"))
            ->pluck('Key_name')->unique();

        // MySQL was using the composite unique index below as the backing
        // index for the exam_id foreign key (no other index started with
        // exam_id alone) — it refuses to drop it without a replacement in
        // place first. Add a plain index on exam_id so the FK has
        // something else to lean on.
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
