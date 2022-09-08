<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

class Api
{
    static public function ok(mixed $output): JsonResponse
    {
        return response()->json($output);
    }

    static public function noContent(): JsonResponse
    {
        return response()->json('', 204);
    }

    static public function badRequest(mixed $output): JsonResponse
    {
        return response()->json($output, 400);
    }

    static public function notFound(mixed $output): JsonResponse
    {
        return response()->json($output, 404);
    }

    static public function internalServerError(mixed $output): JsonResponse
    {
        return response()->json($output, 500);
    }
}
