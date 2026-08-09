<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('account_action_logs', function (Blueprint $table) {
            $table->dropForeign(['actor_id']);
            $table->dropForeign(['target_user_id']);
            $table->dropColumn(['actor_id', 'target_user_id']);
        });

        Schema::table('account_action_logs', function (Blueprint $table) {
            $table->foreignId('actor_id')->nullable()->after('id')
                ->constrained('users')->nullOnDelete();
            $table->foreignId('target_user_id')->nullable()->after('actor_id')
                ->constrained('users')->nullOnDelete();
            $table->string('target_label')->nullable()->after('target_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('account_action_logs', function (Blueprint $table) {
            $table->dropForeign(['actor_id']);
            $table->dropForeign(['target_user_id']);
            $table->dropColumn(['actor_id', 'target_user_id', 'target_label']);
        });

        Schema::table('account_action_logs', function (Blueprint $table) {
            $table->foreignId('actor_id')->after('id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('target_user_id')->after('actor_id')->constrained('users')->cascadeOnDelete();
        });
    }
};
