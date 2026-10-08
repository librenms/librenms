<?php

/**
 * AutoDiscoverDevice.php
 *
 * Add a device that was found through another device (LLDP, CDP, OSPF, ARP, etc.)
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Actions\Device;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\Eventlog;
use App\Models\Port;
use Exception;
use Illuminate\Support\Facades\Log;
use LibreNMS\Enum\Severity;
use LibreNMS\Exceptions\HostExistsException;
use LibreNMS\Util\IP;
use LibreNMS\Util\Validate;

class AutoDiscoverDevice
{
    /**
     * @param  string  $target  hostname or ip of the device to add
     * @param  Device  $source  the device $target was found through
     * @param  string  $method  how $target was found (LLDP, CDP, OSPF, etc.)
     * @param  Port|null  $port  the port on $source that $target was found on
     */
    public function execute(string $target, Device $source, string $method, ?Port $port = null): ?Device
    {
        Log::debug("Discovering $target");

        if (empty(LibrenmsConfig::get('nets'))) {
            Log::debug('Allowed discovery network list is empty - skipping');

            return null;
        }

        if (IP::isValid($target)) {
            if (! LibrenmsConfig::get('discovery_by_ip', false)) {
                Log::debug("Discovery by IP disabled, skipping $target");
                Eventlog::log("$method discovery of $target failed - Discovery by IP disabled", $source, 'discovery', Severity::Warning);

                return null;
            }

            $ip = $target;
        } elseif (Validate::hostname($target)) {
            $target = $this->applyDomain(rtrim($target, '.'));
            $ip = gethostbyname($target);

            if ($ip == $target) {
                Log::debug("Name lookup of $target failed");
                Eventlog::log("$method discovery of $target failed - Check name lookup", $source, 'discovery', Severity::Error);

                return null;
            }
        } else {
            Log::debug("Discovery failed: '$target' is not a valid ip or dns name");

            return null;
        }

        Log::debug("IP lookup result: $ip");
        $ip = IP::parse($ip, true);

        if ($ip === null || $ip->inNetworks(LibrenmsConfig::get('autodiscovery.nets-exclude'))) {
            Log::debug("$ip in an excluded network - skipping");

            return null;
        }

        if (! $ip->inNetworks(LibrenmsConfig::get('nets'))) {
            Log::debug("$ip not in a matched network - skipping");

            return null;
        }

        $device = new Device([
            'hostname' => $target,
            'poller_group' => $source->poller_group,
        ]);

        try {
            if ((new ValidateDeviceAndCreate($device))->execute()) {
                Log::info("+[$device->hostname($device->device_id)]");
                $via = $port ? ' (port ' . $port->getLabel() . ')' : '';
                Eventlog::log("Device $device->hostname ($ip)$via autodiscovered through $method on $source->hostname", $source, 'discovery', Severity::Ok);

                return $device;
            }

            Eventlog::log("$method discovery of $device->hostname ($ip) failed - Check ping and SNMP access", $source, 'discovery', Severity::Error);
        } catch (HostExistsException) {
            // already have this device
        } catch (Exception $e) {
            Eventlog::log("$method discovery of $target ($ip) failed - " . $e->getMessage(), $source, 'discovery', Severity::Error);
        }

        return null;
    }

    /**
     * Append the configured domain, if the result resolves
     */
    private function applyDomain(string $hostname): string
    {
        $domain = trim((string) LibrenmsConfig::get('mydomain'), '.');

        if ($domain === '' || str_ends_with($hostname, ".$domain")) {
            return $hostname;
        }

        $full_host = "$hostname.$domain";

        $resolves = gethostbyname($full_host) != $full_host || ! empty(rescue(fn () => dns_get_record($full_host), [], false));

        return $resolves ? $full_host : $hostname;
    }
}
