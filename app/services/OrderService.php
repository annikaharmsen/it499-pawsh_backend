<?php

namespace App\services;

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
            'status' => 'checking out',
            'userid' => $user->id
        ]);
        $order->save();

        self::orderItemsFromCart($user, $order);
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
}
