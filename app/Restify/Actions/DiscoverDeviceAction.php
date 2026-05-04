<?php

namespace App\Restify\Actions;

use App\Models\Device;
use Binaryk\LaravelRestify\Actions\Action;
use Binaryk\LaravelRestify\Http\Requests\ActionRequest;
use Illuminate\Http\JsonResponse;

class DiscoverDeviceAction extends Action
{
    public static string $uriKey = 'discover';

    public string $description = 'Schedule a fresh discovery run by clearing the last_discovered timestamp.';

    public function handle(ActionRequest $request, Device $device): JsonResponse
    {
        // Mirrors device_discovery_trigger() in includes/functions.php  clearing
        // last_discovered makes the next polling cycle treat the device as fresh.
        $device->last_discovered = null;
        $device->save();

        return response()->json([
            'data' => [
                'message' => 'Device queued for rediscovery',
                'device_id' => $device->device_id,
            ],
        ]);
    }
}
