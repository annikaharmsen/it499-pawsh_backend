<?php

namespace App\services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\PaymentIntent;

class PaymentService {

    private static $CENTS_TO_DOLLARS = .01;

    public static function storePayment(PaymentIntent $payment_intent)
    {
        $status = $payment_intent->status;

        if (in_array($status, [
            'requires_payment_method',
            'requires_confirmation',
            'requires_action',
            'requires_capture'
            ]))
        {
            $status = 'awaiting requirements';
        }

        $payment = Payment::updateOrCreate(
            ['transaction_referenceid' => $payment_intent->id],
            [
                'amount' => (float) $payment_intent->amount * self::$CENTS_TO_DOLLARS,
                'status' => $status,
                'orderid' => $payment_intent->metadata->orderid
            ]
            );

        ResponseService::saveOrError($payment);

        return $payment;
    }
}
