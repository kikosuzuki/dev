<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Split booking_confirm_message into separate email/LINE fields.
     * The existing value is migrated to booking_confirm_email_body.
     */
    public function up(): void
    {
        // Rename booking_confirm_message → booking_confirm_email_body
        DB::table('system_settings')
            ->where('key', 'booking_confirm_message')
            ->update(['key' => 'booking_confirm_email_body']);

        // cancel_notification_message → cancel_notification_email_body (if exists)
        DB::table('system_settings')
            ->where('key', 'cancel_notification_message')
            ->update(['key' => 'cancel_notification_email_body']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('system_settings')
            ->where('key', 'booking_confirm_email_body')
            ->update(['key' => 'booking_confirm_message']);

        DB::table('system_settings')
            ->where('key', 'cancel_notification_email_body')
            ->update(['key' => 'cancel_notification_message']);

        // Clean up the new split keys
        DB::table('system_settings')
            ->whereIn('key', [
                'booking_confirm_email_subject',
                'booking_confirm_line_message',
                'cancel_notification_email_subject',
                'cancel_notification_line_message',
            ])->delete();
    }
};
