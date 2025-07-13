<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class Handler extends \Illuminate\Foundation\Exceptions\Handler
{
    public function render($request, Throwable $e): JsonResponse
    {
        if ($request->is('api/*')) {
            return match (true) {
                $e instanceof AuthenticationException => response()->json([
                    'success' => false,
                    'message' => 'Unauthorized Request.',
                ], Response::HTTP_UNAUTHORIZED),

                $e instanceof ValidationException => response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $e->errors(),
                ], Response::HTTP_UNPROCESSABLE_ENTITY),

                $e instanceof NotFoundHttpException => response()->json([
                    'success' => false,
                    'message' => 'Resource not found.',
                ], Response::HTTP_NOT_FOUND),

                $e instanceof HttpException => response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], $e->getStatusCode()),

                $e instanceof RouteNotFoundException => response()->json([
                    'success' => false,
                    'message' => 'Route not found.',
                ], Response::HTTP_NOT_FOUND),

                default => response()->json([
                    'success' => false,
                    'message' => 'An unexpected error occurred.',
                    'details' => $e->getMessage(),
                ], Response::HTTP_INTERNAL_SERVER_ERROR),
            };
        }

        return parent::render($request, $e);
    }
}
