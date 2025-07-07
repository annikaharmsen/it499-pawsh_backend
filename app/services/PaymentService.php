<?php

namespace App\services;

use App\Models\Order;
use App\Models\Payment;
use Exception;
use Illuminate\Http\Response;

class PaymentService {

    public static function initializePayment(Order $order)
    {
        $session = self::createStripeCheckoutSession($order);

        if ($session->amount_total !== $order->getTotal())
        {
            ResponseService::sendError(
                'Error calculating total.',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        self::storePayment($session, $order);

        return $session;
    }

    //TODO: check necessity
    public static function retreiveStripeCheckoutSession($session_id)
    {
        $stripe = require_once('config/app.php')['stripe'];

        try {
            $session = $stripe->checkout->sessions->retrieve($session_id);
        } catch (Exception $e) {
            ResponseService::sendError('Invalid payment session.', Response::HTTP_BAD_REQUEST);
        }

        return $session;
    }

    // HELPER METHODS

    private static function createStripeCheckoutSession(Order $order)
    {
        $stripe = config('app')['stripe'];

        $line_items = self::getLineItems($order);

        $session = $stripe->checkout->sessions->create([
            'line_items' => $line_items,
            'mode' => 'payment',
            'ui_mode' => 'custom',
            'return_url' => 'http://127.0.0.1:8001/stripe-return?session_id={CHECKOUT_SESSION_ID}',
            'metadata' => [
                'orderid' => $order->id,
            ]
        ]);

        self::storePayment($session, $order);

        return $session;
    }

    private static function getLineItems(Order $order)
    {
        $line_items = [];
        $DOLLARS_TO_CENTS = 100;

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
                  'unit_amount' => (int) $item->unitprice * $DOLLARS_TO_CENTS,
                ],
                'quantity' => $item->quantity,
            ];
        }

        return $line_items;
    }

    private static function storePayment(object $session, Order $order) {
        $payment = new Payment([
            'amount' => $session->amount_total,
            'status' => $session->payment_status,
            'transaction_referenceid' => $session->payment_intent,
            'orderid' => $order->id
        ]);

        ResponseService::saveOrError($payment);
    }
}
