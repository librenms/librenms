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
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Modules;

use App\Models\Device;
use LibreNMS\Config;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\Interfaces\Module;
use LibreNMS\Interfaces\Polling\OSPolling;
use LibreNMS\Util\Url;
use Log;

class OS implements Module
{
    public function discover(\LibreNMS\OS $os)
    {
        if ($os instanceof OSDiscovery) {
            $os->discoverOS();

            // TODO Location

            $this->handleChanges($os->getDeviceModel());
        }
    }

    public function poll(\LibreNMS\OS $os)
    {
        $deviceModel = $os->getDeviceModel();
        if ($os instanceof OSPolling) {
            $os->pollOS();
        } else {
            // legacy poller files
            $device = $os->getDevice();
            if (is_file(base_path('/includes/polling/os/' . $device['os'] . '.inc.php'))) {
                // OS Specific
                include base_path('/includes/polling/os/' . $device['os'] . '.inc.php');
            } elseif ($device['os_group'] && base_path('/includes/polling/os/' . $device['os_group'] . '.inc.php')) {
                // OS Group Specific
                include base_path('/includes/polling/os/' . $device['os_group'] . '.inc.php');
            } else {
                echo "Generic :(\n";
            }

            // handle legacy variables
            $deviceModel->version = $version ?? $deviceModel->version;
            $deviceModel->hardware = $hardware ?? $deviceModel->hardware;
            $deviceModel->features = $features ?? $deviceModel->features;
            $deviceModel->serial = $serial ?? $deviceModel->serial;

            if (!empty($location)) {
                set_device_location($location, $device, $update_array);
                $deviceModel->location_id = $device['location_id'];
            }
        }

        $this->handleChanges($deviceModel);
    }

    public function cleanup(\LibreNMS\OS $os)
    {
        // no cleanup needed
    }

    private function attributeChangedMessage(Device $device, $attribute)
    {
        return trans("device.attributes.$attribute") . ': '
            . ($device->isDirty($attribute) ? ($device->getOriginal($attribute) . ' -> ') : '')
            . $device->$attribute;
    }

    private function handleChanges(Device $device)
    {
        $device->icon = Url::findOsImage($device->os, $device->features, null, 'images/os/');

        foreach ($device->getDirty() as $attribute => $value) {
            if ($attribute == 'location_id') {
                $attribute = 'location';
            }

            Log::event($this->attributeChangedMessage($device, $attribute), $device, 'system', 3);
        }

        foreach (['location', 'hardware', 'version', 'features', 'serial'] as $attribute) {
            echo $this->attributeChangedMessage($device, $attribute) . PHP_EOL;
        }

        $device->save();
    }
}
