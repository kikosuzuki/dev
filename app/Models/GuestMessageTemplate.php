<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuestMessageTemplate extends Model
{
    protected $fillable = ['name', 'subject', 'body', 'sort_order'];

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
