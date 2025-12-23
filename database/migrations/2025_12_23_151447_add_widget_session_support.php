<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('chats', function (Blueprint $table) {
            $table->foreignId('widget_session_id')->nullable()->constrained('widget_sessions')->onDelete('cascade');
            $table->index('widget_session_id');
        });

        Schema::table('chat_messages', function (Blueprint $table) {
            $table->foreignId('widget_session_id')->nullable()->constrained('widget_sessions')->onDelete('cascade');
            $table->index('widget_session_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('api_key')->unique()->nullable();
            $table->enum('role', ['user', 'merchant', 'admin'])->default('user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['api_key', 'role']);
        });

        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropForeign(['widget_session_id']);
            $table->dropColumn('widget_session_id');
        });

        Schema::table('chats', function (Blueprint $table) {
            $table->dropForeign(['widget_session_id']);
            $table->dropColumn('widget_session_id');
        });
    }
};
