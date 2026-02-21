<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * 過剰なスケジュールデータを削減する。
     * 各コンサルタントにつき、直近5営業日・1日2スロットのみ残す。
     */
    public function up(): void
    {
        // Step 1: 保持するスケジュールIDを特定
        // 各コンサルタントごとに日付・時間順で上位10件（5日×2スロット）のみ残す
        $consultantIds = DB::table('consultant_schedules')
            ->distinct()
            ->pluck('user_id');

        $keepIds = collect();

        foreach ($consultantIds as $consultantId) {
            $ids = DB::table('consultant_schedules')
                ->where('user_id', $consultantId)
                ->where('date', '>=', now()->toDateString())
                ->where('is_available', true)
                ->orderBy('date')
                ->orderBy('start_time')
                ->limit(10) // 5 days × 2 slots per day
                ->pluck('id');

            $keepIds = $keepIds->merge($ids);
        }

        // Step 2: 保持対象外のスケジュールを削除
        if ($keepIds->isNotEmpty()) {
            DB::table('consultant_schedules')
                ->whereNotIn('id', $keepIds->toArray())
                ->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Data migration - not reversible
    }
};
