<?php

/*
 * Ucos.php
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
use Illuminate\Support\Str;

/**
 * Cisco Unified Communications OS (formerly Cisco VOS)
 *
 * Covers:
 * - Cisco Unified Communications Manager (CUCM/Call Manager)
 * - Cisco Unity Connection (CUC)
 * - Cisco Unified Contact Center Express (UCCX)
 * - Cisco Emergency Responder (CER)
 * - Cisco Unified Presence/IM&Presence
 *
 * OS-level metrics (CPU, Memory, Storage) are discovered via:
 * - HOST-RESOURCES-MIB (hrProcessorLoad, hrStorage*)
 * - Standard LibreNMS processors/mempools/storage modules
 *
 * Application metrics are discovered via:
 * - CISCO-CCM-MIB for CUCM (phones, gateways, devices, etc.)
 * - Additional application-specific MIBs as available
 */
class Ucos extends \LibreNMS\OS
{
    public function discoverOS(Device $device): void
    {
        parent::discoverOS($device); // yaml

        // Detect the primary application/role based on installed packages
        $applist = snmp_walk($this->getDeviceArray(), 'SYSAPPL-MIB::sysApplInstallPkgProductName', '-OQv');

        // First check sysDescr for product name (newer versions may include it)
        $sysDescr = $device->sysDescr ?? '';

        if (Str::contains($sysDescr, 'Communications Manager')) {
            $device->features = 'CUCM';
        } elseif (Str::contains($sysDescr, 'Unity Connection')) {
            $device->features = 'CUC';
        } elseif (Str::contains($sysDescr, 'IM and Presence')) {
            $device->features = 'CUP';
        } elseif (Str::contains($sysDescr, 'Contact Center Express')) {
            $device->features = 'UCCX';
        } elseif (Str::contains($sysDescr, 'Emergency Responder')) {
            $device->features = 'CER';
        } elseif (Str::contains($sysDescr, 'Unity Express')) {
            $device->features = 'CUC';
        }
        // Fallback to SYSAPPL-MIB detection for older versions with generic sysDescr
        elseif (Str::contains($applist, 'Cisco Unified CCX Database')) {
            $device->features = 'UCCX';  // Contact Center Express
        } elseif (Str::contains($applist, 'Cisco CallManager')) {
            $device->features = 'CUCM';  // Communications Manager (Call Manager)
        } elseif (Str::contains($applist, 'Cisco Emergency Responder')) {
            $device->features = 'CER';   // Emergency Responder
        } elseif (Str::contains($applist, 'Connection System Agent')) {
            $device->features = 'CUC';   // Unity Connection (Voice Mail)
        } elseif (Str::contains($applist, 'Cisco XCP')) {
            // XCP (eXtensible Communication Platform) indicates Presence/IM&Presence
            $device->features = 'CUP';   // Unified Presence / IM&Presence
        }
    }
}
