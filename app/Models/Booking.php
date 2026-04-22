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
        'admin_google_calendar_id',
        'consultant_google_event_id',
        'consultant_google_calendar_id',
        'meeting_url',
        'amount',
        'is_guest',
        'guest_name',
        'guest_email',
        'guest_phone',
        'guest_referrer',
        'consultation_result',
        'consultation_notes',
        'important_document_issued',
        'consultation_record_reset_at',
        'admin_notes',
        'reminder_day_before_sent',
        'reminder_day_of_sent',
        'reminder_10min_sent',
        'morning_chatwork_sent',
    ];

    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
            'is_guest' => 'boolean',
            'important_document_issued' => 'boolean',
            'consultation_record_reset_at' => 'datetime',
            'reminder_day_before_sent' => 'boolean',
            'reminder_day_of_sent' => 'boolean',
            'reminder_10min_sent' => 'boolean',
            'morning_chatwork_sent' => 'boolean',
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
        return $this->status === 'approved';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * 相談記録を入力可能か。
     * - 完了状態：既存記録の編集のため可。
     * - 確定済み：予約開始日時を過ぎたときのみ可（例：4/22 18:00の予約は 4/22 18:00 以降で入力可）。
     */
    public function canEnterConsultationRecord(): bool
    {
        if ($this->status === 'completed') {
            return true;
        }

        if ($this->status === 'approved') {
            $bookingStart = $this->booking_date->copy()->setTimeFromTimeString($this->start_time);
            return now()->greaterThanOrEqualTo($bookingStart);
        }

        return false;
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
