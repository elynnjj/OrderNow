<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InsufficientStockException extends Exception
{
    public array $details;

    public function __construct(string $message, array $details = [])
    {
        parent::__construct($message);
        $this->details = $details;
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'error'   => 'INSUFFICIENT_STOCK',
            'message' => $this->getMessage(),
            'details' => $this->details,
        ], 422);
    }
}
