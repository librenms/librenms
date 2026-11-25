<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * @mixin \App\Models\Port
 */
class PortResource extends JsonApiResource
{
    public function toType(Request $request): string
    {
        return 'ports';
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(Request $request): array
    {
        return [
            'label' => $this->getLabel(...),
            'ifIndex' => $this->ifIndex,
            'name' => $this->ifName,
            'description' => $this->ifAlias,
            'hardwareDescription' => $this->ifDescr,
            'interfaceType' => $this->ifType,
            'speed' => $this->ifSpeed,
            'mtu' => $this->ifMtu,
            'duplex' => $this->ifDuplex,
            'macAddress' => $this->ifPhysAddress,
            'vlan' => $this->ifVlan,
            'trunk' => $this->ifTrunk,
            'administrativeStatus' => $this->ifAdminStatus,
            'operationalStatus' => $this->ifOperStatus,
            'isEnabled' => ! $this->disabled,
            'isIgnored' => (bool) $this->ignore,
            'isDeleted' => (bool) $this->deleted,
            'lastPolledAt' => $this->poll_time ? Carbon::createFromTimestampUTC($this->poll_time) : null,
        ];
    }

    /**
     * @return array<string, class-string<JsonApiResource>>
     */
    public function toRelationships(Request $request): array
    {
        return [
            'device' => DeviceResource::class,
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function toRelationshipLinks(Request $request): array
    {
        return [
            'device' => route('api.v1.ports.device', $this->port_id),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function toLinks(Request $request): array
    {
        return [
            'self' => route('api.v1.ports.show', $this->port_id),
        ];
    }
}
