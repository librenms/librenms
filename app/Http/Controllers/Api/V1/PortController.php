<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ResourceRequest;
use App\Http\Resources\Api\V1\PortResource;
use App\Http\Resources\Api\V1\ResourceCollection;
use App\Models\Device;
use App\Models\IsisAdjacency;
use App\Models\Port;

class PortController extends Controller
{
    /**
     * All ports, or the ports of one device for the device's ports relationship.
     */
    public function index(ResourceRequest $request, ?Device $device = null): ResourceCollection
    {
        $this->authorize('viewAny', Port::class);
        if ($device) {
            $this->authorize('view', $device);
        }
        $request->allowIncludes(['device']);

        return PortResource::collection($request->paginate(
            Port::query()
                ->hasAccess($request->user())
                ->when($device, fn ($query) => $query->where('ports.device_id', $device->device_id))
                ->with('device'), // the display name depends on the device os
            sorts: [
                'name' => 'ifName',
                'description' => 'ifAlias',
                'ifIndex' => 'ifIndex',
                'ifDescr' => 'ifDescr',
                'interfaceType' => 'ifType',
                'administrativeStatus' => 'ifAdminStatus',
                'operationalStatus' => 'ifOperStatus',
                'speed' => 'ifSpeed',
            ],
            filters: [
                'id' => 'port_id',
                'search' => 'search',
                'device' => 'device_id',
                'name' => 'ifName',
                'description' => 'ifAlias',
                'ifIndex' => 'ifIndex',
                'ifDescr' => 'ifDescr',
                'interfaceType' => 'ifType',
                'administrativeStatus' => 'ifAdminStatus',
                'operationalStatus' => 'ifOperStatus',
                'speed' => 'ifSpeed',
                'duplex' => 'ifDuplex',
                'mtu' => 'ifMtu',
                'macAddress' => 'ifPhysAddress',
                'untaggedVlan' => 'ifVlan',
                'isPollingEnabled' => ['disabled', ResourceRequest::booleanFilter(inverted: true)],
                'isIgnored' => ['ignore', ResourceRequest::booleanFilter()],
                'isDeleted' => ['deleted', ResourceRequest::booleanFilter()],
            ],
        ));
    }

    public function show(ResourceRequest $request, Port $port): PortResource
    {
        $this->authorize('view', $port);
        $request->allowIncludes(['device']);

        return new PortResource($port);
    }

    /**
     * The local port of an IS-IS adjacency, for the adjacency's port relationship.
     */
    public function showForIsisAdjacency(ResourceRequest $request, IsisAdjacency $isisAdjacency): PortResource
    {
        $this->authorize('view', $isisAdjacency);
        abort_if($isisAdjacency->port === null, 404);

        return $this->show($request, $isisAdjacency->port);
    }
}
