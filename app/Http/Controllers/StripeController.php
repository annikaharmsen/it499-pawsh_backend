<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\services\ResponseService;
use App\services\StripeService;
use Error;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Stripe\StripeClient;

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

    }
}
