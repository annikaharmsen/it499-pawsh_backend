<?php

namespace App\services;

use App\Models\Order;
use Exception;
use Illuminate\Http\Response;
use Stripe\Event;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\StripeClient;

class StripeService extends PaymentService {

    private static $DOLLARS_TO_CENTS = 100;
    protected StripeClient $client;

    public function __construct() {
        Stripe::setApiKey(config('app.stripe_api_key'));
        $this->client = new StripeClient(config('app.stripe_api_key'));
    }

    public function createCheckoutSession(Order $order)
    {

        if ($order->status !== 'awaiting payment') {
            ResponseService::sendError('Cannot create a checkout session for this order at this time.');
        }

        $line_items = self::getLineItems($order);

        $session = $this->client->checkout->sessions->create([
            'line_items' => $line_items,
            'mode' => 'payment',
            'ui_mode' => 'custom',
            'return_url' => config('app.url') . '/checkout/status/{CHECKOUT_SESSION_ID}',
            'payment_intent_data' => [
                'metadata' => [
                    'orderid' => $order->id,
                ]
            ]
        ]);

        return $session;
    }

    public function retrieveStripeCheckoutSession(string $session_id)
    {
        try {
            $session = $this->client->checkout->sessions->retrieve($session_id);
        } catch (Exception $e) {
            ResponseService::sendError($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        return $session;
    }

    public function retrievePaymentIntent(string $payment_intent_id) {
        return PaymentIntent::retrieve($payment_intent_id);
    }

    public function constructEvent(array $values) {
        return Event::constructFrom($values);
    }

    private static function getLineItems(Order $order)
    {
        $line_items = [];

        foreach ($order->items as $item) {
            $line_items[] =
            [
                'price_data' =>
                [
                  'currency' => 'usd',
                  'product_data' =>
                  [
                    'name' => $item->product->name,
                  ],
                  'unit_amount' => self::toCents($item->unitprice),
                ],
                'quantity' => $item->quantity,
            ];
        }

        return $line_items;
    }

    public static function toCents(float $dollars) {
        return (int) $dollars * self::$DOLLARS_TO_CENTS;
    }
}
