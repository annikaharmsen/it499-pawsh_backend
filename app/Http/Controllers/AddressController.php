<?php

namespace App\Http\Controllers;

use App\Http\Resources\AddressResource;
use App\Models\Address;
use App\services\ResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AddressController extends Controller
{
    private $storeRules = [
        'street_one' => 'required | string',
        'street_two' => 'nullable | string',
        'city' => 'required | string | max:120',
        'state' => 'required | string | max:120',
        'postalcode' => 'required | int',
        'country' => 'required | string | max:120',
        'name' => 'required | string | max:120',
    ];

    private $updateRules = [
        'street_one' => 'nullable | string',
        'street_two' => 'nullable | string',
        'city' => 'nullable | string | max:120',
        'state' => 'nullable | string | max:120',
        'postalcode' => 'nullable | int',
        'country' => 'nullable | string | max:120',
        'name' => 'nullable | string | max:120',
    ];

    public function respondWithOne(String $message, Address $address): JsonResponse {
        return ResponseService::sendResponse($message, ['address' => new AddressResource($address)]);
    }

    public function respondWithMany(String $message, mixed $addresses): JsonResponse {
        return ResponseService::sendResponse($message, ['addresses' => AddressResource::collection($addresses)]);
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
        $input = ResponseService::validateOrError($request, $this->storeRules);

        $address = new Address($input);
        $address['userid'] = Auth::id();
        $address->save();

        return $this->respondWithOne('Address saved successfully.', $address);
    }

    /**
     * Display the specified resource.
     */
    public function show(Address $address)
    {
        if ($address->userid !== Auth::id()) {
            return ResponseService::sendError('Address not found for this user.', Response::HTTP_NOT_FOUND);
        }

        return $this->respondWithOne('Address retreived successfully.', $address);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Address $address)
    {
        $input = ResponseService::validateOrError($request, $this->updateRules);

        if ($address->userid !== Auth::id()) {
            return ResponseService::sendError('Address not found for this user.', Response::HTTP_NOT_FOUND);
        }

        $address->update($request->all());

        return $this->respondWithOne('Address updated successfully.', $address);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Address $address)
    {
        if ($address->userid !== Auth::id()) {
            return ResponseService::sendError('Address not found for this user.', Response::HTTP_NOT_FOUND);
        }

        $address->delete();

        return ResponseService::sendResponse('Address deleted successfully.');
    }
}
