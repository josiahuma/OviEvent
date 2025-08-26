<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

class CheckoutController extends Controller
{
    public function payForEvent(Request $request)
    {
        Stripe::setApiKey(config('cashier.secret'));

        $checkoutSession = StripeSession::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'unit_amount' => $request->ticket_cost * 100,
                    'product_data' => ['name' => $request->event_name],
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('events.success', [], true) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('events.index'),
        ]);

        return redirect($checkoutSession->url);
    }
}
