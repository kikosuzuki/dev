<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // 相談記録のリカバリー（初期化/確定へ差戻し）を行った日時。
            // この値より後のConsultationRecord通知ログが無ければ、再入力時に通知する。
            $table->timestamp('consultation_record_reset_at')->nullable()->after('important_document_issued');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('consultation_record_reset_at');
        });
    }
};
