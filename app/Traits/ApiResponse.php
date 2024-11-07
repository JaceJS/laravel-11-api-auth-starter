<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;

trait ApiResponse
{
    /**
     * Success response.
     *
     * @param string|array $data
     * @param string $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function successResponse($code = 200, $message = 'Success', $data)
    {
        return response()->json([
            'status' => $code,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    /**
     * Error response.
     *
     * @param string $message
     * @param int $code
     * @param string|array|null $data
     * @return \Illuminate\Http\JsonResponse
     */
    protected function errorResponse($code = 400, $message, $data = null)
    {
        Log::error(
            "Error response: {$message}",
            [
                'code' => $code,
                'data' => $data
            ]
        );

        return response()->json([
            'status' => $code,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    /**
     * Not found response.
     *
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function notFoundResponse($message = 'Resource not found')
    {
        Log::warning("Not found: {$message}");

        return $this->errorResponse($message, 404);
    }
}
