<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsultantProfile extends Model
{
    protected $fillable = [
        'user_id',
        'specialty',
        'bio',
        'hourly_rate',
        'experience_years',
        'photo',
        'qualifications',
        'languages',
        'auto_approve',
        'meeting_url',
        'reminder_message',
        'is_featured',
        'average_rating',
        'total_reviews',
        'total_bookings',
    ];

    protected function casts(): array
    {
        return [
            'qualifications' => 'array',
            'languages' => 'array',
            'auto_approve' => 'boolean',
            'is_featured' => 'boolean',
            'average_rating' => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function updateRating(): void
    {
        $reviews = $this->user->receivedReviews();
        $this->average_rating = $reviews->avg('rating') ?? 0;
        $this->total_reviews = $reviews->count();
        $this->save();
    }
}
