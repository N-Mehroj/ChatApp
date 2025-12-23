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
        Schema::create('widget_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique();
            $table->foreignId('merchant_id')->constrained('users')->onDelete('cascade');
            $table->string('visitor_id')->nullable();
            $table->ipAddress('visitor_ip')->nullable();
            $table->text('visitor_user_agent')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('visitor_typing')->default(false);
            $table->timestamp('last_activity')->nullable();
            $table->timestamp('last_typing_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->index(['merchant_id', 'session_id']);
            $table->index(['merchant_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('widget_sessions');
    }
};
