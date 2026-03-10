<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guest_message_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('subject')->nullable();
            $table->text('body')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Migrate existing templates from system_settings to the new table
        $templates = [
            [
                'key_prefix' => 'guest_email_tpl_confirm',
                'default_name' => '予約確認',
                'default_subject' => '【予約確認】個別相談のご予約について',
                'default_body' => "{name}様\n\nご予約の確認をお願いいたします。\n\n■ 日時: {date}\n\nご不明な点がございましたらお気軽にご連絡ください。",
            ],
            [
                'key_prefix' => 'guest_email_tpl_remind',
                'default_name' => 'リマインド',
                'default_subject' => '【リマインド】個別相談のご予約について',
                'default_body' => "{name}様\n\n個別相談の予約日時が近づいてまいりました。\n\n■ 日時: {date}\n\nご準備のほどよろしくお願いいたします。",
            ],
            [
                'key_prefix' => 'guest_email_tpl_followup',
                'default_name' => 'フォローアップ',
                'default_subject' => '【フォローアップ】個別相談について',
                'default_body' => "{name}様\n\n先日の個別相談はいかがでしたでしょうか。\nご不明な点やご質問がございましたらお気軽にお問い合わせください。",
            ],
            [
                'key_prefix' => 'guest_email_tpl_notice',
                'default_name' => 'お知らせ',
                'default_subject' => '【お知らせ】',
                'default_body' => "{name}様\n\nお知らせがございます。\n詳細につきましては下記をご確認ください。\n\n",
            ],
        ];

        foreach ($templates as $index => $tpl) {
            $subject = DB::table('system_settings')
                ->where('key', $tpl['key_prefix'] . '_subject')
                ->value('value') ?? $tpl['default_subject'];

            $body = DB::table('system_settings')
                ->where('key', $tpl['key_prefix'] . '_body')
                ->value('value') ?? $tpl['default_body'];

            DB::table('guest_message_templates')->insert([
                'name' => $tpl['default_name'],
                'subject' => $subject,
                'body' => $body,
                'sort_order' => $index,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Clean up old system_settings keys
        DB::table('system_settings')->whereIn('key', [
            'guest_email_tpl_confirm_subject',
            'guest_email_tpl_confirm_body',
            'guest_email_tpl_remind_subject',
            'guest_email_tpl_remind_body',
            'guest_email_tpl_followup_subject',
            'guest_email_tpl_followup_body',
            'guest_email_tpl_notice_subject',
            'guest_email_tpl_notice_body',
            'booking_slot_duration',
            'schedule_per_page',
            'guest_schedule_disclosure_days',
            'reminder_enabled',
            'default_reminder_message',
            'guest_reminder_message',
        ])->delete();

        // Rename guest_booking_confirmation_message to booking_confirm_message
        DB::table('system_settings')
            ->where('key', 'guest_booking_confirmation_message')
            ->update(['key' => 'booking_confirm_message']);
    }

    public function down(): void
    {
        // Rename back
        DB::table('system_settings')
            ->where('key', 'booking_confirm_message')
            ->update(['key' => 'guest_booking_confirmation_message']);

        Schema::dropIfExists('guest_message_templates');
    }
};
