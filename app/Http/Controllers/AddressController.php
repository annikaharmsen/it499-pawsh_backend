<?php

namespace App\Http\Controllers;

use App\Http\Resources\AddressResource;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AddressController extends Controller
{

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $input = $request->validate([
                'house_num' => 'required | integer | gt:0',
                'apt_num' => 'integer | gt:0',
                'street' => 'required | string | max:120',
                'city' => 'required | string | max:120',
                'state' => 'required | string | max:120',
                'country' => 'required | string | max:120',
                'name' => 'required | string | max:120',
                'user_id' => 'nullable | exists:users,id'
            ]);
        } catch (ValidationException $e) {
            return $this->sendError('Validation Error.', Response::HTTP_BAD_REQUEST);
        }

        if ($input['user_id'] != null && $input['user_id'] != Auth::id()) {
            return $this->sendError('Not authorized to create address for this user.', Response::HTTP_UNAUTHORIZED);
        }

        $address = new Address($input);

        return $this->sendResponse('Address saved successfully.', ['address' => new AddressResource($address)]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Address $address)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Address $address)
    {
        //
    }
}
