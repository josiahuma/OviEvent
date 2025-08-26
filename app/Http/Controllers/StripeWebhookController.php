<?php

namespace App\Http\Controllers;

use App\Models\EventRegistration;
use Illuminate\Http\Request;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // If you configure STRIPE_WEBHOOK_SECRET, verify signature (recommended in production)
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret = config('services.stripe.webhook_secret');

        if ($secret) {
            try {
                $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $secret);
            } catch (\Throwable $e) {
                return response('invalid', 400);
            }
        } else {
            $event = json_decode($payload, true);
        }

        $type = $secret ? $event->type : ($event['type'] ?? null);
        $data = $secret ? $event->data->object : ($event['data']['object'] ?? []);

        if ($type === 'checkout.session.completed') {
            $sessionId = $data['id'] ?? null;
            if ($sessionId) {
                $registration = EventRegistration::where('stripe_session_id', $sessionId)->first();
                if ($registration && $registration->status !== 'paid') {
                    $registration->status = 'paid';
                    $registration->save();
                    // TODO: send confirmation email if needed
                }
            }
        }

        return response('ok', 200);
    }
}
