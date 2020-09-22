<?php
/*
 * Junos.php
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

namespace LibreNMS\OS;

use App\Models\Device;
use LibreNMS\Interfaces\Polling\OSPolling;
use LibreNMS\RRD\RrdDefinition;

class Junos extends \LibreNMS\OS implements OSPolling
{
    public function discoverOS(Device $device): void
    {
        $data = snmp_get_multi($this->getDeviceArray(), [
            'JUNIPER-MIB::jnxBoxDescr.0',
            'JUNIPER-MIB::jnxBoxSerialNo.0',
            'JUNIPER-VIRTUALCHASSIS-MIB::jnxVirtualChassisMemberSWVersion.0',
            'HOST-RESOURCES-MIB::hrSWInstalledName.2',
        ], '-OQUs');

        preg_match('/Juniper Networks, Inc. (?<hardware>\S+) .* kernel JUNOS (?<version>[^, ]+)[, ]/', $device->sysDescr, $parsed);
        if (isset($data[2]['hrSWInstalledName'])) {
            preg_match('/\[(.+)]/', $data[2]['hrSWInstalledName'], $parsedVersion);
        }

        $device->hardware = $data[0]['jnxBoxDescr'] ?? (isset($parsed['hardware']) ? 'Juniper ' . strtoupper($parsed['hardware']) : null);
        $device->serial = $data[0]['jnxBoxSerialNo'] ?? null;
        $device->version = $data[0]['jnxVirtualChassisMemberSWVersion'] ?? $parsedVersion[1] ?? $parsed['version'] ?? null;
    }

    public function pollOS()
    {
        $data = snmp_get_multi($this->getDeviceArray(), 'jnxJsSPUMonitoringCurrentFlowSession.0', '-OUQs', 'JUNIPER-SRX5000-SPU-MONITORING-MIB');

        if (is_numeric($data[0]['jnxJsSPUMonitoringCurrentFlowSession'])) {
            data_update($this->getDeviceArray(), 'junos_jsrx_spu_sessions', [
                'rrd_def' => RrdDefinition::make()->addDataset('spu_flow_sessions', 'GAUGE', 0),
            ], [
                'spu_flow_sessions' => $data[0]['jnxJsSPUMonitoringCurrentFlowSession'],
            ]);

            $this->enableGraph('junos_jsrx_spu_sessions');
        }
    }
}
