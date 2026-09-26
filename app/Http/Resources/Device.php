<?php

namespace App\Http\Resources;

use App\Models\Secret;
use App\Models\Vminfo;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\PortAssociationMode;

/**
 * @mixin \App\Models\Device
 */
class Device extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $snmp = $this->polling()->snmp();
        $icmpMethod = $this->pollingMethod(PollingMethodType::Icmp);
        $snmpMethod = $this->pollingMethod(PollingMethodType::Snmp);
        $canUnmask = (bool) $request->user()?->can('unmask', Secret::class);

        $data = [
            'device_id' => $this->device_id,
            'hostname' => $this->hostname,
            'sysName' => $this->sysName,
            'ip' => $this->ip,
            'overwrite_ip' => $this->overwrite_ip,
            'community' => $canUnmask ? $snmp->community : null,
            'authlevel' => $snmp->authlevel,
            'authname' => $snmp->authname,
            'authpass' => $canUnmask ? $snmp->authpass : null,
            'authalgo' => $snmp->authalgo,
            'cryptopass' => $canUnmask ? $snmp->cryptopass : null,
            'cryptoalgo' => $snmp->cryptoalgo,
            'snmpver' => $snmp->version,
            'port' => $snmp->port,
            'transport' => $snmp->transport,
            'timeout' => $snmp->timeout,
            'retries' => $snmp->retries,
            'snmp_disable' => (int) ! $snmp->enabled,
            'bgpLocalAs' => $this->bgpLocalAs,
            'sysObjectID' => $this->sysObjectID,
            'sysDescr' => $this->sysDescr,
            'sysContact' => $this->sysContact,
            'version' => $this->version,
            'hardware' => $this->hardware,
            'features' => $this->features,
            'location_id' => $this->location_id,
            'os' => $this->os,
            'status' => (int) $this->status,
            'status_reason' => $this->status_reason,
            'ignore' => (int) $this->ignore,
            'disabled' => (int) $this->disabled,
            'uptime' => $this->uptime,
            'agent_uptime' => (int) ($this->agent_uptime ?? 0),
            'last_polled' => $this->last_polled?->toDateTimeString(),
            'last_poll_attempted' => $snmpMethod?->last_checked_at?->toDateTimeString() ?? $this->last_polled?->toDateTimeString(),
            'last_polled_timetaken' => $this->last_polled_timetaken,
            'last_discovered_timetaken' => $this->last_discovered_timetaken,
            'last_discovered' => $this->last_discovered?->toDateTimeString(),
            'last_ping' => $this->stats?->ping_last_timestamp?->toDateTimeString() ?? $icmpMethod?->last_checked_at?->toDateTimeString(),
            'last_ping_timetaken' => $this->stats->ping_rtt_last ?? null,
            'purpose' => $this->purpose,
            'type' => $this->type,
            'serial' => $this->serial,
            'icon' => $this->getRawOriginal('icon') ?? $this->attributes['icon'] ?? null,
            'poller_group' => $this->poller_group,
            'override_sysLocation' => (int) $this->override_sysLocation,
            'notes' => $this->notes,
            'port_association_mode' => PortAssociationMode::getId($snmp->portAssociationMode) ?? 1,
            'max_depth' => $this->max_depth,
            'disable_notify' => (int) $this->disable_notify,
            'inserted' => $this->inserted?->toDateTimeString(),
            'display' => $this->display,
            'display_template' => $this->display_template,
            'snmpEngineID' => $this->snmpEngineID,
            'ignore_status' => (int) $this->ignore_status,
            'mtu_status' => (int) $this->mtu_status,
            'location' => $this->location?->location,
            'lat' => $this->location?->lat,
            'lng' => $this->location?->lng,
        ];

        if ($this->relationLoaded('parents')) {
            $data['dependency_parent_id'] = $this->parents->pluck('device_id')->implode(',') ?: null;
            $data['dependency_parent_hostname'] = $this->parents->pluck('hostname')->implode(',') ?: null;
        }

        $hostId = Vminfo::guessFromDevice($this->resource)->value('device_id');
        if (is_numeric($hostId)) {
            $data['parent_id'] = (int) $hostId;
        }

        return $data;
    }
}
