<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ResourceRequest;
use App\Http\Resources\Api\V1\IsisAdjacencyResource;
use App\Http\Resources\Api\V1\ResourceCollection;
use App\Models\IsisAdjacency;

class IsisAdjacencyController extends Controller
{
    public function index(ResourceRequest $request): ResourceCollection
    {
        $this->authorize('viewAny', IsisAdjacency::class);
        $request->allowIncludes(['device', 'port']);

        return IsisAdjacencyResource::collection($request->paginate(
            IsisAdjacency::query()
                ->hasAccess($request->user())
                // eager loaded so includes don't N+1; the port display name also needs its device os
                ->with(['device', 'port', 'port.device']),
            sorts: [
                'ifIndex' => 'ifIndex',
                'state' => 'isisISAdjState',
                'neighborSystemId' => 'isisISAdjNeighSysID',
                'neighborIpAddress' => 'isisISAdjIPAddrAddress',
            ],
            filters: [
                'id' => 'id',
                'search' => 'search',
                'device' => 'device_id',
                'port' => 'port_id',
                'ifIndex' => 'ifIndex',
                'state' => 'isisISAdjState',
                'neighborSystemId' => 'isisISAdjNeighSysID',
                'neighborIpAddress' => 'isisISAdjIPAddrAddress',
            ],
        ));
    }

    public function show(ResourceRequest $request, IsisAdjacency $isisAdjacency): IsisAdjacencyResource
    {
        $this->authorize('view', $isisAdjacency);
        $request->allowIncludes(['device', 'port']);

        return new IsisAdjacencyResource($isisAdjacency);
    }
}
