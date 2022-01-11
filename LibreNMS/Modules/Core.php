<?php
/**
 * Core.php
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Modules;

use App\Models\Device;
use Illuminate\Support\Str;
use LibreNMS\Config;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Util\Time;
use Log;
use SnmpQuery;

class Core implements Module
{
    public function discover(OS $os)
    {
        $snmpdata = SnmpQuery::numeric()->get(['SNMPv2-MIB::sysObjectID.0', 'SNMPv2-MIB::sysDescr.0', 'SNMPv2-MIB::sysName.0'])
            ->values();

        $device = $os->getDevice();
        $device->fill([
            'sysObjectID' => $snmpdata['.1.3.6.1.2.1.1.2.0'] ?? null,
            'sysName' => $snmpdata['.1.3.6.1.2.1.1.5.0'] ?? null,
            'sysDescr' => $snmpdata['.1.3.6.1.2.1.1.1.0'] ?? null,
        ]);

        foreach ($device->getDirty() as $attribute => $value) {
            Log::event($value . ' -> ' . $device->$attribute, $device, 'system', 3);
            $os->getDeviceArray()[$attribute] = $value; // update device array
        }

        // detect OS
        $device->os = self::detectOS($device, false);

        if ($device->isDirty('os')) {
            Log::event('Device OS changed: ' . $device->getOriginal('os') . ' -> ' . $device->os, $device, 'system', 3);
            $os->getDeviceArray()['os'] = $device->os;

            echo 'Changed ';
        }

        // Set type to a predefined type for the OS if it's not already set
        $loaded_os_type = Config::get("os.$device->os.type");
        if (! $device->getAttrib('override_device_type') && $loaded_os_type != $device->type) {
            $device->type = $loaded_os_type;
            Log::debug("Device type changed to $loaded_os_type!");
        }

        $device->save();

        echo 'OS: ' . Config::getOsSetting($device->os, 'text') . " ($device->os)\n\n";
    }

    public function poll(OS $os)
    {
        $snmpdata = SnmpQuery::numeric()
            ->get(['SNMPv2-MIB::sysDescr.0', 'SNMPv2-MIB::sysObjectID.0', 'SNMPv2-MIB::sysUpTime.0', 'SNMPv2-MIB::sysName.0'])
            ->values();

        $device = $os->getDevice();
        $device->fill([
            'sysName' => $snmpdata['.1.3.6.1.2.1.1.5.0'] ?? null,
            'sysObjectID' => $snmpdata['.1.3.6.1.2.1.1.2.0'] ?? null,
            'sysDescr' => $snmpdata['.1.3.6.1.2.1.1.1.0'] ?? null,
        ]);

        $this->calculateUptime($os, $snmpdata['.1.3.6.1.2.1.1.3.0'] ?? null);
        $device->save();
    }

    public function cleanup(OS $os)
    {
        // nothing to cleanup
    }

    /**
     * Detect the os of the given device.
     *
     * @param  Device  $device  device to check
     * @param  bool  $fetch  fetch sysDescr and sysObjectID fresh from the device
     * @return string the name of the os
     *
     * @throws \Exception
     */
    public static function detectOS(Device $device, bool $fetch = true): string
    {
        if ($fetch) {
            // some devices act oddly when getting both OIDs at once
            $device->sysDescr = SnmpQuery::device($device)->get('SNMPv2-MIB::sysDescr.0')->value();
            $device->sysObjectID = SnmpQuery::device($device)->numeric()->get('SNMPv2-MIB::sysObjectID.0')->value();
        }

        Log::debug("| $device->sysDescr | $device->sysObjectID | \n");

        $deferred_os = [];
        $generic_os = [
            'airos',
            'freebsd',
            'linux',
        ];

        // check yaml files
        $os_defs = Config::get('os');
        foreach ($os_defs as $os => $def) {
            if (isset($def['discovery']) && ! in_array($os, $generic_os)) {
                if (self::discoveryIsSlow($def)) {
                    // defer all os that use snmpget or snmpwalk
                    $deferred_os[] = $os;
                    continue;
                }

                foreach ($def['discovery'] as $item) {
                    if (self::checkDiscovery($device, $item, $def['mib_dir'] ?? null)) {
                        return $os;
                    }
                }
            }
        }

        // check deferred os
        $deferred_os = array_merge($deferred_os, $generic_os);
        foreach ($deferred_os as $os) {
            foreach ($os_defs[$os]['discovery'] as $item) {
                if (self::checkDiscovery($device, $item, $os_defs[$os]['mib_dir'] ?? null)) {
                    return $os;
                }
            }
        }

        return 'generic';
    }

    /**
     * Check an array of conditions if all match, return true
     * sysObjectID if sysObjectID starts with any of the values under this item
     * sysDescr if sysDescr contains any of the values under this item
     * sysDescr_regex if sysDescr matches any of the regexes under this item
     * snmpget perform an snmpget on `oid` and check if the result contains `value`. Other subkeys: options, mib, mibdir
     *
     * Appending _except to any condition will invert the match.
     *
     * @param  Device  $device
     * @param  array  $array  Array of items, keys should be sysObjectID, sysDescr, or sysDescr_regex
     * @param  string|array  $mibdir  MIB directory for evaluated OS
     * @return bool the result (all items passed return true)
     */
    protected static function checkDiscovery(Device $device, array $array, $mibdir): bool
    {
        // all items must be true
        foreach ($array as $key => $value) {
            if ($check = Str::endsWith($key, '_except')) {
                $key = substr($key, 0, -7);
            }

            if ($key == 'sysObjectID') {
                if (Str::startsWith($device['sysObjectID'], $value) == $check) {
                    return false;
                }
            } elseif ($key == 'sysDescr') {
                if (Str::contains($device['sysDescr'], $value) == $check) {
                    return false;
                }
            } elseif ($key == 'sysDescr_regex') {
                if (preg_match_any($device['sysDescr'], $value) == $check) {
                    return false;
                }
            } elseif ($key == 'sysObjectID_regex') {
                if (preg_match_any($device['sysObjectID'], $value) == $check) {
                    return false;
                }
            } elseif ($key == 'snmpget') {
                $get_value = SnmpQuery::device($device)
                    ->options($value['options'] ?? null)
                    ->mibDir($value['mib_dir'] ?? $mibdir)
                    ->get(isset($value['mib']) ? "{$value['mib']}::{$value['oid']}" : $value['oid'])
                    ->value();
                if (compare_var($get_value, $value['value'], $value['op'] ?? 'contains') == $check) {
                    return false;
                }
            } elseif ($key == 'snmpwalk') {
                $walk_value = SnmpQuery::device($device)
                    ->options($value['options'] ?? null)
                    ->mibDir($value['mib_dir'] ?? $mibdir)
                    ->walk(isset($value['mib']) ? "{$value['mib']}::{$value['oid']}" : $value['oid'])
                    ->raw();
                if (compare_var($walk_value, $value['value'], $value['op'] ?? 'contains') == $check) {
                    return false;
                }
            }
        }

        return true;
    }

    private function calculateUptime(OS $os, ?string $sysUpTime): void
    {
        global $agent_data;
        $device = $os->getDevice();

        if (Config::get("os.$device->os.bad_uptime")) {
            return;
        }

        if (! empty($agent_data['uptime'])) {
            $uptime = round((float) substr($agent_data['uptime'], 0, strpos($agent_data['uptime'], ' ')));
            echo "Using UNIX Agent Uptime ($uptime)\n";
        } else {
            $uptime_data = SnmpQuery::make()->get(['SNMP-FRAMEWORK-MIB::snmpEngineTime.0', 'HOST-RESOURCES-MIB::hrSystemUptime.0'])->values();

            $uptime = max(
                round($sysUpTime / 100),
                Config::get("os.$device->os.bad_snmpEngineTime") ? 0 : $uptime_data['SNMP-FRAMEWORK-MIB::snmpEngineTime.0'] ?? 0,
                Config::get("os.$device->os.bad_hrSystemUptime") ? 0 : round(($uptime_data['HOST-RESOURCES-MIB::hrSystemUptime.0'] ?? 0) / 100)
            );
            Log::debug("Uptime seconds: $uptime\n");
        }

        // set it if unless it is wrong
        if ($uptime > 0) {
            if ($uptime < $device->uptime) {
                Log::event('Device rebooted after ' . Time::formatInterval($device->uptime) . " -> {$uptime}s", $device, 'reboot', 4, $device->uptime);
                if (Config::get('discovery_on_reboot')) {
                    $device->last_discovered = null;
                    $device->save();
                }
            }

            app('Datastore')->put($os->getDeviceArray(), 'uptime', [
                'rrd_def' => RrdDefinition::make()->addDataset('uptime', 'GAUGE', 0),
            ], $uptime);

            $os->enableGraph('uptime');

            echo 'Uptime: ' . Time::formatInterval($uptime) . PHP_EOL;
            $device->uptime = $uptime;
        }
    }

    protected static function discoveryIsSlow($def): bool
    {
        foreach ($def['discovery'] as $item) {
            if (array_key_exists('snmpget', $item) || array_key_exists('snmpwalk', $item)) {
                return true;
            }
        }

        return false;
    }
}
