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
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_available' => 'boolean',
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
