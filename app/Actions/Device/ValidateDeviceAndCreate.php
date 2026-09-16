<?php

/*
 * ValidateDeviceAndCreate.php
 *
 * -Description-
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2022 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Actions\Device;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\Models\Secret;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\PortAssociationMode;
use LibreNMS\Enum\SecretType;
use LibreNMS\Exceptions\HostIpExistsException;
use LibreNMS\Exceptions\HostNameEmptyException;
use LibreNMS\Exceptions\HostnameExistsException;
use LibreNMS\Exceptions\HostSysnameExistsException;
use LibreNMS\Exceptions\HostUnreachablePingException;
use LibreNMS\Exceptions\HostUnreachableSnmpException;
use LibreNMS\Exceptions\SnmpVersionUnsupportedException;
use LibreNMS\Modules\Core;
use SnmpQuery;

class ValidateDeviceAndCreate
{
    /**
     * @param  array<string, mixed>  $input
     */
    public function __construct(
        private readonly Device $device,
        private readonly bool $force = false,
        private readonly bool $ping_fallback = false,
        private readonly array $input = [],
        private readonly BuildDefaultPollingMethods $builder = new BuildDefaultPollingMethods,
    ) {
    }

    /**
     * @return bool
     *
     * @throws \LibreNMS\Exceptions\HostExistsException
     * @throws HostUnreachablePingException
     * @throws \LibreNMS\Exceptions\HostUnreachableException
     * @throws SnmpVersionUnsupportedException
     */
    public function execute(): bool
    {
        if (empty($this->device->hostname)) {
            throw new HostNameEmptyException();
        }

        if ($this->device->exists) {
            return false;
        }

        $this->exceptIfHostnameExists();
        $this->fillDefaults();
        $this->fillDefaultRelations();

        if (! $this->force) {
            $this->exceptIfIpExists();

            $icmpMethod = $this->device->pollingMethod(PollingMethodType::Icmp)
                ?? DevicePollingMethod::transient(PollingMethodType::Icmp, device: $this->device, affectsAvailability: false);
            if (! PollingMethodType::Icmp->definition()->probe()->check($this->device)->isSuccess()) {
                throw new HostUnreachablePingException($this->device->hostname);
            }

            $this->detectCredentials();

            if (! $this->device->getAttribute('snmp_disable')) {
                $this->device->sysName = SnmpQuery::device($this->device)->get('SNMPv2-MIB::sysName.0')->value();
                $this->exceptIfSysNameExists();

                $this->device->os = Core::detectOS($this->device);
            }
        }

        $saved = $this->device->save();

        if ($saved) {
            if ($this->device->relationLoaded('pollingMethods')) {
                foreach ($this->device->pollingMethods as $method) {
                    $method->device_id = $this->device->device_id;

                    // If the method has an associated secret, save it first to get its ID
                    if ($method->relationLoaded('secret') && $method->secret) {
                        $secret = $method->secret;
                        if (! $secret->exists && empty($secret->description)) {
                            $secret->description = strtoupper((string) $method->method_type->value) . ' ' . $this->device->hostname;
                        }
                        $secret->save();
                        $method->secret_id = $secret->id;
                    }

                    $method->save();
                }
            }
        }

        return $saved;
    }

    /**
     * @throws \LibreNMS\Exceptions\HostUnreachableException
     * @throws SnmpVersionUnsupportedException
     */
    private function detectCredentials(): void
    {
        if ($this->device->getAttribute('snmp_disable')) {
            return;
        }

        $host_unreachable_exception = new HostUnreachableSnmpException($this->device->hostname);

        // Keep track of other polling methods so we do not overwrite them when setting the relation
        $otherPollingMethods = collect();
        if ($this->device->relationLoaded('pollingMethods')) {
            $otherPollingMethods = $this->device->pollingMethods->filter(fn ($m) => $m->method_type !== PollingMethodType::Snmp);
        }

        $existingSnmpMethod = $this->device->relationLoaded('pollingMethods')
            ? $this->device->pollingMethods->firstWhere('method_type', PollingMethodType::Snmp)
            : null;

        // If a specific secret or specific secret_data was supplied on the method, test that directly
        if ($existingSnmpMethod !== null && ($existingSnmpMethod->secret !== null || ! empty(array_filter($existingSnmpMethod->getSecretData())))) {
            if (PollingMethodType::Snmp->definition()->probe()->check($this->device)->isSuccess()) {
                return;
            }

            $secretData = $existingSnmpMethod->getSecretData();
            $version = $secretData['version'] ?? 'unknown';
            $target = $existingSnmpMethod->secret?->description ?? ($secretData['community'] ?? ($secretData['authname'] ?? 'custom'));
            $host_unreachable_exception->addReason($version, (string) $target);
        } else {
            // Otherwise, attempt ordered default credentials
            /** @var array<int, int> $defaultSecretIds */
            $defaultSecretIds = (array) LibrenmsConfig::get('snmp.default_credentials', []);
            $defaultSecrets = Secret::where('secret_type', SecretType::Snmp)
                ->whereIn('id', $defaultSecretIds)
                ->get()
                ->sortBy(fn ($s) => array_search($s->id, $defaultSecretIds));

            $settings = $existingSnmpMethod?->getSettings() ?? [];

            foreach ($defaultSecrets as $secret) {
                $secretData = $secret->toSecretData();
                $snmpMethod = DevicePollingMethod::transient(
                    PollingMethodType::Snmp,
                    settings: $settings,
                    secretData: $secretData->toArray(),
                    device: $this->device,
                    affectsAvailability: true,
                );
                $snmpMethod->setRelation('secret', $secret);
                $snmpMethod->secret_id = $secret->id;

                // Set relation temporarily for probe check
                $this->device->setRelation('pollingMethods', $otherPollingMethods->concat([$snmpMethod]));

                if (PollingMethodType::Snmp->definition()->probe()->check($this->device)->isSuccess()) {
                    return;
                }

                $host_unreachable_exception->addReason($secretData->version, $secret->description);
            }
        }

        if ($this->ping_fallback) {
            $this->device->setAttribute('snmp_disable', true);
            $this->device->os = 'ping';
            $this->device->setRelation('pollingMethods', $otherPollingMethods);

            return;
        }

        throw $host_unreachable_exception;
    }

    private function fillDefaultRelations(): void
    {
        if (! $this->device->relationLoaded('pollingMethods')) {
            $pollingMethods = $this->builder->execute($this->device, $this->input);
            $this->device->setRelation('pollingMethods', $pollingMethods);
        }
    }

    private function fillDefaults(): void
    {
        $this->device->poller_group = $this->device->poller_group ?: LibrenmsConfig::get('default_poller_group', 0);
        $this->device->os = $this->device->os ?: 'generic';
        $this->device->status_reason = '';
        $this->device->sysName = $this->device->sysName ?: $this->device->hostname;
        $this->device->port_association_mode = $this->device->port_association_mode ?: LibrenmsConfig::get('default_port_association_mode', 'ifIndex');
        if (! is_int($this->device->port_association_mode)) {
            $this->device->port_association_mode = PortAssociationMode::getId($this->device->port_association_mode) ?? 1;
        }
    }

    /**
     * @throws \LibreNMS\Exceptions\HostExistsException
     */
    private function exceptIfHostnameExists(): void
    {
        if (Device::where('hostname', $this->device->hostname)->exists()) {
            throw new HostnameExistsException($this->device->hostname);
        }
    }

    /**
     * @throws \LibreNMS\Exceptions\HostExistsException
     */
    private function exceptIfIpExists(): void
    {
        if ($this->device->overwrite_ip) {
            $ip = $this->device->overwrite_ip;
        } elseif (LibrenmsConfig::get('addhost_alwayscheckip')) {
            $ip = gethostbyname($this->device->hostname);
        } else {
            $ip = $this->device->hostname;
        }

        $existing = Device::findByIp($ip);

        if ($existing) {
            throw new HostIpExistsException($this->device->hostname, $existing->hostname, $ip);
        }
    }

    /**
     * Check if a device with match hostname or sysname exists in the database.
     * Throw and error if they do.
     *
     * @return void
     *
     * @throws \LibreNMS\Exceptions\HostExistsException
     */
    private function exceptIfSysNameExists(): void
    {
        if (LibrenmsConfig::get('allow_duplicate_sysName')) {
            return;
        }

        if (Device::where('sysName', $this->device->sysName)
            ->when(LibrenmsConfig::get('mydomain'), function ($query, $domain): void {
                $query->orWhere('sysName', rtrim($this->device->sysName, '.') . '.' . $domain);
            })->exists()) {
            throw new HostSysnameExistsException($this->device->hostname, $this->device->sysName);
        }
    }
}
