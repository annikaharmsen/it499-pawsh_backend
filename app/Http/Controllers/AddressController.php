<?php

namespace App\Http\Controllers;

use App\Http\Resources\AddressResource;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AddressController extends Controller
{
    private $storeRules = [
        'house_num' => 'required | integer | gt:0',
        'apt_num' => 'integer | gt:0',
        'street' => 'required | string | max:120',
        'city' => 'required | string | max:120',
        'state' => 'required | string | max:120',
        'country' => 'required | string | max:120',
        'name' => 'required | string | max:120',
        'user_id' => 'nullable | exists:users,id'
    ];

    private $updateRules = [
        'house_num' => 'nullable | integer | gt:0',
        'apt_num' => 'nullable | integer | gt:0',
        'street' => 'nullable | string | max:120',
        'city' => 'nullable | string | max:120',
        'state' => 'nullable | string | max:120',
        'country' => 'nullable | string | max:120',
        'name' => 'nullable | string | max:120',
        'user_id' => 'nullable | exists:users,id'
    ];

    public function respondWithOne(String $message, Address $address): JsonResponse {
        return parent::sendResponse($message, ['address' => new AddressResource($address)]);
    }

    public function respondWithMany(String $message, mixed $addresses): JsonResponse {
        return parent::sendResponse($message, ['addresses' => AddressResource::collection($addresses)]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $addresses = Auth::user()->addresses;

        return $this->respondWithMany('User\'s addresses retreived successfully', $addresses);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $input = $this->validateOrError($request, $this->storeRules);

        $address = new Address($input);
        $address['user_id'] = Auth::id();
        $address->save();

        return $this->respondWithOne('Address saved successfully.', $address);
    }

    /**
     * Display the specified resource.
     */
    public function show(Address $address)
    {
        return $this->respondWithOne('Address retreived successfully.', $address);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Address $address)
    {
        $input = $this->validateOrError($request, $this->updateRules);

        if ($address->user_id !== Auth::id()) {
            return $this->sendError('Address not found for this user.', Response::HTTP_NOT_FOUND);
        }

        $address->update($request->all());

        return $this->respondWithOne('Address updated successfully.', $address);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Address $address)
    {
        if ($address->user_id !== Auth::id()) {
            return $this->sendError('Address not found for this user.', Response::HTTP_NOT_FOUND);
        }

        $address->delete();

        return $this->sendResponse('Address deleted successfully.');
    }
}
