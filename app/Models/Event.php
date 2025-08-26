<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Event extends Model
{
      use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'organizer',
        'category',
        'tags',
        'location',
        'description',
        'avatar_url',
        'banner_url',
        'ticket_cost'
    ];

    protected $casts = [
        'tags' => 'array',
        'is_promoted' => 'boolean',
        'ticket_cost' => 'decimal:2',
    ];

    public function sessions()
    {
        return $this->hasMany(EventSession::class);
    }

    public function registrations()
    {
        return $this->hasMany(EventRegistration::class);
    }

    // ...
    public function unlocks() 
    { 
        return $this->hasMany(\App\Models\EventUnlock::class); 
    }

    public function payouts(){ return $this->hasMany(\App\Models\EventPayout::class); }


}
