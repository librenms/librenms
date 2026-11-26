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
            'displayName' => $this->display,
            'hostname' => $this->hostname,
            'ipAddress' => $this->ip,
            'description' => $this->purpose,
            'notes' => $this->notes,
            'systemName' => $this->sysName,
            'systemDescription' => $this->sysDescr,
            'systemContact' => $this->sysContact,
            'sysObjectID' => $this->sysObjectID,
            'isUp' => $this->status,
            'statusReason' => $this->status_reason,
            'uptime' => $this->uptime,
            'deviceType' => $this->type,
            'os' => $this->os,
            'osVersion' => $this->version,
            'osFeatures' => $this->features,
            'hardware' => $this->hardware,
            'serialNumber' => $this->serial,
            'bgpLocalAs' => $this->bgpLocalAs,
            'isMonitored' => ! $this->disabled,
            'isIgnored' => $this->ignore,
            'isStatusIgnored' => $this->ignore_status,
            'isAlertingEnabled' => ! $this->disable_notify,
            'lastPolledAt' => $this->last_polled,
            'lastPollDuration' => $this->last_polled_timetaken,
            'lastDiscoveredAt' => $this->last_discovered,
            'lastDiscoveryDuration' => $this->last_discovered_timetaken,
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
