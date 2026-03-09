<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleRequest extends Model
{
    protected $fillable = [
        'guest_name',
        'guest_email',
        'guest_phone',
        'candidate_1',
        'candidate_2',
        'candidate_3',
        'message',
        'status',
        'admin_notes',
    ];

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isProcessed(): bool
    {
        return $this->status === 'processed';
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessed($query)
    {
        return $query->where('status', 'processed');
    }
}
