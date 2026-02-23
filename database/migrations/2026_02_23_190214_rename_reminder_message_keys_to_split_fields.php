<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Split reminder_*_message into separate email/LINE fields.
     * Existing values are migrated to *_email_body.
     */
    public function up(): void
    {
        $renames = [
            'reminder_day_before_message' => 'reminder_day_before_email_body',
            'reminder_day_of_message' => 'reminder_day_of_email_body',
            'reminder_minutes_before_message' => 'reminder_minutes_before_email_body',
        ];

        foreach ($renames as $old => $new) {
            DB::table('system_settings')
                ->where('key', $old)
                ->update(['key' => $new]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $renames = [
            'reminder_day_before_email_body' => 'reminder_day_before_message',
            'reminder_day_of_email_body' => 'reminder_day_of_message',
            'reminder_minutes_before_email_body' => 'reminder_minutes_before_message',
        ];

        foreach ($renames as $old => $new) {
            DB::table('system_settings')
                ->where('key', $old)
                ->update(['key' => $new]);
        }

        // Clean up new split keys
        DB::table('system_settings')
            ->whereIn('key', [
                'reminder_day_before_email_subject',
                'reminder_day_before_line_message',
                'reminder_day_of_email_subject',
                'reminder_day_of_line_message',
                'reminder_minutes_before_email_subject',
                'reminder_minutes_before_line_message',
            ])->delete();
    }
};
