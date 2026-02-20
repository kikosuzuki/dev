<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultant_profiles', function (Blueprint $table) {
            $table->string('meeting_url', 500)->nullable()->after('auto_approve');
            $table->text('reminder_message')->nullable()->after('meeting_url');
        });
    }

    public function down(): void
    {
        Schema::table('consultant_profiles', function (Blueprint $table) {
            $table->dropColumn(['meeting_url', 'reminder_message']);
        });
    }
};
