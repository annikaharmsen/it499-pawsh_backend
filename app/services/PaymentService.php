<?php

namespace App\services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;

class PaymentService {

    private static $CENTS_TO_DOLLARS = .01;

    public static function storePayment(Session $checkout_session)
    {
        $status = new StripeService()->retrievePaymentIntent($checkout_session->payment_intent)->status;

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
            ['transaction_referenceid' => $checkout_session->payment_intent],
            [
                'amount' => (float) $checkout_session->amount_total * self::$CENTS_TO_DOLLARS,
                'status' => $status,
                'orderid' => $checkout_session->metadata->orderid
            ]
            );

        ResponseService::saveOrError($payment);

        return $payment;
    }
}
