<?php

namespace App\Support;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class ApiErrorResponse
{
    /**
     * Format an exception as a consistent JSON API error response.
     */
    public static function fromException(Throwable $e, Request $request, int $statusCode = 500): JsonResponse
    {
        $response = [
            'message' => self::getMessage($e, $statusCode),
        ];

        if ($e instanceof ValidationException) {
            $response['errors'] = $e->errors();
        }

        if (config('app.debug')) {
            $response['debug'] = [
                'exception' => \get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ];
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Get a user-friendly error message for the exception.
     */
    protected static function getMessage(Throwable $e, int $statusCode): string
    {
        if ($e instanceof ValidationException) {
            return $e->getMessage();
        }

        return match ($statusCode) {
            404 => 'The requested resource was not found.',
            401 => 'Unauthenticated.',
            403 => 'This action is unauthorized.',
            422 => 'The given data was invalid.',
            429 => 'Too many requests. Please try again later.',
            500 => config('app.debug') ? $e->getMessage() : 'An unexpected error occurred.',
            default => config('app.debug') ? $e->getMessage() : 'An error occurred.',
        };
    }

    /**
     * Determine the HTTP status code for the exception.
     */
    public static function getStatusCode(Throwable $e): int
    {
        if ($e instanceof ValidationException) {
            return 422;
        }

        if ($e instanceof ModelNotFoundException) {
            return 404;
        }

        if ($e instanceof HttpExceptionInterface) {
            return (int) $e->getStatusCode();
        }

        return 500;
    }

    /**
     * Check if the request should receive a JSON error response.
     */
    public static function shouldRenderJson(Request $request): bool
    {
        return $request->expectsJson() || $request->is('api/*');
    }
}
