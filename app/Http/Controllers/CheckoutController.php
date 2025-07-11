<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderResource;
use App\Models\Address;
use App\Models\Order;
use App\services\CartService;
use App\services\OrderService;
use App\services\PaymentService;
use App\services\ResponseService;
use App\services\StripeService;
use Error;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use UnexpectedValueException;

class CheckoutController extends Controller
{

    private $addressRules = [
        'shipping_addressid' => 'required|exists:addresses,id',
    ];

    public function respondWithOrder(String $message, Order $order): JsonResponse {
        return ResponseService::sendResponse($message, ['order' => new OrderResource($order)]);
    }

    /**
     * Fill in order's shipping address
     */
    public function shippingAddress(Request $request, Order $order)
    {
        $input = ResponseService::validateOrError($request, $this->addressRules, 'Invalid address ID.');

        $address = Address::whereId($input['shipping_addressid'])->first();

        if ($address->userid !== Auth::id() || $order->userid !== Auth::id()) {
            ResponseService::sendError('Unauthorized.', Response::HTTP_UNAUTHORIZED);
        }

        OrderService::updateShippingAddress($order, $address);

        return $this->respondWithOrder('Order address updated successfully.', $order);
    }

    /**
     * Create stripe checkout session
     */
    public function session(Order $order)
    {
        if ($order->userid !== Auth::id()) {
            ResponseService::sendError('Unauthorized');
        }

        $session = new StripeService()->createCheckoutSession($order);

        return ResponseService::sendResponse(
            'Order in progress',
            [
                'order' => new OrderResource($order),
                'checkoutSessionClientSecret' => $session->client_secret
                ]
        );
    }

    /**
     * Receive post-session events, update DB
     */
    public function processSessionEvents(Request $request)
    {
        $stripe_service = new StripeService();

        try {
            $event = $stripe_service->constructEvent(
            $request->toArray()
        );
        } catch(UnexpectedValueException $e) {
            ResponseService::sendError('Webhook error while parsing basic request.');
        }

        Log::info('Reveived event: ' . $event->type);


        if (in_array($event->type, ['checkout.session.completed', 'checkout.session.async_payment_succeeded', 'checkout.session.async_payment_failed']))
        {
            $session = $event->data->object;
            $payment = PaymentService::storePayment($session);

            if ($event->type === 'checkout.session.completed' && OrderService::isPaid($payment->order)) {
                self::completeCheckout($payment->order);
            }
        }
        else {
            Log::alert('Received unknown event type: ' . $event->type);
        }

        http_response_code(200);
    }

    private function completeCheckout(Order $order)
    {
        OrderService::placeOrder($order);
        CartService::clearCart($order->user);
    }

    /**
     * Get checkout session status
     */
    public function status(Request $request)
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
}
