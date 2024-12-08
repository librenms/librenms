<?php
/*
 * Linux.php
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
 * @copyright  2023 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\EntPhysical;
use Illuminate\Support\Collection;
use LibreNMS\Interfaces\Discovery\VminfoDiscovery;
use LibreNMS\OS\Traits\VminfoLibvirt;
use LibreNMS\OS\Traits\VminfoVmware;
use LibreNMS\Util\StringHelpers;
use SnmpQuery;

class Linux extends Shared\Unix implements VminfoDiscovery
{
    // NOTE: Only Linux specific stuff should go here, most things should be in Unix

    use VminfoLibvirt, VminfoVmware {
        VminfoLibvirt::discoverVminfo as discoverLibvirtVminfo;
        VminfoVmware::discoverVmInfo as discoverVmwareVminfo;
    }

    public function discoverVmInfo(): Collection
    {
        $vms = $this->discoverLibvirtVminfo();

        if ($vms->isNotEmpty()) {
            return $vms;
        }

        return $this->discoverVmwareVminfo();
    }

    public function discoverEntityPhysical(): Collection
    {
        return $this->discoverLsiMegaRaidInventory();
    }

    private function discoverLsiMegaRaidInventory(): Collection
    {
        $inventory = new Collection;

        $controller_array = SnmpQuery::hideMib()->walk('LSI-MegaRAID-SAS-MIB::adapterInfoTable')->table(1);

        if (empty($controller_array)) {
            return $inventory; // no controllers, skip the rest.
        }

        foreach ($controller_array as $controller) {
            $inventory->push(new EntPhysical([
                'entPhysicalIndex' => 200 + $controller['adapterID-AIT'],
                'entPhysicalParentRelPos' => $controller['adapterID-AIT'] ?? -1,
                'entPhysicalDescr' => '/C' . $controller['adapterID-AIT'],
                'entPhysicalClass' => 'port',
                'entPhysicalModelName' => $controller['productName'],
                'entPhysicalSerialNum' => $controller['serialNo'],
                'entPhysicalContainedIn' => '0',
                'entPhysicalVendorType' => $controller['adapterVendorID'],
                'entPhysicalFirmwareRev' => $controller['firmwareVersion'],
            ]));
        }

        $bbus = SnmpQuery::hideMib()->walk('LSI-MegaRAID-SAS-MIB::bbuTable')->table(1);
        foreach ($bbus as $bbu) {
            $inventory->push(new EntPhysical([
                'entPhysicalIndex' => 1000 + $bbu['pdIndex'],
                'entPhysicalClass' => 'charge',
                'entPhysicalModelName' => $bbu['deviceName'],
                'entPhysicalSerialNum' => $bbu['serialNumber'],
                'entPhysicalContainedIn' => 200 + $bbu['adpID'],
                'entPhysicalIsFRU' => 'true',
                'entPhysicalFirmwareRev' => $bbu['firmwareStatus'],
            ]));
        }

        $enclosures = SnmpQuery::hideMib()->walk('LSI-MegaRAID-SAS-MIB::enclosureTable')->table(1);
        foreach ($enclosures as $enclosure) {
            $inventory->push(new EntPhysical([
                'entPhysicalIndex' => 210 + $enclosure['deviceId'],
                'entPhysicalVendorType' => $enclosure['slotCount'],
                'entPhysicalParentRelPos' => $enclosure['deviceId'] ?? -1,
                'entPhysicalDescr' => '/C' . ($enclosure['adapterID-ET'] ?? '0') . '/E' . $enclosure['deviceId'],
                'entPhysicalClass' => 'chassis',
                'entPhysicalModelName' => $enclosure['productID'],
                'entPhysicalSerialNum' => $enclosure['enclSerialNumber'],
                'entPhysicalContainedIn' => 200 + $enclosure['adapterID-ET'],
                'entPhysicalMfgName' => $enclosure['vendorID'],
                'entPhysicalFirmwareRev' => $this->handleHex($enclosure['enclFirmwareVersion']),
            ]));
        }

        $drives = SnmpQuery::hideMib()->walk('LSI-MegaRAID-SAS-MIB::physicalDriveTable')->table(1);
        foreach ($drives as $drive) {
            $enclDeviceId = $drive['enclDeviceId'] ?? 0;
            $inventory->push(new EntPhysical([
                'entPhysicalIndex' => 500 + $enclDeviceId * 100 + $drive['physDevID'],
                'entPhysicalParentRelPos' => $drive['slotNumber'] ?? -1,
                'entPhysicalDescr' => '/C' . ($drive['adpID-PDT'] ?? '0') . '/E' . $enclDeviceId . '/S' . ($drive['slotNumber'] ?? '0'),
                'entPhysicalClass' => 'drive',
                'entPhysicalModelName' => $drive['pdProductID'] ?? null,
                'entPhysicalSerialNum' => $drive['pdSerialNumber'] ?? null,
                'entPhysicalContainedIn' => 210 + $enclDeviceId,
                'entPhysicalIsFRU' => 'true',
                'entPhysicalFirmwareRev' => $drive['pdFwversion'] ?? null, // missing
            ]));
        }

        return $inventory;
    }

    private function handleHex(string $string): string
    {
        $string = str_replace("\n", '', $string);
        if (StringHelpers::isHex($string)) {
            $ascii = StringHelpers::hexToAscii($string, ' ');

            return preg_split('/[^ -~]/', $ascii)[0] ?? $ascii;
        }

        return $string;
    }
}
