<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'avatar',
        'line_user_id',
        'chatwork_id',
        'chatwork_room_id',
        'notification_channel',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isConsultant(): bool
    {
        return $this->role === 'consultant';
    }

    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    public function consultantProfile()
    {
        return $this->hasOne(ConsultantProfile::class);
    }

    public function schedules()
    {
        return $this->hasMany(ConsultantSchedule::class);
    }

    public function bookingsAsUser()
    {
        return $this->hasMany(Booking::class, 'user_id');
    }

    public function bookingsAsConsultant()
    {
        return $this->hasMany(Booking::class, 'consultant_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'user_id');
    }

    public function receivedReviews()
    {
        return $this->hasMany(Review::class, 'consultant_id');
    }

    public function favorites()
    {
        return $this->belongsToMany(User::class, 'favorites', 'user_id', 'consultant_id')->withTimestamps();
    }

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorites', 'consultant_id', 'user_id')->withTimestamps();
    }
}
