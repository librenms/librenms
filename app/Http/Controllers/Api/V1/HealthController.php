<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Events\DiagnosingHealth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        try {
            Event::dispatch(new DiagnosingHealth);

            return response()->json(['meta' => [
                'status' => 'healthy',
            ]]);
        } catch (\Throwable) {
            return response()->json(['errors' => [[
                'status' => '503',
                'title' => 'Service Unavailable',
                'detail' => 'A required service is unavailable.',
            ]]], 503);
        }
    }
}
