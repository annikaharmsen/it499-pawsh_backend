<?php

namespace App\Http\Controllers;

use App\Http\Resources\CartItemResource;
use App\Models\CartItem;
use App\services\ResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class CartItemController extends Controller
{
    private $storeRules = [
        'quantity' => 'required | integer | gte:0',
        'productid' => 'required | exists:products,id'
    ];

    private $updateRules = [
        'quantity' => 'required | integer | gte:0',
    ];

    public function respondWithOne(String $message, CartItem $cartitem): JsonResponse {
        return parent::sendResponse($message, ['cartitem' => new CartItemResource($cartitem)]);
    }

    public function respondWithMany(String $message, mixed $cartitems): JsonResponse {
        return parent::sendResponse($message, ['cartitems' => CartItemResource::collection($cartitems)]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cartitems = Auth::user()->cartitems;

        //TODO: implement out of stock behavior

        return $this->respondWithMany('User\'s cart items retreived successfully', $cartitems);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $input = $this->validateOrError($request, $this->storeRules);

        $cartitem = CartItem::where([
            'userid' => Auth::id(),
            'productid' => $input['productid'],
        ])->first();

        if (!$cartitem) {
            $cartitem = new CartItem([
                'userid' => Auth::id(),
                'productid' => $input['productid'],
                'quantity' => 0
            ]);
        }

        $cartitem->quantity += $input['quantity'];
        $cartitem->save();

        return $this->respondWithOne('Product was successfully added to cart.', $cartitem);
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

        if ($cartItem->userid !== Auth::id()) {
            return ResponseService::sendError('Item not found for this user. ' . 'Cart item user id: ' . $cartItem->userid . ', Authenticated user id: ' . Auth::id(), Response::HTTP_NOT_FOUND);
        }

        $cartItem->update($input);

        return $this->respondWithOne('Cart updated successfully.', $cartItem);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CartItem $cartItem)
    {

        if ($cartItem->userid !== Auth::id()) {
            return ResponseService::sendError('Cart item not found for this user.', Response::HTTP_NOT_FOUND);
        }

        $cartItem->delete();

        return $this->respondWithMany('Item successfully removed from cart', Auth::user()->cartitems);
    }
}
