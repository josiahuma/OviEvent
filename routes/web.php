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


// Homepage - public listing
Route::get('/', [EventController::class, 'publicIndex'])->name('homepage');

// PUBLIC event pages (numeric IDs only)
Route::get('/events/{id}', [EventController::class, 'show'])
    ->whereNumber('id')
    ->name('events.show');

Route::get('/events/{id}/avatar', [EventController::class, 'avatar'])
    ->whereNumber('id')
    ->name('events.avatar');

// PUBLIC registration routes
Route::get('/events/{id}/register', [RegistrationController::class, 'create'])
    ->whereNumber('id')
    ->name('events.register.create');

Route::post('/events/{id}/register', [RegistrationController::class, 'store'])
    ->whereNumber('id')
    ->name('events.register.store');

// Stripe webhook
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])
    ->name('stripe.webhook');


// Marketing pages
Route::get('/how-it-works', [PageController::class, 'how'])->name('how');
Route::get('/pricing', [PageController::class, 'pricing'])->name('pricing');

// NEW: result page for success / cancel / errors
Route::get('/events/{id}/register/result', [RegistrationController::class, 'result'])
    ->name('events.register.result');

// AUTH-only routes (manage your own events)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/dashboard', [EventController::class, 'dashboard'])->name('dashboard');
    // Generate /events, /events/create, /events (POST), /events/{event}/edit, etc.
    // Exclude 'show' so it doesn't clash with the public show route above.
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
});

require __DIR__.'/auth.php';
