<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventRegistration extends Model
{
    //
    protected $fillable = [
        'event_id','user_id','name','email','mobile','status','stripe_session_id','amount',
    ];

    public function event() {
        return $this->belongsTo(Event::class);
    }

    public function sessions() {
        return $this->belongsToMany(EventSession::class, 'event_registration_session');
    }
}
