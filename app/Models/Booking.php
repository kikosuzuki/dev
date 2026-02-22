<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'consultant_id',
        'schedule_id',
        'booking_date',
        'start_time',
        'end_time',
        'status',
        'notes',
        'cancel_reason',
        'google_event_id',
        'meeting_url',
        'amount',
        'is_guest',
        'guest_name',
        'guest_email',
        'guest_phone',
        'guest_referrer',
        'reminder_day_before_sent',
        'reminder_day_of_sent',
        'reminder_10min_sent',
    ];

    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
            'is_guest' => 'boolean',
            'reminder_day_before_sent' => 'boolean',
            'reminder_day_of_sent' => 'boolean',
            'reminder_10min_sent' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function consultant()
    {
        return $this->belongsTo(User::class, 'consultant_id');
    }

    public function schedule()
    {
        return $this->belongsTo(ConsultantSchedule::class, 'schedule_id');
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }

    public function canCancel(): bool
    {
        return in_array($this->status, ['pending', 'approved']);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isGuest(): bool
    {
        return (bool) $this->is_guest;
    }

    public function bookerName(): string
    {
        return $this->is_guest ? $this->guest_name : ($this->user->name ?? '不明');
    }

    public function bookerEmail(): string
    {
        return $this->is_guest ? $this->guest_email : ($this->user->email ?? '');
    }
}
