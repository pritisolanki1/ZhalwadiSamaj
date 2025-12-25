<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponser
{
    /**
     * @param  null  $message
     */
    protected function successResponse($message = null, $data = null, int $code = 200): JsonResponse
    {
        return response()->json([
            'status'  => 'Success',
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    /**
     * @param  null  $message
     * @param  null  $data
     */
    protected function errorResponse($message = null, $data = null, int $code = 500): JsonResponse
    {
        return response()->json([
            'status'  => 'Error',
            'message' => $message,
            'data'    => $data,
        ], $code);
    }
}
