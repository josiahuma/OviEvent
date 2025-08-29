<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

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
        'ticket_cost',
        'is_promoted',   // <-- add this
        'public_id',     // optional to keep; created automatically below
        'ticket_currency',
    ];

    protected $casts = [
        'tags'        => 'array',
        'is_promoted' => 'boolean',
        'ticket_cost' => 'decimal:2',
    ];

    public function getCurrencySymbolAttribute(): string
    {
        return match (strtoupper($this->ticket_currency ?? 'GBP')) {
            'GBP' => '£',
            'USD' => '$',
            'EUR' => '€',
            'NGN' => '₦',
            'KES' => 'KSh',
            'GHS' => 'GH₵',
            'ZAR' => 'R',
            'CAD' => 'C$',
            'AUD' => 'A$',
            default => strtoupper($this->ticket_currency ?? 'GBP').' ',
        };
    }

    /** Use public_id in URLs/route model binding */
    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    /** Auto-generate a unique public_id when creating */
    protected static function booted()
    {
        static::creating(function (Event $event) {
            if (empty($event->public_id)) {
                do {
                    $event->public_id = (string) Str::ulid(); // or Str::uuid()
                } while (static::where('public_id', $event->public_id)->exists());
            }
        });
    }

    // Relationships
    public function sessions()       { return $this->hasMany(EventSession::class); }
    public function registrations()  { return $this->hasMany(EventRegistration::class); }
    public function unlocks()        { return $this->hasMany(\App\Models\EventUnlock::class); }
    public function payouts()        { return $this->hasMany(\App\Models\EventPayout::class); }
    public function user()           { return $this->belongsTo(\App\Models\User::class); } // used in emails
}
