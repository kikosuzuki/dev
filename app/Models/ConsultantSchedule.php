<?php

namespace App\Models;

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
}
