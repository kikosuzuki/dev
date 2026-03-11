<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('consultant_profiles', 'sync_available_slots')) {
            Schema::table('consultant_profiles', function (Blueprint $table) {
                $table->dropColumn('sync_available_slots');
            });
        }

        if (Schema::hasColumn('consultant_schedules', 'google_event_id')) {
            Schema::table('consultant_schedules', function (Blueprint $table) {
                $table->dropColumn('google_event_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('consultant_profiles', function (Blueprint $table) {
            $table->boolean('sync_available_slots')->default(false)->after('google_conflict_calendar_ids');
        });

        Schema::table('consultant_schedules', function (Blueprint $table) {
            $table->string('google_event_id')->nullable()->after('calendar_blocked_reason');
        });
    }
};
