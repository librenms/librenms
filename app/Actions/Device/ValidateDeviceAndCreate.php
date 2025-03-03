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

use App\Models\Device;
use Illuminate\Support\Arr;
use LibreNMS\Config;
use LibreNMS\Enum\PortAssociationMode;
use LibreNMS\Exceptions\HostIpExistsException;
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
     * @var \App\Models\Device
     */
    private $device;
    /**
     * @var bool
     */
    private $force;
    /**
     * @var bool
     */
    private $ping_fallback;
    /**
     * @var \LibreNMS\Polling\ConnectivityHelper
     */
    private $connectivity;

    public function __construct(Device $device, bool $force = false, bool $ping_fallback = false)
    {
        $this->device = $device;
        $this->force = $force;
        $this->ping_fallback = $ping_fallback;
        $this->connectivity = new \LibreNMS\Polling\ConnectivityHelper($this->device);
    }

    /**
     * @return bool
     *
     * @throws \LibreNMS\Exceptions\HostExistsException
     * @throws \LibreNMS\Exceptions\HostUnreachablePingException
     * @throws \LibreNMS\Exceptions\HostUnreachableException
     * @throws \LibreNMS\Exceptions\SnmpVersionUnsupportedException
     */
    public function execute(): bool
    {
        if ($this->device->exists) {
            return false;
        }

        $this->exceptIfHostnameExists();
        $this->fillDefaults();

        if (! $this->force) {
            $this->exceptIfIpExists();

            if (! $this->connectivity->isPingable()->success()) {
                throw new HostUnreachablePingException($this->device->hostname);
            }

            $this->detectCredentials();
            $this->cleanCredentials();

            if (! $this->device->snmp_disable) {
                $this->device->sysName = SnmpQuery::device($this->device)->get('SNMPv2-MIB::sysName.0')->value();
                $this->exceptIfSysNameExists();

                $this->device->os = Core::detectOS($this->device);
            }
        }

        return $this->device->save();
    }

    /**
     * @throws \LibreNMS\Exceptions\HostUnreachableException
     * @throws \LibreNMS\Exceptions\SnmpVersionUnsupportedException
     */
    private function detectCredentials(): void
    {
        if ($this->device->snmp_disable) {
            return;
        }

        $host_unreachable_exception = new HostUnreachableSnmpException($this->device->hostname);

        // which snmp version should we try (and in what order)
        $snmp_versions = $this->device->snmpver ? [$this->device->snmpver] : Config::get('snmp.version');

        $communities = Arr::where(Arr::wrap(Config::get('snmp.community')), function ($community) {
            return $community && is_string($community);
        });
        if ($this->device->community) {
            array_unshift($communities, $this->device->community);
        }
        $communities = array_unique($communities);

        $v3_credentials = Config::get('snmp.v3');
        array_unshift($v3_credentials, $this->device->only(['authlevel', 'authname', 'authpass', 'authalgo', 'cryptopass', 'cryptoalgo']));
        $v3_credentials = array_unique($v3_credentials, SORT_REGULAR);

        foreach ($snmp_versions as $snmp_version) {
            $this->device->snmpver = $snmp_version;

            if ($snmp_version === 'v3') {
                // Try each set of parameters from config
                foreach ($v3_credentials as $v3) {
                    $this->device->fill(Arr::only($v3, ['authlevel', 'authname', 'authpass', 'authalgo', 'cryptopass', 'cryptoalgo']));

                    if ($this->connectivity->isSNMPable()) {
                        return;
                    } else {
                        $host_unreachable_exception->addReason($snmp_version, $this->device->authname . '/' . $this->device->authlevel);
                    }
                }
            } elseif ($snmp_version === 'v2c' || $snmp_version === 'v1') {
                // try each community from config
                foreach ($communities as $community) {
                    $this->device->community = $community;
                    if ($this->connectivity->isSNMPable()) {
                        return;
                    } else {
                        $host_unreachable_exception->addReason($snmp_version, $this->device->community);
                    }
                }
            } else {
                throw new SnmpVersionUnsupportedException($snmp_version);
            }
        }

        if ($this->ping_fallback) {
            $this->device->snmp_disable = true;
            $this->device->os = 'ping';

            return;
        }

        throw $host_unreachable_exception;
    }

    private function cleanCredentials(): void
    {
        if ($this->device->snmpver == 'v3') {
            $this->device->community = null;
        } else {
            $this->device->authlevel = null;
            $this->device->authname = null;
            $this->device->authalgo = null;
            $this->device->cryptopass = null;
            $this->device->cryptoalgo = null;
        }
    }

    private function fillDefaults(): void
    {
        $this->device->port = $this->device->port ?: Config::get('snmp.port', 161);
        $this->device->transport = $this->device->transport ?: Config::get('snmp.transports.0', 'udp');
        $this->device->poller_group = $this->device->poller_group ?: Config::get('default_poller_group', 0);
        $this->device->os = $this->device->os ?: 'generic';
        $this->device->status_reason = '';
        $this->device->sysName = $this->device->sysName ?: $this->device->hostname;
        $this->device->port_association_mode = $this->device->port_association_mode ?: Config::get('default_port_association_mode', 'ifIndex');
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
        } elseif (Config::get('addhost_alwayscheckip')) {
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
        if (Config::get('allow_duplicate_sysName')) {
            return;
        }

        if (Device::where('sysName', $this->device->sysName)
            ->when(Config::get('mydomain'), function ($query, $domain) {
                $query->orWhere('sysName', rtrim($this->device->sysName, '.') . '.' . $domain);
            })->exists()) {
            throw new HostSysnameExistsException($this->device->hostname, $this->device->sysName);
        }
    }
}
