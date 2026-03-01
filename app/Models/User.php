<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public function sendPasswordResetNotification($token)
    {
        $url = url(route('password.reset', ['token' => $token, 'email' => $this->email], false));

        Mail::raw(
            "{$this->name}様\n\n"
            . "パスワードリセットのリクエストを受け付けました。\n\n"
            . "以下のリンクからパスワードを再設定してください：\n"
            . "{$url}\n\n"
            . "このリンクは60分間有効です。\n"
            . "リクエストした覚えがない場合は、このメールを無視してください。",
            function ($message) {
                $message->to($this->email)
                    ->subject('【パスワードリセット】YCSコンサルタント予約システム');
            }
        );
    }

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
        'notify_email',
        'notify_line',
        'notify_chatwork',
        'is_active',
        'admin_notes',
        'user_type',
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
            'notify_email' => 'boolean',
            'notify_line' => 'boolean',
            'notify_chatwork' => 'boolean',
        ];
    }

    public function getAvatarUrl(): ?string
    {
        if (!$this->avatar) {
            return null;
        }

        $url = url('media/' . $this->avatar);

        if ($this->updated_at) {
            $url .= '?v=' . $this->updated_at->timestamp;
        }

        return $url;
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
