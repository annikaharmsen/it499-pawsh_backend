<?php

namespace App\services;

use App\Models\Order;
use Exception;
use Illuminate\Http\Response;
use Stripe\StripeClient;

class StripeService extends PaymentService {

    private static $DOLLARS_TO_CENTS = 100;
    protected StripeClient $client;

    public function __construct() {

        $this->client = new StripeClient(config('app.stripe_api_key'));
    }

    public function createStripeCheckoutSession(Order $order)
    {
        $line_items = self::getLineItems($order);

        $session = config('app.stripe_client')->checkout->sessions->create([
            'line_items' => $line_items,
            'mode' => 'payment',
            'ui_mode' => 'custom',
            'return_url' => route('payment.store') . '/{CHECKOUT_SESSION_ID}',
            'metadata' => [
                'orderid' => $order->id,
            ]
        ]);

        return $session;
    }

    //TODO: check necessity
    public function retrieveStripeCheckoutSession($session_id)
    {
        try {
            $session = $this->client->checkout->sessions->retrieve($session_id);
        } catch (Exception $e) {
            ResponseService::sendError('Invalid payment session.', Response::HTTP_BAD_REQUEST);
        }

        return $session;
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
