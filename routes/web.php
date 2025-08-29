<?php

use App\Http\Controllers\EventController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\RegistrantEmailController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\RegistrantsController;
use App\Http\Controllers\PayoutController;
use App\Models\Event as EventModel;
use App\Http\Controllers\TicketLookupController;
use App\Http\Controllers\MyTicketsController;
use App\Http\Controllers\SocialAuthController;

/**
 * Legacy numeric ID redirect (301).
 * Works for /events/14, /events/14/register, /events/14/avatar, etc.
 * Preserves any query string.
 */
// --- Legacy numeric ID redirect (must stay ABOVE other /events/* routes) ---
Route::get('/events/{id}/{tail?}', function (int $id, $tail = null) {
    $event = EventModel::query()->select('public_id')->find($id); // 👈 use EventModel
    abort_unless($event && $event->public_id, 404);

    $base = '/events/'.$event->public_id.($tail ? '/'.$tail : '');
    $qs   = request()->getQueryString();

    return redirect($base.($qs ? '?'.$qs : ''), 301);
})->whereNumber('id')->where('tail', '.*');

// Homepage - public listing
Route::get('/', [EventController::class, 'publicIndex'])->name('homepage');

// Marketing pages
Route::get('/how-it-works', [PageController::class, 'how'])->name('how');
Route::get('/pricing', [PageController::class, 'pricing'])->name('pricing');

// Stripe webhook
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])
    ->name('stripe.webhook');

// AUTH-only routes (manage your own events)
Route::middleware('auth')->group(function () {
    // /oauth/google/redirect
    Route::get('{provider}/redirect', [SocialAuthController::class, 'redirect'])
        ->whereIn('provider', ['google','github'])
        ->name('oauth.redirect');

    // /oauth/google/callback
    Route::get('{provider}/callback', [SocialAuthController::class, 'callback'])
        ->whereIn('provider', ['google','github'])
        ->name('oauth.callback');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/dashboard', [EventController::class, 'dashboard'])->name('dashboard');

    // Resource CRUD (excludes show so it won't clash with public show)
    Route::resource('events', EventController::class)->except(['show']);

    // View registrants (organizer)
    Route::get('/events/{event}/registrants', [RegistrantsController::class, 'index'])
        ->name('events.registrants');

    // Unlock flow for FREE events
    Route::get('/events/{event}/registrants/unlock', [RegistrantsController::class, 'unlock'])
        ->name('events.registrants.unlock');

    Route::post('/events/{event}/registrants/checkout', [RegistrantsController::class, 'checkout'])
        ->name('events.registrants.checkout');

    Route::get('/events/{event}/registrants/unlock/success', [RegistrantsController::class, 'success'])
        ->name('events.registrants.unlock.success');

    // Payout routes
    Route::get('/payouts', [PayoutController::class, 'index'])->name('payouts.index');
    Route::get('/events/{event}/payouts/new', [PayoutController::class, 'create'])->name('payouts.create');
    Route::post('/events/{event}/payouts', [PayoutController::class, 'store'])->name('payouts.store');

    // Email registrants
    Route::get('/events/{event}/registrants/email', [RegistrantEmailController::class, 'create'])
        ->name('events.registrants.email');
    Route::post('/events/{event}/registrants/email', [RegistrantEmailController::class, 'send'])
        ->name('events.registrants.email.send');

    // "My Tickets" area (for attendees to manage their own registrations)
    Route::get('/my/tickets', [MyTicketsController::class, 'index'])->name('my.tickets');
    Route::get('/my/tickets/{registration}', [MyTicketsController::class, 'edit'])->name('my.tickets.edit');
    Route::post('/my/tickets/{registration}', [MyTicketsController::class, 'update'])->name('my.tickets.update');
});

// PUBLIC registration + avatar (these don't clash with /events/create)
Route::get('/events/{event}/ticket', [TicketLookupController::class, 'showForm'])
    ->name('events.ticket.find');

Route::post('/events/{event}/ticket', [TicketLookupController::class, 'sendLink'])
    ->name('events.ticket.sendlink');

Route::get('/events/{event}/ticket/manage/{reg}', [TicketLookupController::class, 'edit'])
    ->name('events.ticket.edit');

Route::post('/events/{event}/ticket/manage/{reg}', [TicketLookupController::class, 'update'])
    ->name('events.ticket.update');

Route::get('/events/{event}/avatar', [EventController::class, 'avatar'])
    ->name('events.avatar');

Route::get('/events/{event}/register', [RegistrationController::class, 'create'])
    ->name('events.register.create');

Route::post('/events/{event}/register', [RegistrationController::class, 'store'])
    ->name('events.register.store');

Route::get('/events/{event}/register/result', [RegistrationController::class, 'result'])
    ->name('events.register.result');

// PUBLIC show route — put AFTER resource routes so it doesn't catch /events/create
// Also guard against the reserved word "create" just in case.
Route::get('/events/{event}', [EventController::class, 'show'])
    ->where('event', '^(?!create$).+')
    ->name('events.show');

require __DIR__.'/auth.php';
