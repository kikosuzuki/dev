<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('notify_email')->default(true)->after('notification_channel');
            $table->boolean('notify_line')->default(false)->after('notify_email');
        });

        // Migrate existing data
        DB::table('users')->where('notification_channel', 'email')->update(['notify_email' => true, 'notify_line' => false]);
        DB::table('users')->where('notification_channel', 'line')->update(['notify_email' => false, 'notify_line' => true]);
        DB::table('users')->where('notification_channel', 'both')->update(['notify_email' => true, 'notify_line' => true]);

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('notification_channel');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('notification_channel', ['email', 'line', 'both'])->default('email')->after('chatwork_room_id');
        });

        DB::table('users')->where('notify_email', true)->where('notify_line', true)->update(['notification_channel' => 'both']);
        DB::table('users')->where('notify_email', true)->where('notify_line', false)->update(['notification_channel' => 'email']);
        DB::table('users')->where('notify_email', false)->where('notify_line', true)->update(['notification_channel' => 'line']);

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['notify_email', 'notify_line']);
        });
    }
};
