<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

abstract class Controller
{
    function sendResponse(string $message, Array $data): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], 200);
    }

    function sendError(string $message, int $status): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message
        ], $status);
    }
}
