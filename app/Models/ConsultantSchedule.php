<?php

namespace App\Models;

use App\Models\SystemSetting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ConsultantSchedule extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'start_time',
        'end_time',
        'is_available',
        'calendar_blocked',
        'calendar_blocked_reason',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_available' => 'boolean',
            'calendar_blocked' => 'boolean',
        ];
    }

    public function consultant()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'schedule_id');
    }

    public function isBooked(): bool
    {
        return $this->bookings()->whereIn('status', ['pending', 'approved'])->exists();
    }

    public function isCalendarBlocked(): bool
    {
        return (bool) $this->calendar_blocked;
    }

    public function scopeNotCalendarBlocked(Builder $query): Builder
    {
        return $query->where('calendar_blocked', false);
    }

    public function isEffectivelyAvailable(): bool
    {
        return $this->is_available && !$this->calendar_blocked;
    }

    /**
     * 現在時刻より未来のスケジュールのみに絞り込む（当日の過去時間帯を除外）
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        $today = now()->toDateString();
        $currentTime = now()->format('H:i:s');

        return $query->where(function ($q) use ($today, $currentTime) {
            $q->where('date', '>', $today)
                ->orWhere(function ($q) use ($today, $currentTime) {
                    $q->where('date', '=', $today)
                        ->where('start_time', '>', $currentTime);
                });
        });
    }

    /**
     * 予約受付中のコンサルタントのスケジュールのみに絞り込む
     */
    public function scopeAcceptingBookings(Builder $query): Builder
    {
        return $query->whereHas('consultant.consultantProfile', function ($q) {
            $q->where('booking_acceptance_enabled', true);
        });
    }

    /**
     * 1日あたりの予約上限に達していないスケジュールのみに絞り込む
     */
    public function scopeWithinDailyLimit(Builder $query): Builder
    {
        $maxPerDay = (int) SystemSetting::get('max_bookings_per_day', 8);

        return $query->whereRaw(
            '(SELECT COUNT(*) FROM bookings WHERE bookings.consultant_id = consultant_schedules.user_id AND bookings.booking_date = consultant_schedules.date AND bookings.status IN (?, ?)) < ?',
            ['pending', 'approved', $maxPerDay]
        );
    }
}
