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

    function validateOrError(Request $request, Array $rules, string $message = 'Validation Error.', int $code = Response::HTTP_BAD_REQUEST) {
        try {
            return $request->validate($rules);
        } catch (ValidationException $e) {
            return ResponseService::sendError($message, $code);
        }
    }
}
