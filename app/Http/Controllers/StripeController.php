<?php

namespace App\Http\Controllers;

use App\services\PaymentService;
use App\services\ResponseService;
use App\services\StripeService;
use Error;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use UnexpectedValueException;

class StripeController extends Controller
{

    /**
     * Checkout session 'return_url': returns status of checkout session
     */
    public function getCheckoutStatus(Request $request)
    {
        try {
            $session = new StripeService()->retrieveStripeCheckoutSession($request->session_id);

            if (Auth::user()->email !== $session->customer_details->email) {
                ResponseService::sendError('Unauthorized.', Response::HTTP_UNAUTHORIZED);
            }

            return ResponseService::sendResponse('Checkout session status retreived successfully.', ['status' => $session->status], Response::HTTP_OK);

          } catch (Error $e) {
            ResponseService::sendError($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
          }
    }

    /**
     * Receive post-payment events, update DB
     */
    public function processPostPaymentEvent(Request $request)
    {
        $stripe_service = new StripeService();

        try {
            $event = $stripe_service->constructEvent(
              $request->toArray()
            );
          } catch(UnexpectedValueException $e) {
            ResponseService::sendError('Webhook error while parsing basic request.');
          }


        switch ($event->type)
        {
            case 'payment_intent.succeeded':
                $payment_intent = $event->data->object;
                PaymentService::storePayment($payment_intent);
                break;

            case 'checkout.session.async_payment_succeeded':
                $session = $event->data->object;
                $payment_intent = $stripe_service->retrievePaymentIntent($session->payment_intent);
                PaymentService::storePayment($payment_intent);
                break;

            case 'checkout.session.async_payment_failed':
                error_log('Payment failed.');
                break;

            default:
                // Unexpected event type
                error_log('Received unknown event type: ' . $event->type);
        }

        http_response_code(200);
    }
}
