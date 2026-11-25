<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;

/**
 * @mixin \App\Models\Device
 */
class DeviceResource extends JsonApiResource
{
    public function toType(Request $request): string
    {
        return 'devices';
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(Request $request): array
    {
        return [
            'hostname' => $this->hostname,
            'displayName' => $this->display,
            'ipAddress' => $this->ip,
            'description' => $this->purpose,
            'systemName' => $this->sysName,
            'systemDescription' => $this->sysDescr,
            'systemContact' => $this->sysContact,
            'systemObjectId' => $this->sysObjectID,
            'os' => $this->os,
            'osVersion' => $this->version,
            'osFeatures' => $this->features,
            'hardware' => $this->hardware,
            'serialNumber' => $this->serial,
            'deviceType' => $this->type,
            'notes' => $this->notes,
            'bgpLocalAs' => $this->bgpLocalAs,
            'isUp' => $this->status,
            'statusReason' => $this->status_reason,
            'isEnabled' => ! $this->disabled,
            'isIgnored' => $this->ignore,
            'isStatusIgnored' => $this->ignore_status,
            'isAlertingEnabled' => ! $this->disable_notify,
            'uptime' => $this->uptime,
            'lastPollDuration' => $this->last_polled_timetaken,
            'lastDiscoveryDuration' => $this->last_discovered_timetaken,
            'lastPolledAt' => $this->last_polled,
            'lastDiscoveredAt' => $this->last_discovered,
            'createdAt' => $this->inserted,
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function toRelationshipLinks(Request $request): array
    {
        return [
            'ports' => route('api.v1.devices.ports', $this->device_id),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function toLinks(Request $request): array
    {
        return [
            'self' => route('api.v1.devices.show', $this->device_id),
        ];
    }
}
