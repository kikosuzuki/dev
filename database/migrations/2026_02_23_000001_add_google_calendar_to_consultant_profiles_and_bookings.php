<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultant_profiles', function (Blueprint $table) {
            $table->text('google_refresh_token')->nullable()->after('reminder_message');
            $table->string('google_calendar_email')->nullable()->after('google_refresh_token');
            $table->string('google_calendar_id')->nullable()->after('google_calendar_email');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->string('consultant_google_event_id')->nullable()->after('google_event_id');
        });
    }

    public function down(): void
    {
        Schema::table('consultant_profiles', function (Blueprint $table) {
            $table->dropColumn(['google_refresh_token', 'google_calendar_email', 'google_calendar_id']);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('consultant_google_event_id');
        });
    }
};
