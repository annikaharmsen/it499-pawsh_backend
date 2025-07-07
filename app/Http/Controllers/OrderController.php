<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderResource;
use App\Models\Address;
use App\Models\Order;
use App\Models\Payment;
use App\services\OrderService;
use App\services\ResponseService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    private $storeRules = [
    ];

    private $updateRules = [
        'shipping_addressid' => 'required|exists:addresses,id',
    ];

    public function respondWithOne(String $message, Order $order): JsonResponse {
        return parent::sendResponse($message, ['order' => new OrderResource($order)]);
    }

    public function respondWithMany(String $message, mixed $orders): JsonResponse {
        return parent::sendResponse($message, ['orders' => OrderResource::collection($orders)]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $orders = Auth::user()->orders;

        return $this->respondWithMany(
            'User\'s orders retreived successfully',
            $orders
        );
    }

    /**
     * Create order
     * Create stripe checkout session
     */
    public function initialize(Request $request)
    {
        $order = OrderService::initiateOrder(Auth::user());

        // store payment

        // CREATE CHECKOUT SESSION
        $stripe = config('app')['stripe'];
        $DOLLARS_TO_CENTS = 100;

        // create stripe checkout session
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
                  'unit_amount' => (int) $item->unitprice * $DOLLARS_TO_CENTS,
                ],
                'quantity' => $item->quantity,
            ];
        }

        $session = $stripe->checkout->sessions->create([
            'line_items' => $line_items,
            'mode' => 'payment',
            'ui_mode' => 'custom',
            'return_url' => 'http://127.0.0.1:8001/stripe-return?session_id={CHECKOUT_SESSION_ID}',
            'metadata' => [
                'orderid' => $order->id,
            ]
        ]);

        if ($session->amount_total !== $order->getTotal())
        {
            ResponseService::sendError(
                'Error calculating total.',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        // STORE PAYMENT
        $payment = new Payment([
            'amount' => $session->amount_total,
            'status' => $session->payment_status,
            'transaction_referenceid' => $session->payment_intent,
            'orderid' => $order->id
        ]);

        // send order and checkout session's client secret
        return $this->sendResponse(
            'Order in progress',
            [
                'order' => new OrderResource($order),
                'checkoutSessionClientSecret' => $session->client_secret
                ]
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        if ($order->userid !== Auth::id()) {
            return ResponseService::sendError('Order not found for this user.', Response::HTTP_NOT_FOUND);
        }

        return $this->respondWithOne('Order retreived successfully.', $order);
    }

    /**
     * Check payment status
     * Store payment
     * Define order shipping address
     * Update order status
     */
    public function update(Request $request, Order $order)
    {
        $stripe = require_once('config/app.php')['stripe'];

        try {
            $session = $stripe->checkout->sessions->retrieve($request->sessionid);
        } catch (Exception $e) {
            ResponseService::sendError('Invalid session.', Response::HTTP_BAD_REQUEST);
        }

        $order = Order::whereKey($session->metadata->order_id)->first();

        if ($order->userid !== Auth::id()) {
            ResponseService::sendError('Unauthorized.', Response::HTTP_UNAUTHORIZED);
        }

        // store payment info in pawsh db
        $payment = new Payment([
                'amount' => $session->amount_total,
                'status' => $session->payment_status,
                'transaction_referenceid' => $session->payment_intent,
                /*'billing_addressid' => //TODO: add value or remove entirely */
                'orderid' => $order->id
        ]);
        $payment->save();

        $input = $this->validateOrError($request, $this->updateRules, 'Invalid address ID.');

        $address = Address::whereId($input['shipping_addressid']);

        OrderService::updateAddress($order, $address);

        $this->respondWithOne('Order updated successfully.', $order);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        //
    }
}
