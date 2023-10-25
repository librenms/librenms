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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Modules;

use App\Models\Device;
use App\Models\Eventlog;
use App\Models\Location;
use LibreNMS\Enum\Severity;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Module;
use LibreNMS\Interfaces\Polling\OSPolling;
use LibreNMS\Polling\ModuleStatus;
use LibreNMS\Util\Url;

class Os implements Module
{
    /**
     * @inheritDoc
     */
    public function dependencies(): array
    {
        return [];
    }

    public function shouldDiscover(\LibreNMS\OS $os, ModuleStatus $status): bool
    {
        return $status->isEnabledAndDeviceUp($os->getDevice());
    }

    public function discover(\LibreNMS\OS $os): void
    {
        $this->updateLocation($os);
        $this->sysContact($os);

        // null out values in case they aren't filled.
        $os->getDevice()->fill([
            'hardware' => null,
            'version' => null,
            'features' => null,
            'serial' => null,
            'icon' => null,
        ]);

        $os->discoverOS($os->getDevice());
        $this->handleChanges($os);
    }

    public function shouldPoll(\LibreNMS\OS $os, ModuleStatus $status): bool
    {
        return $status->isEnabledAndDeviceUp($os->getDevice());
    }

    public function poll(\LibreNMS\OS $os, DataStorageInterface $datastore): void
    {
        $deviceModel = $os->getDevice(); /** @var \App\Models\Device $deviceModel */
        if ($os instanceof OSPolling) {
            $os->pollOS($datastore);
        } else {
            $device = $os->getDeviceArray();
            $location = null;

            if (is_file(base_path('/includes/polling/os/' . $device['os'] . '.inc.php'))) {
                // OS Specific
                Eventlog::log("Warning: OS {$device['os']} using deprecated polling method", $deviceModel, 'poller', Severity::Error);
                include base_path('/includes/polling/os/' . $device['os'] . '.inc.php');
            } elseif (! empty($device['os_group']) && is_file(base_path('/includes/polling/os/' . $device['os_group'] . '.inc.php'))) {
                // OS Group Specific
                Eventlog::log("Warning: OS {$device['os']} using deprecated polling method", $deviceModel, 'poller', Severity::Error);
                include base_path('/includes/polling/os/' . $device['os_group'] . '.inc.php');
            }

            // handle legacy variables, sometimes they are false
            $deviceModel->version = ($version ?? $deviceModel->version) ?: null;
            $deviceModel->hardware = ($hardware ?? $deviceModel->hardware) ?: null;
            $deviceModel->features = ($features ?? $deviceModel->features) ?: null;
            $deviceModel->serial = ($serial ?? $deviceModel->serial) ?: null;

            if (! empty($location)) { // legacy support, remove when no longer needed
                $deviceModel->setLocation($location);
                $deviceModel->location?->save();
            }
        }

        $this->handleChanges($os);
    }

    /**
     * @inheritDoc
     */
    public function cleanup(Device $device): void
    {
        // no cleanup needed
    }

    /**
     * @inheritDoc
     */
    public function dump(Device $device)
    {
        // get data fresh from the database
        return [
            'devices' => Device::where('device_id', $device->device_id)
            ->leftJoin('locations', 'location_id', 'id')
            ->select(['sysName', 'sysObjectID', 'sysDescr', 'sysContact', 'version', 'hardware', 'features', 'location', 'os', 'type', 'serial', 'icon'])
            ->get(),
        ];
    }

    private function handleChanges(\LibreNMS\OS $os): void
    {
        $device = $os->getDevice();

        $device->icon = basename(Url::findOsImage($device->os, $device->features, null, 'images/os/'));

        echo trans('device.attributes.location') . ': ' . $device->location?->display() . PHP_EOL;
        foreach (['hardware', 'version', 'features', 'serial'] as $attribute) {
            if (isset($device->$attribute)) {
                $device->$attribute = trim($device->$attribute);
            }
            echo \App\Observers\DeviceObserver::attributeChangedMessage($attribute, $device->$attribute, $device->getOriginal($attribute)) . PHP_EOL;
        }

        $device->save();
    }

    private function updateLocation(\LibreNMS\OS $os): void
    {
        $device = $os->getDevice();
        $new_location = $device->override_sysLocation ? new Location() : $os->fetchLocation(); // fetch location data from device
        $device->setLocation($new_location, true); // set location and lookup coordinates if needed
        $device->location?->save();
    }

    private function sysContact(\LibreNMS\OS $os): void
    {
        $device = $os->getDevice();
        $device->sysContact = snmp_get($os->getDeviceArray(), 'sysContact.0', '-Ovq', 'SNMPv2-MIB');
        $device->sysContact = str_replace(['', '"', '\n', 'not set'], '', $device->sysContact);
        if (empty($device->sysContact)) {
            $device->sysContact = null;
        }
    }
}
