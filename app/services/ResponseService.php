<?php

namespace App\services;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class ResponseService {

    public static function sendError(string $message = 'Error fulfilling request.', int $code = Response::HTTP_BAD_REQUEST) {
        abort(
            response()->json([
                'success' => false,
                'message' => $message
            ],
            $code)
        );
    }

    public static function sendResponse(string $message = 'Request processed successfully.', Array|null $data = [], $code = Response::HTTP_OK): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ],
        $code);
    }

    public static function validateOrError(Request $request, Array $rules, string $message = 'Validation Error.', int $code = Response::HTTP_BAD_REQUEST) {
        try {
            return $request->validate($rules);
        } catch (ValidationException $e) {
            return self::sendError($message, $code);
        }
    }
}
