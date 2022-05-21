<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\JsonResponse;
use LibreNMS\Services;

class ServiceCheckController extends Controller
{
    public function types(): JsonResponse
    {
        return response()->json(Services::list());
    }

    public function params(string $type): JsonResponse
    {
        $service = new Service(['service_type' => $type]);
        $check = Services::makeCheck($service);

        return response()->json($check->availableParameters()->map(function (Services\CheckParameter $param) {
            return $param->toEscapedArray();
        })->values());
    }
}
