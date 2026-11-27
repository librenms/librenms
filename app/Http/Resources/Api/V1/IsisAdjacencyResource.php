<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;

/**
 * @mixin \App\Models\IsisAdjacency
 */
class IsisAdjacencyResource extends JsonApiResource
{
    public function toType(Request $request): string
    {
        return 'isis-adjacencies';
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(Request $request): array
    {
        return [
            'index' => $this->index,
            'ifIndex' => $this->ifIndex,
            'circuitAdministrativeStatus' => $this->isisCircAdminState,
            'level1Metric' => $this->isisCircLevelMetricL1,
            'level1WideMetric' => $this->isisCircLevelWideMetricL1,
            'level2Metric' => $this->isisCircLevelMetricL2,
            'level2WideMetric' => $this->isisCircLevelWideMetricL2,
            'state' => $this->isisISAdjState,
            'neighborSystemType' => $this->isisISAdjNeighSysType,
            'neighborSystemId' => $this->isisISAdjNeighSysID,
            'neighborPriority' => $this->isisISAdjNeighPriority,
            'lastUpTime' => $this->isisISAdjLastUpTime,
            'neighborAreaAddress' => $this->isisISAdjAreaAddress,
            'neighborIpAddressType' => $this->isisISAdjIPAddrType,
            'neighborIpAddress' => $this->isisISAdjIPAddrAddress,
        ];
    }

    /**
     * @return array<string, class-string<JsonApiResource>>
     */
    public function toRelationships(Request $request): array
    {
        return [
            'device' => DeviceResource::class,
            'port' => PortResource::class,
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function toRelationshipLinks(Request $request): array
    {
        return [
            'device' => route('api.v1.routing.isis-adjacencies.device', $this->id),
            'port' => route('api.v1.routing.isis-adjacencies.port', $this->id),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function toLinks(Request $request): array
    {
        return [
            'self' => route('api.v1.routing.isis-adjacencies.show', $this->id),
        ];
    }
}
