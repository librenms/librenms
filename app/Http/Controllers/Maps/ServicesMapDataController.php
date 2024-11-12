<?php

namespace App\Http\Controllers\Maps;

use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use LibreNMS\Config;
use LibreNMS\Util\Url;

class ServicesMapDataController
{
    public function getServices(Request $request): JsonResponse
    {
        $group_id = $request->device_group;
        $services = Service::hasAccess($request->user())->with('device');

        if ($group_id) {
            $services->inDeviceGroup($group_id);
        }

        $service_list = [];
        foreach ($services->get() as $service) {
            if ($service->device->status) {
                $updowntime = \LibreNMS\Util\Time::formatInterval($service->device->uptime);
            } elseif ($service->device->last_polled) {
                $updowntime = \LibreNMS\Util\Time::formatInterval(time() - strtotime($service->device->last_polled));
            } else {
                $updowntime = '';
            }

            $service_list[] = [
                'id' => $service->service_id,
                'name' => $service->service_name,
                'type' => $service->service_type,
                'status' => $service->service_status,
                'icon' => $service->device->icon,
                'icontitle' => $service->device->icon ? str_replace(['.svg', '.png'], '', basename($service->device->icon)) : $service->device->os,
                'device_name' => $service->device->shortDisplayName(),
                'url' => Url::deviceUrl($service->device_id),
                'updowntime' => $updowntime,
                'compact' => Config::get('webui.availability_map_compact'),
                'box_size' => Config::get('webui.availability_map_box_size'),
            ];
        }

        return response()->json($service_list);
    }
}
