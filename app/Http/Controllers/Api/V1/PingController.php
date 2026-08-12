<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class PingController extends Controller
{
    /**
     * Public, unauthenticated liveness probe. Confirms the application is
     * up and serving requests.
     */
    public function __invoke(): JsonResponse
    {
        return response()->json(['meta' => [
            "status" => "healthy",
            "message" => "pong",
            "timestamp" => now()->toIso8601ZuluString(),
      ]]);
    }
}
