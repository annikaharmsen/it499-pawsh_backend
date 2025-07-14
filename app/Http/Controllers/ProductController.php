<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\services\ResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{

    public function respondWithOne(String $message, Product $product): JsonResponse {
        return ResponseService::sendResponse($message, ['product' => new ProductResource($product)]);
    }

    public function respondWithMany(String $message, mixed $products): JsonResponse {
        return ResponseService::sendResponse($message, ['products' => ProductResource::collection($products)]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::all()->sortBy('created_at')->reverse();

        return $this->respondWithMany('Products retreived successfully', $products);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return $this->respondWithOne('Product retreived successfully.', $product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        //
    }
}
