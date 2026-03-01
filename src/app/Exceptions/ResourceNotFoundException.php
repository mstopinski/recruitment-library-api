<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use RuntimeException;

class ResourceNotFoundException extends RuntimeException
{
    public function __construct(string $resource, int|string $id)
    {
        parent::__construct("{$resource} with ID {$id} not found");
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'data' => null,
        ], 404);
    }
}
