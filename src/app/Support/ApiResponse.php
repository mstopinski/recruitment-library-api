<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ApiResponse
{
    public function success(mixed $data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    public function collection(AnonymousResourceCollection $resourceCollection, string $message = 'Success', int $code = 200): JsonResponse
    {
        $paginated = $resourceCollection->response()->getData(true);

        return response()->json(array_merge([
            'success' => true,
            'message' => $message,
        ], $paginated), $code);
    }

    public function error(string $message = 'Error', int $code = 400, mixed $data = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }
}
