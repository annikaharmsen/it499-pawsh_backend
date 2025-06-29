<?php

namespace App\Http\Controllers;

use App\Http\Resources\PaymentResource;
use App\Models\Order;
use App\Models\Payment;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function respondWithOne(String $message, Payment $payment): JsonResponse {
        return parent::sendResponse($message, ['payment' => new PaymentResource($payment)]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $stripe = require_once('config/app.php')['stripe'];

        try {
            $session = $stripe->checkout->sessions->retrieve($request->session_id);
        } catch (Exception $e) {
            $this->sendError('Invalid session.', Response::HTTP_BAD_REQUEST);
        }

        $order = Order::whereKey($session->metadata->order_id)->first();

        if ($order->user_id !== Auth::id()) {
            $this->sendError('Unauthorized.', Response::HTTP_UNAUTHORIZED);
        }

        $payment = new Payment([
                'amount' => $session->amount_total,
                'status' => $session->payment_status,
                'transaction_reference_id' => $session->payment_intent,
                'order_id' => $order->id,
        ]);
        $payment->save();

        if ($payment->status == 'paid') {

            $order->status = 'sent';
            $order->save();

            $cart_items = Auth::user()->cart_items()->get();

            foreach ($cart_items as $item) {
                $item->delete();
            }

            $this->respondWithOne('Payment received successfully. Order has been sent.', $payment);
        } else {
            $this->sendError('There was an issue processing the payment.', Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Payment $payment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Payment $payment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Payment $payment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment)
    {
        //
    }
}
