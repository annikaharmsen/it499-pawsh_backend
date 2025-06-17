<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Address;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    private $storeRules = [
        'title' => 'required | string | max:120',
        'description' => 'required | string | max:500',
        'price' => 'required | float'
    ];
    private $updateRules = [
        'title' => 'nullable | string | max:120',
        'description' => 'nullable | string | max:500',
        'price' => 'nullable | float'
    ];

    public function respondWithOne(String $message, Product $product): JsonResponse {
        return parent::sendResponse($message, ['product' => new ProductResource($product)]);
    }

    public function respondWithMany(String $message, mixed $products): JsonResponse {
        return parent::sendResponse($message, ['products' => ProductResource::collection($products)]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->respondWithMany('Products retreived successfully', Product::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
