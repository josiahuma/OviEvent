<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventUnlock extends Model
{
    protected $fillable = [
        'event_id','user_id','stripe_session_id','stripe_payment_intent_id','amount','currency','unlocked_at'
    ];

    protected $casts = [
        'unlocked_at' => 'datetime',
    ];

    public function event() { return $this->belongsTo(Event::class); }
    public function user()  { return $this->belongsTo(\App\Models\User::class); }
}
