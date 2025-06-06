<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Exception;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse {
        try {
            $credentials = $request->validate( [
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required|email',
                'password' => 'required'
            ]);
        } catch (ValidationException $e) {
            return $this->sendError('Validation Error.', Response::HTTP_BAD_REQUEST);
        }

        $credentials['password'] = bcrypt($credentials['password']);

        try {
            $user = User::create($credentials);
        } catch (UniqueConstraintViolationException $e) {
            return $this->sendError('This email has already been registered.', Response::HTTP_BAD_REQUEST);
        }

        $token = $user->createToken('pawsh')->plainTextToken;

        return $this->sendResponse('User registered successfully.', ['token' => $token, 'user' => new UserResource($user)]);
    }

    public function login(Request $request): JsonResponse
    {
        try {
            $credentials = $request->validate([
                'email' => 'required | email',
                'password' => 'required'
            ]);
        } catch (ValidationException) {
            return $this->sendError('Validation Error.', Response::HTTP_BAD_REQUEST);
        }

        if (Auth::attempt($credentials)) {
            $token = Auth::user()->createToken('pawsh')->plainTextToken;

            return $this->sendResponse('Login Successful', [
                'token' => $token
            ]);
        }
        else {
            return $this->sendError('Invalid login details', 400);
        }
    }
}
