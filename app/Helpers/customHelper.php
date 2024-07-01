<?php

use Illuminate\Http\JsonResponse;

function baseResponse($status , $message, $data, $error, $http_code): JsonResponse
{
    return response()->json([
        'message' => $message,
        'status' => $status,
        'data' => $data,
        'error' => $error
    ], $http_code);
}

?>
