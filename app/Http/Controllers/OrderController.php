<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\services\OrderService;
use App\services\ResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    private $storeRules = [
    ];

    private $updateRules = [
    ];

    public function respondWithOne(String $message, Order $order): JsonResponse {
        return ResponseService::sendResponse($message, ['order' => new OrderResource($order)]);
    }

    public function respondWithMany(String $message, mixed $orders): JsonResponse {
        return ResponseService::sendResponse($message, ['orders' => OrderResource::collection($orders)]);
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
     * Initiate order
     * Create stripe checkout session
     * Store payment
     *
     */
    public function store(Request $request)
    {
        $order = OrderService::initializeOrder(Auth::user());

        return $this->respondWithOne('Order created successfully.', $order);
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
}
