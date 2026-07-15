<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// FR-8.4: audit trail for account-management actions performed by the
// lead teacher (create/disable/reset), since there's no separate admin role.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_action_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('target_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('action'); // e.g. "password_reset", "account_disabled", "account_created"
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_action_logs');
    }
};
