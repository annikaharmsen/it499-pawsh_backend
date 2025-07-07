<?php

namespace App\services;

use App\Models\User;

class CartService {

    public static function clearCart(User $user)
    {
        foreach ($user->cartitems as $item) {
            $item->delete();
        }
    }
}
