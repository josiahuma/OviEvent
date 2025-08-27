<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\Webhook;
use Throwable;

use App\Models\EventRegistration;
use App\Models\EventUnlock; // only if you use the unlock flow
use App\Mail\NewRegistrationNotificationMail;
use App\Mail\RegistrationConfirmedMail;
use App\Mail\OrganizerNewRegistrationMail;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret    = config('services.stripe.webhook_secret');

        // Build $event (object) or $json (array) depending on whether a secret is set
        if ($secret) {
            try {
                $event = Webhook::constructEvent($payload, $sigHeader, $secret);
            } catch (Throwable $e) {
                Log::warning('Stripe webhook signature verification failed', ['error' => $e->getMessage()]);
                return response('invalid', 400);
            }
            $type = $event->type;
            $data = $event->data->object;
        } else {
            $json = json_decode($payload, true);
            $type = $json['type'] ?? null;
            $data = $json['data']['object'] ?? [];
        }

        // Handle successful Checkout Sessions (sync or async)
        if (in_array($type, ['checkout.session.completed', 'checkout.session.async_payment_succeeded'], true)) {
            $paymentStatus = $data['payment_status'] ?? null;
            if ($paymentStatus !== 'paid') {
                return response('ok', 200);
            }

            // Optional: handle your "registrants_unlock" purchase
            $purpose = $data['metadata']['purpose'] ?? 'registration';
            if ($purpose === 'registrants_unlock') {
                $eventId = (int) ($data['metadata']['event_id'] ?? 0);
                $userId  = (int) ($data['metadata']['user_id'] ?? 0);

                if ($eventId && $userId) {
                    EventUnlock::updateOrCreate(
                        ['event_id' => $eventId, 'user_id' => $userId],
                        [
                            'stripe_session_id'         => $data['id'] ?? null,
                            'stripe_payment_intent_id'  => $data['payment_intent'] ?? null,
                            'unlocked_at'               => now(),
                        ]
                    );
                }
                return response('ok', 200);
            }

            // ---- Registration payment flow ----
            $registration = null;

            // Prefer metadata.registration_id if you set it at Checkout creation time
            if (!empty($data['metadata']['registration_id'])) {
                $registration = EventRegistration::find($data['metadata']['registration_id']);
            }

            // Fallback to lookup by session id
            if (!$registration && !empty($data['id'])) {
                $registration = EventRegistration::where('stripe_session_id', $data['id'])->first();
            }

            if (!$registration) {
                // Not for us / can’t resolve a record — ack to avoid retries.
                return response('ok', 200);
            }

            // Idempotency: bail if already paid
            if ($registration->status === 'paid') {
                return response('ok', 200);
            }

            $registration->status = 'paid';

            // If you want to persist Stripe PI id for support / reconciliation:
            if (!empty($data['payment_intent'])) {
                $registration->stripe_payment_intent_id = $data['payment_intent'];
            }

            // If your DB stores amount in major units (e.g. 10.00), keep as-is.
            // If you store minor units instead, you could uncomment:
            // if (isset($data['amount_total'])) {
            //     $registration->amount = $data['amount_total'] / 100;
            // }

            $registration->save();

            // Notify attendee
            try {
                Mail::to($registration->email)
                    ->send(new RegistrationConfirmedMail($registration->event, $registration));
            } catch (Throwable $e) {
                Log::warning('Webhook attendee mail failed', ['error' => $e->getMessage()]);
            }

            // Notify organizer (if user relation/email exists)
            try {
                $organizerEmail = optional($registration->event->user)->email;
                if ($organizerEmail) {
                    Mail::to($organizerEmail)
                        ->send(new OrganizerNewRegistrationMail($registration->event, $registration));
                }
            } catch (Throwable $e) {
                Log::warning('Webhook organizer mail failed', ['error' => $e->getMessage()]);
            }
        }

        return response('ok', 200);
    }
}
