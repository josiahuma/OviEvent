<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventSession extends Model
{
    //
    protected $fillable = ['event_id', 'session_name', 'session_date'];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
