<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            // FR-1.5: role-based access control. 'teacher' or 'student'.
            $table->enum('role', ['teacher', 'student'])->default('student');

            // FR-8.1/8.2: baseline admin substitute — the first teacher becomes
            // the lead teacher and gets elevated account-management privileges.
            $table->boolean('is_lead_teacher')->default(false);

            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();

            $table->index('role');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
