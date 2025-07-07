<?php

namespace App\services;

use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\services\ResponseService/* TODO: implement */;
use Illuminate\Http\Response;

class OrderService {

    public static function initiateOrder(User $user)
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
        foreach ($user->cartitems as $item) {
            OrderItem::create([
                'orderid'   => $order->id,
                'productid' => $item->productid,
                'quantity'  => $item->quantity,
                'unitprice' => $item->product->price
            ]);
        }
    }

    public static function updateAddress(Order $order, Address $address)
    {
        if ($order->status !== 'in progress') {
            ResponseService::sendError('Cannot update shipping address of a sent order.', Response::HTTP_BAD_REQUEST);
        }

        ResponseService::updateOrError($order, ['shipping_addressid' => $address->id]);

        if (self::isPaid($order)) {
            ResponseService::updateOrError($order, ['status' => 'sent']);
        }
    }

    private static function getTotal(Order $order) {
        return $order->items->reduce(function ($carry, $item) {
            return $carry += $item->unitprice * $item->quantity;
        });
    }

    private static function isPaid(Order $order) {
        $orderTotal = self::getTotal($order);

        $paidTotal = $order->payments->where('status', 'paid')->sum('amount');

        return $orderTotal === $paidTotal;
    }
}
