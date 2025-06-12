<?php

namespace App\Http\Controllers;

use App\Http\Resources\CartItemResource;
use App\Models\CartItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class CartItemController extends Controller
{
    private $storeRules = [
        'quantity' => 'required | integer | gte:0',
        'product_id' => 'required | exists:products,id'
    ];

    private $updateRules = [
        'quantity' => 'required | integer | gte:0',
    ];

    public function respondWithOne(String $message, CartItem $cart_item): JsonResponse {
        return parent::sendResponse($message, ['cart_item' => new CartItemResource($cart_item)]);
    }

    public function respondWithMany(String $message, mixed $cart_items): JsonResponse {
        return parent::sendResponse($message, ['cart_items' => CartItemResource::collection($cart_items)]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cart_items = Auth::user()->cart_items;

        return $this->respondWithMany('User\'s cart items retreived successfully', $cart_items);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $input = $this->validateOrError($request, $this->storeRules);

        $cart_item = CartItem::where([
            'user_id' => Auth::id(),
            'product_id' => $input['product_id'],
        ])->first();

        if (!$cart_item) {
            $cart_item = new CartItem([
                'user_id' => Auth::id(),
                'product_id' => $input['product_id'],
                'quantity' => 0
            ]);
        }

        $cart_item->quantity += $input['quantity'];
        $cart_item->save();

        return $this->respondWithOne('Product was successfully added to cart.', $cart_item);
    }

    /**
     * Display the specified resource.
     */
    public function show(CartItem $cartItem)
    {
        return $this->respondWithOne('Cart item retreived successfully.', $cartItem);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CartItem $cartItem)
    {
        $input = $this->validateOrError($request, $this->updateRules);

        if ($cartItem->user_id !== Auth::id()) {
            return $this->sendError('Item not found for this user. ' . 'Cart item user id: ' . $cartItem->user_id . ', Authenticated user id: ' . Auth::id(), Response::HTTP_NOT_FOUND);
        }

        $cartItem->update($input);

        return $this->respondWithOne('Cart updated successfully.', $cartItem);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CartItem $cartItem)
    {

        if ($cartItem->user_id !== Auth::id()) {
            return $this->sendError('Cart item not found for this user.', Response::HTTP_NOT_FOUND);
        }

        $cartItem->delete();

        return $this->sendResponse('Item successfully removed from cart');
    }
}
