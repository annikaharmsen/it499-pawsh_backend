<?php

namespace App\services;

use Illuminate\Http\Response;

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
}
