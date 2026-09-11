<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\Port;
use App\Models\Sensor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use LibreNMS\Util\Version;

class SystemController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json(['meta' => [
            'application' => [
                'name' => 'LibreNMS',
                'version' => Version::VERSION,
            ],
            'statistics' => [
                'devices' => Device::query()->hasAccess($user)->count(),
                'ports' => Port::query()->hasAccess($user)->count(),
                'sensors' => Sensor::query()->hasAccess($user)->count(),
            ],
        ]]);
    }
}
