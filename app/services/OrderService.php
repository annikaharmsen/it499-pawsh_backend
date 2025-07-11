<?php

namespace App\services;

use App\Http\Resources\OrderResource;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\services\ResponseService;
use Illuminate\Http\Response;

class OrderService {

    private static $DOLLARS_TO_CENTS = 100;

    public static function initializeOrder(User $user)
    {
        self::requireCartItems($user);

        // STORE ORDER
        $order = new Order([
            'status' => 'in progress',
            'userid' => $user->id
        ]);
        ResponseService::saveOrError($order);

        self::orderItemsFromCart($user, $order);

        return $order;
    }

    private static function requireCartItems(User $user) {
        $cartitems = $user->cartitems;

        if ($cartitems->count() < 1) {
            ResponseService::sendError('Order cannot be placed. There are no items in cart.', Response::HTTP_BAD_REQUEST);
        }

        return $cartitems;
    }

    private static function orderItemsFromCart(User $user, Order $order) {
        foreach (self::requireCartItems($user) as $item) {
            OrderItem::create([
                'orderid'   => $order->id,
                'productid' => $item->productid,
                'quantity'  => $item->quantity,
                'unitprice' => $item->product->price
            ]);
        }
    }

    public static function updateShippingAddress(Order $order, Address $address)
    {
        if ($order->status !== 'processing') {
            ResponseService::sendError('Cannot update shipping address of this order.');
        }

        ResponseService::updateOrError($order, ['shipping_addressid' => $address->id, 'status' => 'awaiting payment']);

    }

    public static function placeOrder(Order $order)
    {
        ResponseService::updateOrError($order, ['status' => 'paid']);
    }

    public static function getTotal(Order|OrderResource $order) {
        return $order->items->reduce(function ($carry, $item) {
            return $carry += $item->unitprice * $item->quantity;
        });
    }

    public static function getCentTotal(Order|OrderResource $order) {
        return (int) self::getTotal($order) * self::$DOLLARS_TO_CENTS;
    }

    public static function isPaid(Order $order) {
        $orderTotal = self::getCentTotal($order);

        $paidTotal = $order->payments->where('status', 'paid')->sum('amount');

        return $orderTotal >= $paidTotal;
    }
}
