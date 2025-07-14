<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderResource;
use App\Http\Resources\PaymentResource;
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
use Stripe\Checkout\Session;
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

        if (in_array($event->type, ['payment_intent.succeeded', 'payment_intent.failed']))
        {
            Log::info('Received event: ' . $event->type);

            $payment_intent = $event->data->object;
            $payment = PaymentService::storePayment($payment_intent);

            Log::info('stored payment: ' . $payment);

            if ($payment->status === 'succeeded' && OrderService::isPaid($payment->order)) {
                self::completeCheckout($payment->order);
                Log::info('order status: ' . $payment->order->status);
                Log::info('cart items: ' . $payment->order->user->cartitems);
            }

            return ResponseService::sendResponse('Event processed successfully.', ['payment' => new PaymentResource($payment), 'order' => new OrderResource($payment->order)]);
        }
        else {
            Log::alert('Received unknown event type: ' . $event->type);

            return ResponseService::sendResponse('Unknown event type: ' . $event);
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
    public function status(string $sessionId)
    {
        try {
            $session = new StripeService()->retrieveStripeCheckoutSession($sessionId);

            return ResponseService::sendResponse('Checkout session status retreived successfully.', ['status' => $session->status], Response::HTTP_OK);

        } catch (Error $e) {
          ResponseService::sendError($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
