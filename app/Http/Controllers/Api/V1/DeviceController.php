<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ResourceRequest;
use App\Http\Resources\Api\V1\DeviceResource;
use App\Http\Resources\Api\V1\ResourceCollection;
use App\Models\Device;
use App\Models\IsisAdjacency;
use App\Models\Port;

class DeviceController extends Controller
{
    public function index(ResourceRequest $request): ResourceCollection
    {
        $this->authorize('viewAny', Device::class);
        $request->allowIncludes([]);

        return DeviceResource::collection($request->paginate(
            Device::query()->hasAccess($request->user()),
            sorts: [
                'displayName' => 'display',
                'hostname' => 'hostname',
                'systemName' => 'sysName',
                'uptime' => 'uptime',
                'deviceType' => 'type',
                'os' => 'os',
                'lastPolledAt' => 'last_polled',
                'createdAt' => 'inserted',
            ],
            filters: [
                'id' => 'device_id',
                'search' => 'search',
                'displayName' => 'display',
                'hostname' => 'hostname',
                'systemName' => 'sysName',
                'isUp' => ['status', ResourceRequest::booleanFilter()],
                'deviceType' => 'type',
                'os' => 'os',
                'osVersion' => 'version',
                'osFeatures' => 'features',
                'hardware' => 'hardware',
                'isMonitored' => ['disabled', ResourceRequest::booleanFilter(inverted: true)],
                'isIgnored' => ['ignore', ResourceRequest::booleanFilter()],
                'isAlertingEnabled' => ['disable_notify', ResourceRequest::booleanFilter(inverted: true)],
            ],
        ));
    }

    public function show(ResourceRequest $request, Device $device): DeviceResource
    {
        $this->authorize('view', $device);
        $request->allowIncludes([]);

        return new DeviceResource($device);
    }

    /**
     * The device of a port, for the port's device relationship.
     */
    public function showForPort(ResourceRequest $request, Port $port): DeviceResource
    {
        $this->authorize('view', $port);
        abort_if($port->device === null, 404);

        return $this->show($request, $port->device);
    }

    /**
     * The device of an IS-IS adjacency, for the adjacency's device relationship.
     */
    public function showForIsisAdjacency(ResourceRequest $request, IsisAdjacency $isisAdjacency): DeviceResource
    {
        $this->authorize('view', $isisAdjacency);
        abort_if($isisAdjacency->device === null, 404);

        return $this->show($request, $isisAdjacency->device);
    }
}
