<?php

namespace App\services;

use App\Models\Order;
use App\Models\Payment;
use Stripe\PaymentIntent;

class PaymentService {

    public static function storePayment(PaymentIntent $payment_intent)
    {
        $payment = new Payment([
            'amount' => $payment_intent->amount,
            'status' => $payment_intent->status,
            'orderid' => $payment_intent->metadata->orderid,
            'transaction_referenceid' => $payment_intent->id
        ]);

        ResponseService::saveOrError($payment);

        OrderService::trySend($payment->order);
    }
}
