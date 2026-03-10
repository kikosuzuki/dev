<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultant_schedules', function (Blueprint $table) {
            $table->boolean('calendar_blocked')->default(false)->after('is_available');
            $table->string('calendar_blocked_reason')->nullable()->after('calendar_blocked');
        });
    }

    public function down(): void
    {
        Schema::table('consultant_schedules', function (Blueprint $table) {
            $table->dropColumn(['calendar_blocked', 'calendar_blocked_reason']);
        });
    }
};
