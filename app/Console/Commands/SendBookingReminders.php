<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendBookingReminders extends Command
{
    protected $signature = 'bookings:send-reminders';
    protected $description = '予約のリマインド通知を送信する（前日・当日・10分前）';

    public function handle(NotificationService $notificationService): int
    {
        $now = Carbon::now();
        $today = $now->toDateString();
        $tomorrow = $now->copy()->addDay()->toDateString();

        // Day-before reminders (send at 18:00 the day before)
        if ($now->hour === 18) {
            $bookings = Booking::where('booking_date', $tomorrow)
                ->where('status', 'approved')
                ->where('reminder_day_before_sent', false)
                ->with(['user', 'consultant'])
                ->get();

            foreach ($bookings as $booking) {
                $notificationService->sendReminder($booking, 'reminder_day_before');
                $booking->update(['reminder_day_before_sent' => true]);
                $this->info("Day-before reminder sent for booking #{$booking->id}");
            }
        }

        // Day-of reminders (send at 8:00 on the day)
        if ($now->hour === 8) {
            $bookings = Booking::where('booking_date', $today)
                ->where('status', 'approved')
                ->where('reminder_day_of_sent', false)
                ->with(['user', 'consultant'])
                ->get();

            foreach ($bookings as $booking) {
                $notificationService->sendReminder($booking, 'reminder_day_of');
                $booking->update(['reminder_day_of_sent' => true]);
                $this->info("Day-of reminder sent for booking #{$booking->id}");
            }
        }

        // 10-minute-before reminders
        $tenMinutesLater = $now->copy()->addMinutes(10)->format('H:i:s');
        $currentTime = $now->format('H:i:s');

        $bookings = Booking::where('booking_date', $today)
            ->where('status', 'approved')
            ->where('reminder_10min_sent', false)
            ->where('start_time', '>=', $currentTime)
            ->where('start_time', '<=', $tenMinutesLater)
            ->with(['user', 'consultant'])
            ->get();

        foreach ($bookings as $booking) {
            $notificationService->sendReminder($booking, 'reminder_10min');
            $booking->update(['reminder_10min_sent' => true]);
            $this->info("10-min reminder sent for booking #{$booking->id}");
        }

        return Command::SUCCESS;
    }
}
