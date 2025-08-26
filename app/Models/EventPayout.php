<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventPayout extends Model
{
    protected $fillable = [
        'event_id','user_id','amount','currency',
        'account_name','sort_code','account_number','iban',
        'status',
    ];

    public function event(){ return $this->belongsTo(Event::class); }
    public function user(){ return $this->belongsTo(\App\Models\User::class); }
}
