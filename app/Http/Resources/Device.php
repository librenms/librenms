<?php

namespace App\Http\Resources;

use App\Models\DevicePollingMethod;
use App\Models\Secret;
use App\Models\Vminfo;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\PortAssociationMode;
use LibreNMS\Exceptions\SecretDecryptionException;
use LibreNMS\Polling\Method\Methods\SnmpPollingMethod;
use LibreNMS\Polling\Secrets\Data\SnmpSecretData;

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
        $canUnmask = (bool) $request->user()?->can('unmask', Secret::class);
        $snmpMethod = $this->pollingMethod(PollingMethodType::Snmp);
        $snmpSettings = $snmpMethod ? ($snmpMethod->settings ?? []) + (new SnmpPollingMethod)->defaults($this->resource) : [];
        $snmpSecret = $this->snmpSecret($snmpMethod);
        $snmpV3 = $snmpSecret?->version === 'v3' ? $snmpSecret : null;

        $data = [
            'device_id' => $this->device_id,
            'hostname' => $this->hostname,
            'sysName' => $this->sysName,
            'ip' => $this->ip,
            'overwrite_ip' => $this->overwrite_ip,
            'community' => $canUnmask && $snmpV3 === null ? $snmpSecret?->community : null,
            'authlevel' => $snmpV3?->authlevel,
            'authname' => $snmpV3?->authname,
            'authpass' => $canUnmask ? $snmpV3?->authpass : null,
            'authalgo' => $snmpV3?->authalgo,
            'cryptopass' => $canUnmask ? $snmpV3?->cryptopass : null,
            'cryptoalgo' => $snmpV3?->cryptoalgo,
            'snmpver' => $snmpSecret?->version,
            'port' => $snmpSettings['port'] ?? null,
            'transport' => $snmpSettings['transport'] ?? null,
            'timeout' => $snmpSettings['timeout'] ?? null,
            'retries' => $snmpSettings['retries'] ?? null,
            'snmp_disable' => (int) ! $this->polling()->isEnabled(PollingMethodType::Snmp),
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
            'agent_uptime' => 0, // no longer tracked
            'last_polled' => $this->last_polled?->toDateTimeString(),
            'last_poll_attempted' => null, // no longer tracked
            'last_polled_timetaken' => $this->last_polled_timetaken,
            'last_discovered_timetaken' => $this->last_discovered_timetaken,
            'last_discovered' => $this->last_discovered?->toDateTimeString(),
            'last_ping' => $this->stats?->ping_last_timestamp?->toDateTimeString(),
            'last_ping_timetaken' => $this->stats?->ping_rtt_last,
            'purpose' => $this->purpose,
            'type' => $this->type,
            'serial' => $this->serial,
            'icon' => $this->getRawOriginal('icon') ?? $this->attributes['icon'] ?? null,
            'poller_group' => $this->poller_group,
            'override_sysLocation' => (int) $this->override_sysLocation,
            'notes' => $this->notes,
            'port_association_mode' => PortAssociationMode::getId($snmpSettings['port_association_mode'] ?? ''),
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

    /**
     * Credentials are not needed to list devices, so a secret that can't be decrypted is left out.
     */
    private function snmpSecret(?DevicePollingMethod $snmpMethod): ?SnmpSecretData
    {
        if ($snmpMethod?->secret === null) {
            return null;
        }

        try {
            return SnmpSecretData::fromArray($snmpMethod->secret->data);
        } catch (SecretDecryptionException) {
            return null;
        }
    }
}
