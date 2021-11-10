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
use Illuminate\Support\Arr;
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
use LibreNMS\Polling\Secrets\SnmpSecret;
use LibreNMS\Polling\Secrets\SnmpSecretData;
use SnmpQuery;

class ValidateDeviceAndCreate
{
    public function __construct(private readonly Device $device, private readonly bool $force = false, private readonly bool $ping_fallback = false)
    {
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

            if (! app(DeviceIcmpIsAvailable::class)->execute($this->device)->isAlive()) {
                throw new HostUnreachablePingException($this->device->hostname);
            }

            $this->detectCredentials();

            if (! $this->device->snmp_disable) {
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
                            $secret->description = strtoupper($method->method_type->value) . ' ' . $this->device->hostname;
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
        if ($this->device->snmp_disable) {
            return;
        }

        $host_unreachable_exception = new HostUnreachableSnmpException($this->device->hostname);

        // Retrieve existing SNMP secret from relations if set
        $existingSnmpSecret = $this->device->relationLoaded('pollingMethods')
            ? $this->device->pollingMethods->firstWhere('method_type', PollingMethodType::Snmp)?->secret
            : null;

        if ($existingSnmpSecret !== null) {
            $secretData = $existingSnmpSecret->asSecretData(SnmpSecretData::class);
            $snmp_versions = [$secretData->version];
            if ($secretData->version === 'v3') {
                $v3_credentials = [[
                    'authlevel' => $secretData->authlevel,
                    'authname' => $secretData->authname,
                    'authpass' => $secretData->authpass,
                    'authalgo' => $secretData->authalgo,
                    'cryptopass' => $secretData->cryptopass,
                    'cryptoalgo' => $secretData->cryptoalgo,
                ]];
                $communities = [];
            } else {
                $communities = [$secretData->community];
                $v3_credentials = [];
            }
        } else {
            $snmp_versions = array_unique(LibrenmsConfig::get('snmp.version', []));
            $communities = array_unique(Arr::where(Arr::wrap(LibrenmsConfig::get('snmp.community')), fn ($community) => $community && is_string($community)));
            $v3_credentials = array_unique(LibrenmsConfig::get('snmp.v3', []), SORT_REGULAR);
        }

        // Keep track of other polling methods so we do not overwrite them when setting the relation
        $otherPollingMethods = collect();
        if ($this->device->relationLoaded('pollingMethods')) {
            $otherPollingMethods = $this->device->pollingMethods->filter(function ($m) {
                return $m->method_type !== PollingMethodType::Snmp;
            });
        }

        foreach ($snmp_versions as $snmp_version) {
            if ($snmp_version === 'v3') {
                // Try each set of parameters from config
                foreach ($v3_credentials as $v3) {
                    $snmpData = [
                        'version' => 'v3',
                        'authlevel' => $v3['authlevel'] ?? 'noAuthNoPriv',
                        'authname' => $v3['authname'] ?? null,
                        'authpass' => $v3['authpass'] ?? null,
                        'authalgo' => $v3['authalgo'] ?? 'SHA',
                        'cryptopass' => $v3['cryptopass'] ?? null,
                        'cryptoalgo' => $v3['cryptoalgo'] ?? 'AES',
                    ];

                    $secret = new Secret([
                        'secret_type' => SecretType::Snmp,
                        'data' => $snmpData,
                    ]);

                    $snmpMethod = new DevicePollingMethod([
                        'method_type' => PollingMethodType::Snmp,
                        'enabled' => true,
                        'affects_availability' => true,
                    ]);
                    $snmpMethod->setRelation('secret', $secret);

                    // Set the relation temporarily for testing
                    $this->device->setRelation('pollingMethods', $otherPollingMethods->concat([$snmpMethod]));

                    if (app(DeviceSnmpIsAvailable::class)->execute($this->device)) {
                        return;
                    } else {
                        $host_unreachable_exception->addReason($snmp_version, $snmpData['authname'] . '/' . $snmpData['authlevel']);
                    }
                }
            } elseif ($snmp_version === 'v2c' || $snmp_version === 'v1') {
                // try each community from config
                foreach ($communities as $community) {
                    $snmpData = [
                        'version' => $snmp_version,
                        'community' => $community,
                    ];

                    $secret = new Secret([
                        'secret_type' => SecretType::Snmp,
                        'data' => $snmpData,
                    ]);

                    $snmpMethod = new DevicePollingMethod([
                        'method_type' => PollingMethodType::Snmp,
                        'enabled' => true,
                        'affects_availability' => true,
                    ]);
                    $snmpMethod->setRelation('secret', $secret);

                    // Set the relation temporarily for testing
                    $this->device->setRelation('pollingMethods', $otherPollingMethods->concat([$snmpMethod]));

                    if (app(DeviceSnmpIsAvailable::class)->execute($this->device)) {
                        return;
                    } else {
                        $host_unreachable_exception->addReason($snmp_version, $community);
                    }
                }
            } else {
                throw new SnmpVersionUnsupportedException($snmp_version);
            }
        }

        if ($this->ping_fallback) {
            $this->device->snmp_disable = true;
            $this->device->os = 'ping';
            $this->device->setRelation('pollingMethods', $otherPollingMethods);

            return;
        }

        throw $host_unreachable_exception;
    }

    private function fillDefaultRelations(): void
    {
        if (! $this->device->relationLoaded('pollingMethods')) {
            $pollingMethods = collect();

            $pollingMethods->push(new DevicePollingMethod([
                'method_type' => PollingMethodType::Icmp,
                'enabled' => true,
                'affects_availability' => false,
            ]));

            if (! $this->device->snmp_disable) {
                $snmpMethod = new DevicePollingMethod([
                    'method_type' => PollingMethodType::Snmp,
                    'enabled' => true,
                    'affects_availability' => true,
                ]);

                $pollingMethods->push($snmpMethod);
            }

            $this->device->setRelation('pollingMethods', $pollingMethods);
        }
    }

    private function fillDefaults(): void
    {
        $this->device->port = $this->device->port ?: LibrenmsConfig::get('snmp.port', 161);
        $this->device->transport = $this->device->transport ?: LibrenmsConfig::get('snmp.transports.0', 'udp');
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
