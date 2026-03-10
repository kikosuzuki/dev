<?php

namespace App\Console\Commands;

use App\Models\ConsultantProfile;
use App\Models\ConsultantSchedule;
use App\Services\GoogleCalendarService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncCalendarConflicts extends Command
{
    protected $signature = 'calendar:sync-conflicts';
    protected $description = 'Googleカレンダーの予定と予約枠の重複をチェックし、自動ブロック/解除を行う';

    public function handle(GoogleCalendarService $googleService): int
    {
        $profiles = ConsultantProfile::whereNotNull('google_refresh_token')
            ->whereNotNull('google_conflict_calendar_ids')
            ->get();

        $totalBlocked = 0;
        $totalUnblocked = 0;
        $errors = 0;

        foreach ($profiles as $profile) {
            $calendarIds = $profile->getConflictCalendarIds();
            if (empty($calendarIds)) {
                continue;
            }

            try {
                $this->processConsultant($googleService, $profile, $calendarIds, $totalBlocked, $totalUnblocked);
            } catch (\Exception $e) {
                $errors++;
                Log::warning('Calendar sync failed for consultant', [
                    'user_id' => $profile->user_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("完了: {$totalBlocked}件ブロック, {$totalUnblocked}件解除, {$errors}件エラー");

        Log::info('Calendar conflict sync completed', [
            'blocked' => $totalBlocked,
            'unblocked' => $totalUnblocked,
            'errors' => $errors,
        ]);

        return self::SUCCESS;
    }

    private function processConsultant(
        GoogleCalendarService $googleService,
        ConsultantProfile $profile,
        array $calendarIds,
        int &$totalBlocked,
        int &$totalUnblocked
    ): void {
        $today = Carbon::today()->toDateString();

        // Get future schedules that are either available or already calendar-blocked
        $schedules = ConsultantSchedule::where('user_id', $profile->user_id)
            ->where('date', '>=', $today)
            ->where(function ($q) {
                $q->where('is_available', true)
                    ->orWhere('calendar_blocked', true);
            })
            ->get();

        if ($schedules->isEmpty()) {
            return;
        }

        // Determine date range for API call
        $minDate = $schedules->min('date');
        $maxDate = $schedules->max('date');
        $timeMin = Carbon::parse($minDate)->format('Y-m-d') . 'T00:00:00+09:00';
        $timeMax = Carbon::parse($maxDate)->format('Y-m-d') . 'T23:59:59+09:00';

        // Fetch all events at once
        $events = $googleService->listEventsFromMultipleCalendars(
            $profile->google_refresh_token,
            $calendarIds,
            $timeMin,
            $timeMax
        );

        // Build slot array for conflict checking
        $slots = $schedules->map(fn ($s) => [
            'date' => $s->date->format('Y-m-d'),
            'start_time' => $s->start_time,
            'end_time' => $s->end_time,
        ])->toArray();

        $results = $googleService->findConflicts($events, $slots);

        // Update each schedule
        foreach ($schedules as $index => $schedule) {
            $hasConflict = $results[$index]['has_conflict'] ?? false;
            $conflictReason = null;

            if ($hasConflict && !empty($results[$index]['conflicting_events'])) {
                $conflictReason = $results[$index]['conflicting_events'][0]['summary'] ?? '(予定)';
            }

            if ($hasConflict && !$schedule->calendar_blocked) {
                // Block: new conflict found
                $schedule->update([
                    'calendar_blocked' => true,
                    'calendar_blocked_reason' => $conflictReason,
                ]);
                $totalBlocked++;
            } elseif (!$hasConflict && $schedule->calendar_blocked) {
                // Unblock: conflict resolved (auto-recovery)
                $schedule->update([
                    'calendar_blocked' => false,
                    'calendar_blocked_reason' => null,
                ]);
                $totalUnblocked++;
            } elseif ($hasConflict && $schedule->calendar_blocked && $schedule->calendar_blocked_reason !== $conflictReason) {
                // Update reason if changed
                $schedule->update([
                    'calendar_blocked_reason' => $conflictReason,
                ]);
            }
        }
    }
}
