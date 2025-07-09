<?php

namespace App\services;

use App\Models\Order;
use App\Models\Payment;

class PaymentService {

    public static function initializePayment(Order $order)
    {
        $session = new StripeService()->createStripeCheckoutSession($order);

        self::storePayment($session, $order);

        return $session;
    }

    // HELPER METHODS

    private static function storePayment(object $session, Order $order)
    {
        $payment = new Payment([
            'amount' => $session->amount_total,
            'status' => $session->payment_status,
            'orderid' => $order->id
        ]);

        ResponseService::saveOrError($payment);
    }
}
