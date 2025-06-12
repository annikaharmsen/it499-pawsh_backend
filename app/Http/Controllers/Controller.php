<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

abstract class Controller
{
    private Array $storeRules = [];
    private Array $updateRules = [];

    function sendResponse(string $message, Array $data = []): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], Response::HTTP_OK);
    }

    function sendError(string $message, int $status): JsonResponse {
        abort(
            response()->json([
            'success' => false,
            'message' => $message
        ], $status)
    );
    }

    function validateOrError(Request $request, Array $rules) {
        try {
            return $request->validate($rules);
        } catch (ValidationException $e) {
            return $this->sendError('Validation Error.', Response::HTTP_BAD_REQUEST);
        }
    }
}
