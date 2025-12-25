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
        Schema::table('widget_sessions', function (Blueprint $table) {
            $table->string('visitor_name')->nullable()->after('visitor_id');
            $table->string('visitor_email')->nullable()->after('visitor_name');
            $table->string('visitor_phone')->nullable()->after('visitor_email');
            $table->text('visitor_notes')->nullable()->after('visitor_phone');
            $table->json('visitor_metadata')->nullable()->after('visitor_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('widget_sessions', function (Blueprint $table) {
            $table->dropColumn([
                'visitor_name',
                'visitor_email',
                'visitor_phone',
                'visitor_notes',
                'visitor_metadata',
            ]);
        });
    }
};
