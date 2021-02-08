<?php
/**
 * Apc.php
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
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\Device;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\OS;

class Apc extends OS implements OSDiscovery
{
    public function discoverOS(Device $device): void
    {
        $apc_serial = snmp_get_multi_oid($this->getDeviceArray(), ['rPDUIdentSerialNumber.0', 'atsIdentSerialNumber.0', 'upsAdvIdentSerialNumber.0', 'sPDUIdentSerialNumber.0', 'airIRRCUnitIdentSerialNumber.0', 'isxModularPduIdentSerialNumber.0', 'airIRRP100UnitIdentSerialNumber.0', 'airIRRP500UnitIdentSerialNumber.0'], '-OUQs', 'PowerNet-MIB');
        $device->serial = array_pop($apc_serial);

        $apc_model = snmp_get_multi_oid($this->getDeviceArray(), ['rPDUIdentModelNumber.0', 'atsIdentModelNumber.0', 'upsBasicIdentModel.0', 'sPDUIdentModelNumber.0', 'airIRRCUnitIdentModelNumber.0', 'isxModularPduIdentModelNumber.0', 'airIRRP100UnitIdentModelNumber.0', 'airIRRP500UnitIdentModelNumber.0'], '-OUQs', 'PowerNet-MIB');
        $hardware = array_pop($apc_model);
        $apc_hardware = snmp_get_multi_oid($this->getDeviceArray(), ['rPDUIdentHardwareRev.0', 'atsIdentHardwareRev.0', 'upsAdvIdentFirmwareRevision.0', 'sPDUIdentHardwareRev.0', 'airIRRCUnitIdentHardwareRevision.0', 'isxModularPduIdentMonitorCardHardwareRev.0', 'airIRRP100UnitIdentHardwareRevision.0', 'airIRRP500UnitIdentHardwareRevision.0'], '-OUQs', 'PowerNet-MIB');
        if (! empty($apc_hardware)) {
            $hardware = trim($hardware . ' ' . array_pop($apc_hardware));
        }
        if (empty($hardware)) {
            preg_match('/APC (.+) \(/', $device->sysDescr, $hardware_match);
            $hardware = $hardware_match[1] ?? null;
        }
        $device->hardware = $hardware;

        $AOSrev = snmp_get($this->getDeviceArray(), '1.3.6.1.4.1.318.1.4.2.4.1.4.1', '-OQv');
        $APPrev = snmp_get($this->getDeviceArray(), '1.3.6.1.4.1.318.1.4.2.4.1.4.2', '-OQv');
        if ($AOSrev) {
            $device->version = "AOS $AOSrev / App $APPrev";
        } else {
            $apc_version = snmp_get_multi_oid($this->getDeviceArray(), ['rPDUIdentFirmwareRev.0', 'atsIdentFirmwareRev.0', 'sPDUIdentFirmwareRev.0', 'airIRRCUnitIdentFirmwareRevision.0', 'isxModularPduIdentMonitorCardFirmwareAppRev.0', 'airIRRP100UnitIdentFirmwareRevision.0', 'airIRRP500UnitIdentFirmwareRevision.0'], '-OUQs', 'PowerNet-MIB');
            $version = array_pop($apc_version);
            if (empty($version) && preg_match('/PF:([^ ]+) .*AF1:([^ ]+) /', $device->sysDescr, $version_matches)) {
                $version = "AOS {$version_matches[1]} / App $version_matches[2]";
            }
            $device->version = $version;
        }
    }
}
