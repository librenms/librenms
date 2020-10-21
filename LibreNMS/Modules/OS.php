<?php
/**
 * OS.php
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
 * @link       http://librenms.org
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Modules;

use LibreNMS\Config;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\Interfaces\Module;
use LibreNMS\Interfaces\Polling\OSPolling;
use LibreNMS\Util\Url;

class OS implements Module
{
    public function discover(\LibreNMS\OS $os)
    {
        $this->updateLocation($os);
        if ($os instanceof OSDiscovery) {
            // null out values in case they aren't filled.
            $os->getDevice()->fill([
                'hardware' => null,
                'version' => null,
                'features' => null,
                'serial' => null,
                'icon' => null,
            ]);

            $os->discoverOS($os->getDevice());
        }
        $this->handleChanges($os);
    }

    public function poll(\LibreNMS\OS $os)
    {
        $deviceModel = $os->getDevice();
        if ($os instanceof OSPolling) {
            $os->pollOS();
        } else {
            // legacy poller files
            global $graphs, $device;
            if (is_file(base_path('/includes/polling/os/' . $device['os'] . '.inc.php'))) {
                // OS Specific
                include base_path('/includes/polling/os/' . $device['os'] . '.inc.php');
            } elseif ($device['os_group'] && is_file(base_path('/includes/polling/os/' . $device['os_group'] . '.inc.php'))) {
                // OS Group Specific
                include base_path('/includes/polling/os/' . $device['os_group'] . '.inc.php');
            } else {
                echo "Generic :(\n";
            }

            // handle legacy variables, sometimes they are false
            $deviceModel->version = ($version ?? $deviceModel->version) ?: null;
            $deviceModel->hardware = ($hardware ?? $deviceModel->hardware) ?: null;
            $deviceModel->features = ($features ?? $deviceModel->features) ?: null;
            $deviceModel->serial = ($serial ?? $deviceModel->serial) ?: null;
            if (! empty($location)) {
                $deviceModel->setLocation($location);
            }
        }

        $this->handleChanges($os);
    }

    public function cleanup(\LibreNMS\OS $os)
    {
        // no cleanup needed?
    }

    private function handleChanges(\LibreNMS\OS $os)
    {
        $device = $os->getDevice();

        $device->icon = basename(Url::findOsImage($device->os, $device->features, null, 'images/os/'));

        echo trans('device.attributes.location') . ": $device->location\n";
        foreach (['hardware', 'version', 'features', 'serial'] as $attribute) {
            echo \App\Observers\DeviceObserver::attributeChangedMessage($attribute, $device->$attribute, $device->getOriginal($attribute)) . PHP_EOL;
        }

        $device->save();
    }

    private function updateLocation(\LibreNMS\OS $os, $altLocation = null)
    {
        $device = $os->getDevice();
        if ($device->override_sysLocation == 0) {
            $device->setLocation(snmp_get($os->getDeviceArray(), 'sysLocation.0', '-Ovq', 'SNMPv2-MIB'));
        }

        // make sure the location has coordinates
        if (Config::get('geoloc.latlng', true) && $device->location && ! $device->location->hasCoordinates()) {
            $device->location->lookupCoordinates();
            $device->location->save();
        }
    }
}
