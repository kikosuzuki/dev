<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\SystemSetting;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendBookingReminders extends Command
{
    protected $signature = 'bookings:send-reminders';
    protected $description = '予約のリマインド通知を送信する（前日・当日・開始前）';

    public function handle(NotificationService $notificationService): int
    {
        // Check if reminders are enabled
        if (SystemSetting::get('reminder_enabled', '1') !== '1') {
            $this->info('Reminders are disabled.');
            return Command::SUCCESS;
        }

        $now = Carbon::now();
        $today = $now->toDateString();
        $tomorrow = $now->copy()->addDay()->toDateString();

        $dayBeforeHour = (int) SystemSetting::get('reminder_day_before_hour', 18);
        $dayOfHour = (int) SystemSetting::get('reminder_day_of_hour', 8);
        $minutesBefore = (int) SystemSetting::get('reminder_minutes_before', 10);

        // Day-before reminders
        if ($now->hour === $dayBeforeHour) {
            $bookings = Booking::where('booking_date', $tomorrow)
                ->where('status', 'approved')
                ->where('reminder_day_before_sent', false)
                ->with(['user', 'consultant.consultantProfile'])
                ->get();

            foreach ($bookings as $booking) {
                $notificationService->sendReminder($booking, 'reminder_day_before');
                $booking->update(['reminder_day_before_sent' => true]);
                $this->info("Day-before reminder sent for booking #{$booking->id}");
            }
        }

        // Day-of reminders
        if ($now->hour === $dayOfHour) {
            $bookings = Booking::where('booking_date', $today)
                ->where('status', 'approved')
                ->where('reminder_day_of_sent', false)
                ->with(['user', 'consultant.consultantProfile'])
                ->get();

            foreach ($bookings as $booking) {
                $notificationService->sendReminder($booking, 'reminder_day_of');
                $booking->update(['reminder_day_of_sent' => true]);
                $this->info("Day-of reminder sent for booking #{$booking->id}");
            }
        }

        // N-minutes-before reminders
        $laterTime = $now->copy()->addMinutes($minutesBefore)->format('H:i:s');
        $currentTime = $now->format('H:i:s');

        $bookings = Booking::where('booking_date', $today)
            ->where('status', 'approved')
            ->where('reminder_10min_sent', false)
            ->where('start_time', '>=', $currentTime)
            ->where('start_time', '<=', $laterTime)
            ->with(['user', 'consultant.consultantProfile'])
            ->get();

        foreach ($bookings as $booking) {
            $notificationService->sendReminder($booking, 'reminder_before_start');
            $booking->update(['reminder_10min_sent' => true]);
            $this->info("{$minutesBefore}-min reminder sent for booking #{$booking->id}");
        }

        return Command::SUCCESS;
    }
}
