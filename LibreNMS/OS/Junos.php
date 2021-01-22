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
use LibreNMS\OS;
use App\Models\Device;
use LibreNMS\Interfaces\Polling\OSPolling;
use LibreNMS\RRD\RrdDefinition;

use App\Models\MplsLsp;
use App\Models\MplsLspPath;
use App\Models\MplsSap;
use App\Models\MplsSdp;
use App\Models\MplsSdpBind;
use App\Models\MplsService;
use App\Models\MplsTunnelArHop;
use App\Models\MplsTunnelCHop;

use Illuminate\Support\Collection;
use LibreNMS\Interfaces\Discovery\MplsDiscovery;
use LibreNMS\Interfaces\Polling\MplsPolling;

class Junos extends OS implements OSPolling, MplsDiscovery
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

    /**
     * @return Collection MplsLsp objects
     */
    public function discoverMplsLsps()
    {
        $mplsLspCache = snmpwalk_cache_multi_oid($this->getDeviceArray(), 'mplsLspInfoList', [], 'MPLS-MIB', 'junos');
        if (! empty($mplsLspCache)) {
            $mplsLspCache = snmpwalk_cache_multi_oid($this->getDeviceArray(), 'mplsLspInfoLastPathChange', $mplsLspCache, 'MPLS-MIB', 'junos', '-OQUst');
        }

        $lsps = collect();
        #var_dump($mplsLspCache);
        int count = 0;
        foreach ($mplsLspCache as $key => $value) {
            #[$vrf_oid, $lsp_oid] = explode(':', $key);
            $lsp_devices = explode('->', $key);
            if (empty($lsp_devices)) {
                continue;
            }
            $count++;
            #var_dump($key);
            #var_dump($lsp_devices);
            #echo($vrf_oid);
            #echo($lsp_oid);

            // example $keys
            // Without filtering for "->"
            // ["'1.1.1.1:1.1.1.2:6:vpls:VPLS-C123-123456'"]

            // With filtering for "->"
            // lp-building-a-er1->building-b-er1

            $lsp_oid = $count;
            $vrf_oid = 0;

            $mplsLspFromAddr = $value['mplsLspInfoFrom'];
            if (isset($value['mplsLspInfoFrom'])) {
                $mplsLspFromAddr = long2ip(hexdec(str_replace(' ', '', $value['mplsLspInfoFrom'])));
            }
            $mplsLspToAddr = $value['mplsLspInfoTo'];
            if (isset($value['mplsLspInfoTo'])) {
                $mplsLspToAddr = long2ip(hexdec(str_replace(' ', '', $value['mplsLspInfoTo'])));
            }

            #var_dump($mplsLspCache);


            # todo: fast reroute not in this table!
            $lsps->push(new MplsLsp([
                'vrf_oid' => $vrf_oid,
                'lsp_oid' => $lsp_oid,
                'device_id' => $this->getDeviceId(),
                'mplsLspRowStatus' => $value['mplsLspInfoState'],
                'mplsLspLastChange' => round($value['mplsLspInfoLastPathChange'] / 100),
                'mplsLspName' => $value['mplsLspInfoName'],
                'mplsLspOperState' => $value['mplsLspInfoState'],
                'mplsLspFromAddr' => $mplsLspFromAddr,
                'mplsLspToAddr' => $mplsLspToAddr,
                'mplsLspType' => $value['mplsPathInfoType'],
                'mplsLspFastReroute' => $value['mplsPathInfoProperties'],
            ]));
        }

        return $lsps;
    }

    public function discoverMplsPaths($lsps)
    {
        // TODO
    }

    public function discoverMplsSdps()
    {
        // TODO
    }

    public function discoverMplsServices()
    {
        // TODO
    }

    public function discoverMplsSaps($svcs)
    {
        // TODO
    }

    public function discoverMplsSdpBinds($sdps, $svcs)
    {
        // TODO
    }

    public function discoverMplsTunnelArHops($paths)
    {
        // TODO
    }

    public function discoverMplsTunnelCHops($paths)
    {
        // TODO
    }
}
