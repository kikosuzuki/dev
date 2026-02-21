<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE notification_logs MODIFY COLUMN channel VARCHAR(20) NOT NULL");
            DB::statement("ALTER TABLE notification_logs MODIFY COLUMN type VARCHAR(30) NOT NULL");
        } else {
            Schema::table('notification_logs', function (Blueprint $table) {
                $table->string('channel', 20)->change();
                $table->string('type', 30)->change();
            });
        }
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE notification_logs MODIFY COLUMN channel ENUM('email', 'line') NOT NULL");
            DB::statement("ALTER TABLE notification_logs MODIFY COLUMN type ENUM('reminder_day_before', 'reminder_day_of', 'reminder_10min', 'booking_confirmed', 'booking_cancelled', 'booking_rejected') NOT NULL");
        } else {
            Schema::table('notification_logs', function (Blueprint $table) {
                $table->string('channel', 20)->change();
                $table->string('type', 30)->change();
            });
        }
    }
};
