<?php

/*
 * Ciscosb.php
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
 * @package    LibreNMS
 * @link       https://www.librenms.org
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\Device;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\OS;

class Ciscosb extends OS implements OSDiscovery
{
    protected ?string $entityVendorTypeMib = 'CISCO-ENTITY-VENDORTYPE-OID-MIB';

    public function discoverOS(Device $device): void
    {
        parent::discoverOS($device); // yaml

        $data = snmp_get_multi($this->getDeviceArray(), ['rlPhdUnitGenParamModelName.1', 'genGroupHWVersion.0', 'rlPhdUnitGenParamHardwareVersion.1', 'rlPhdUnitGenParamSoftwareVersion.1', 'rlPhdUnitGenParamFirmwareVersion.1', 'rndBaseBootVersion.0'], '-OQUs', 'CISCOSB-DEVICEPARAMS-MIB:CISCOSB-Physicaldescription-MIB');

        if (empty($device->hardware)) {
            if (preg_match('/\.1\.3\.6\.1\.4\.1\.9\.6\.1\.72\.(....).+/', $device->sysObjectID, $model)) {
                $hardware = 'SGE' . $model[1] . '-' . substr($device->sysDescr, 0, 2);
            } elseif ($device->sysObjectID == '.1.3.6.1.4.1.9.6.1.89.26.1') {
                $hardware = 'SG220-26';
            } else {
                $hardware = str_replace(' ', '', $data['1']['rlPhdUnitGenParamModelName'] ?? '');
            }
            $device->hardware = $hardware;
        }

        $hwversion = $data['0']['genGroupHWVersion'] ?? $data['1']['rlPhdUnitGenParamHardwareVersion'] ?? null;
        if ($hwversion) {
            $device->hardware = trim("$device->hardware $hwversion");
        }

        $device->version = isset($data['1']['rlPhdUnitGenParamSoftwareVersion']) ? ('Software ' . $data['1']['rlPhdUnitGenParamSoftwareVersion']) : null;
        $boot = $data['0']['rndBaseBootVersion'] ?? null;
        $firmware = $data['1']['rlPhdUnitGenParamFirmwareVersion'] ?? null;
        if ($boot) {
            $device->version .= ", Bootldr $boot";
        }
        if ($firmware) {
            $device->version .= ", Firmware $firmware";
        }
        if ($device->version) {
            $device->version = trim($device->version, ', ');
        }

        // CBS220 and similar devices do not implement CISCOSB proprietary MIBs.
        // Fall back to ENTITY-MIB for hardware, serial and version.
        if (empty($device->hardware) || empty($device->serial)) {
            $entityData = \SnmpQuery::enumStrings()->walk([
                'ENTITY-MIB::entPhysicalClass',
                'ENTITY-MIB::entPhysicalModelName',
                'ENTITY-MIB::entPhysicalHardwareRev',
                'ENTITY-MIB::entPhysicalFirmwareRev',
                'ENTITY-MIB::entPhysicalSoftwareRev',
                'ENTITY-MIB::entPhysicalSerialNum',
            ])->valuesByIndex();

            foreach ($entityData as $entry) {
                if (($entry['ENTITY-MIB::entPhysicalClass'] ?? '') !== 'chassis') {
                    continue;
                }
                if (empty($device->hardware)) {
                    $model = $entry['ENTITY-MIB::entPhysicalModelName'] ?? '';
                    $hwRev = $entry['ENTITY-MIB::entPhysicalHardwareRev'] ?? '';
                    $device->hardware = $hwRev ? trim("$model $hwRev") : $model;
                }
                if (empty($device->serial)) {
                    $device->serial = $entry['ENTITY-MIB::entPhysicalSerialNum'] ?? '';
                }
                if (empty($device->version)) {
                    $sw = $entry['ENTITY-MIB::entPhysicalSoftwareRev'] ?? '';
                    $fw = $entry['ENTITY-MIB::entPhysicalFirmwareRev'] ?? '';
                    $parts = array_filter([
                        $sw ? "Software $sw" : null,
                        $fw ? "Firmware $fw" : null,
                    ]);
                    if ($parts) {
                        $device->version = implode(', ', $parts);
                    }
                }
                break;
            }
        }
    }
}
