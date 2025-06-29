<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    private $storeRules = [
        'address_id' => 'required | exists:addresses,id'
    ];

    private $updateRules = [
        'status' => 'required | string'
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

        return $this->respondWithMany('User\'s orders retreived successfully', $orders);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $input = $this->validateOrError($request, $this->storeRules);

        $cart_items = Auth::user()->cart_items()->get();

        if ($cart_items->count() < 1) {
            $this->sendError('Order cannot be placed. There are no items in cart.', Response::HTTP_BAD_REQUEST);
        }

        // create order
        $order = new Order($input);
        $order['status'] = 'processing';
        $order['user_id'] = Auth::id();
        $order->save();

        // convert cart items to order items
        foreach ($cart_items as $item) {
            OrderItem::create([
                'order_id'   => $order->id,
                'product_id' => $item->product_id,
                'quantity'   => $item->quantity,
                'price'      => $item->product->price
            ]);
        }

        $order['status'] = 'awaiting payment';
        $order->save();

        // create checkout session
        $stripe = config('app')['stripe'];

        $line_items = [];
        foreach ($order->items as $item) {
            $line_items[] = [
                'price_data' => [
                  'currency' => 'usd',
                  'product' => $item->product->id,
                  'unit_amount_decimal' => $item->price,
                  'tax_behavior' => 'unspecified'
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
                'order_id' => $order->id,
            ]
        ]);

        // send order and checkout session's client secret
        $this->sendResponse('Order in progress', ['order' => new OrderResource($order), 'checkoutSessionClientSecret' => $session->client_secret]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            return $this->sendError('Order not found for this user.', Response::HTTP_NOT_FOUND);
        }

        return $this->respondWithOne('Order retreived successfully.', $order);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        //
    }
}
