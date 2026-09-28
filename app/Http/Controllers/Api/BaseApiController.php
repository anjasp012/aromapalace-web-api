<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class BaseApiController extends Controller
{
    /**
     * Standard success JSON response for Mobile & Web clients
     */
    public function sendResponse(mixed $data = null, string $message = 'Success', int $code = 200, ?array $meta = null): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];

        if ($meta !== null) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $code);
    }

    /**
     * Standard error JSON response for Mobile & Web clients
     */
    public function sendError(string $message = 'Error', mixed $errors = [], int $code = 400): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }
}

