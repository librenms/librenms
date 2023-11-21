<?php
/*
 * Panos.php
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

use Illuminate\Support\Str;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Polling\OSPolling;
use LibreNMS\RRD\RrdDefinition;

class Panos extends \LibreNMS\OS implements OSPolling
{
    private $validNetBufferMemory = [
        'Packet Descriptors',
        'Packet Buffers',
    ];

    public function pollOS(DataStorageInterface $datastore): void
    {
        $data = snmp_get_multi($this->getDeviceArray(), [
            'panSessionActive.0',
            'panSessionActiveTcp.0',
            'panSessionActiveUdp.0',
            'panSessionActiveICMP.0',
            'panSessionActiveSslProxy.0',
            'panSessionSslProxyUtilization.0',
            'panGPGWUtilizationActiveTunnels.0',
            'panFlowDosBlkNumEntries.0',
            'panFlowMeterVsysThrottle.0',
            'panFlowPolicyDeny.0',
            'panFlowPolicyNat.0',
            'panFlowScanDrop.0',
            'panFlowDosDropIpBlocked.0',
            'panFlowDosRedIcmp.0',
            'panFlowDosRedIcmp6.0',
            'panFlowDosRedIp.0',
            'panFlowDosRedTcp.0',
            'panFlowDosRedUdp.0',
            'panFlowDosPbpDrop.0',
            'panFlowDosRuleDeny.0',
            'panFlowDosRuleDrop.0',
            'panFlowDosZoneRedAct.0',
            'panFlowDosZoneRedMax.0',
            'panFlowDosSyncookieNotTcpSyn.0',
            'panFlowDosSyncookieNotTcpSynAck.0',
            'panFlowDosBlkSwEntries.0',
            'panFlowDosBlkHwEntries.0',
        ], '-OQUs', 'PAN-COMMON-MIB');

        if (is_numeric($data[0]['panSessionActive'])) {
            $rrd_def = RrdDefinition::make()->addDataset('sessions', 'GAUGE', 0, 3000000);

            $fields = [
                'sessions' => $data[0]['panSessionActive'],
            ];

            $tags = compact('rrd_def');
            $datastore->put($this->getDeviceArray(), 'panos-sessions', $tags, $fields);

            $this->enableGraph('panos_sessions');
        }

        if (is_numeric($data[0]['panSessionActiveTcp'])) {
            $rrd_def = RrdDefinition::make()->addDataset('sessions_tcp', 'GAUGE', 0, 3000000);

            $fields = [
                'sessions_tcp' => $data[0]['panSessionActiveTcp'],
            ];

            $tags = compact('rrd_def');
            $datastore->put($this->getDeviceArray(), 'panos-sessions-tcp', $tags, $fields);

            $this->enableGraph('panos_sessions_tcp');
        }

        if (is_numeric($data[0]['panSessionActiveUdp'])) {
            $rrd_def = RrdDefinition::make()->addDataset('sessions_udp', 'GAUGE', 0, 3000000);

            $fields = [
                'sessions_udp' => $data[0]['panSessionActiveUdp'],
            ];

            $tags = compact('rrd_def');
            $datastore->put($this->getDeviceArray(), 'panos-sessions-udp', $tags, $fields);

            $this->enableGraph('panos_sessions_udp');
        }

        if (is_numeric($data[0]['panSessionActiveICMP'])) {
            $rrd_def = RrdDefinition::make()->addDataset('sessions_icmp', 'GAUGE', 0, 3000000);

            $fields = [
                'sessions_icmp' => $data[0]['panSessionActiveICMP'],
            ];

            $tags = compact('rrd_def');
            $datastore->put($this->getDeviceArray(), 'panos-sessions-icmp', $tags, $fields);

            $this->enableGraph('panos_sessions_icmp');
        }

        if (is_numeric($data[0]['panSessionActiveSslProxy'])) {
            $rrd_def = RrdDefinition::make()->addDataset('sessions_ssl', 'GAUGE', 0, 3000000);

            $fields = [
                'sessions_ssl' => $data[0]['panSessionActiveSslProxy'],
            ];

            $tags = compact('rrd_def');
            $datastore->put($this->getDeviceArray(), 'panos-sessions-ssl', $tags, $fields);

            $this->enableGraph('panos_sessions_ssl');
        }

        if (is_numeric($data[0]['panSessionSslProxyUtilization'])) {
            $rrd_def = RrdDefinition::make()->addDataset('sessions_sslutil', 'GAUGE', 0, 3000000);

            $fields = [
                'sessions_sslutil' => $data[0]['panSessionSslProxyUtilization'],
            ];

            $tags = compact('rrd_def');
            $datastore->put($this->getDeviceArray(), 'panos-sessions-sslutil', $tags, $fields);

            $this->enableGraph('panos_sessions_sslutil');
        }

        if (is_numeric($data[0]['panGPGWUtilizationActiveTunnels'])) {
            $rrd_def = RrdDefinition::make()->addDataset('activetunnels', 'GAUGE', 0, 3000000);

            $fields = [
                'activetunnels' => $data[0]['panGPGWUtilizationActiveTunnels'],
            ];

            $tags = compact('rrd_def');
            $datastore->put($this->getDeviceArray(), 'panos-activetunnels', $tags, $fields);

            $this->enableGraph('panos_activetunnels');
        }
        if (is_numeric($data[0]['panFlowDosBlkNumEntries'])) {
            $rrd_def = RrdDefinition::make()->addDataset('panFlowDosBlkNumEntries', 'GAUGE', 0);

            $fields = [
                'panFlowDosBlkNumEntries' => $data[0]['panFlowDosBlkNumEntries'],
            ];

            $tags = compact('rrd_def');
            $datastore->put($this->getDeviceArray(), 'panos-panFlowDosBlkNumEntries', $tags, $fields);

            $this->enableGraph('panos_panFlowDosBlkNumEntries');
        }
        if (is_numeric($data[0]['panFlowMeterVsysThrottle'])) {
            $rrd_def = RrdDefinition::make()->addDataset('panFlowMeterVsysThrottle', 'COUNTER', 0);

            $fields = [
                'panFlowMeterVsysThrottle' => $data[0]['panFlowMeterVsysThrottle'],
            ];

            $tags = compact('rrd_def');
            $datastore->put($this->getDeviceArray(), 'panos-panFlowMeterVsysThrottle', $tags, $fields);

            $this->enableGraph('panos_panFlowMeterVsysThrottle');
        }
        if (is_numeric($data[0]['panFlowPolicyDeny'])) {
            $rrd_def = RrdDefinition::make()->addDataset('panFlowPolicyDeny', 'COUNTER', 0);

            $fields = [
                'panFlowPolicyDeny' => $data[0]['panFlowPolicyDeny'],
            ];

            $tags = compact('rrd_def');
            $datastore->put($this->getDeviceArray(), 'panos-panFlowPolicyDeny', $tags, $fields);

            $this->enableGraph('panos_panFlowPolicyDeny');
        }
        if (is_numeric($data[0]['panFlowPolicyNat'])) {
            $rrd_def = RrdDefinition::make()->addDataset('panFlowPolicyNat', 'COUNTER', 0);

            $fields = [
                'panFlowPolicyNat' => $data[0]['panFlowPolicyNat'],
            ];

            $tags = compact('rrd_def');
            $datastore->put($this->getDeviceArray(), 'panos-panFlowPolicyNat', $tags, $fields);

            $this->enableGraph('panos_panFlowPolicyNat');
        }
        if (is_numeric($data[0]['panFlowScanDrop'])) {
            $rrd_def = RrdDefinition::make()->addDataset('panFlowScanDrop', 'COUNTER', 0);

            $fields = [
                'panFlowScanDrop' => $data[0]['panFlowScanDrop'],
            ];

            $tags = compact('rrd_def');
            $datastore->put($this->getDeviceArray(), 'panos-panFlowScanDrop', $tags, $fields);

            $this->enableGraph('panos_panFlowScanDrop');
        }
        if (is_numeric($data[0]['panFlowDosDropIpBlocked'])) {
            $rrd_def = RrdDefinition::make()->addDataset('panFlowDosDropIpBlocked', 'COUNTER', 0);

            $fields = [
                'panFlowDosDropIpBlocked' => $data[0]['panFlowDosDropIpBlocked'],
            ];

            $tags = compact('rrd_def');
            $datastore->put($this->getDeviceArray(), 'panos-panFlowDosDropIpBlocked', $tags, $fields);

            $this->enableGraph('panos_panFlowDosDropIpBlocked');
        }
        if (is_numeric($data[0]['panFlowDosRedIcmp'])) {
            $rrd_def = RrdDefinition::make()->addDataset('panFlowDosRedIcmp', 'COUNTER', 0);

            $fields = [
                'panFlowDosRedIcmp' => $data[0]['panFlowDosRedIcmp'],
            ];

            $tags = compact('rrd_def');
            $datastore->put($this->getDeviceArray(), 'panos-panFlowDosRedIcmp', $tags, $fields);

            $this->enableGraph('panos_panFlowDosRedIcmp');
        }
        if (is_numeric($data[0]['panFlowDosRedIcmp6'])) {
            $rrd_def = RrdDefinition::make()->addDataset('panFlowDosRedIcmp6', 'COUNTER', 0);

            $fields = [
                'panFlowDosRedIcmp6' => $data[0]['panFlowDosRedIcmp6'],
            ];

            $tags = compact('rrd_def');
            $datastore->put($this->getDeviceArray(), 'panos-panFlowDosRedIcmp6', $tags, $fields);

            $this->enableGraph('panos_panFlowDosRedIcmp6');
        }
        if (is_numeric($data[0]['panFlowDosRedIp'])) {
            $rrd_def = RrdDefinition::make()->addDataset('panFlowDosRedIp', 'COUNTER', 0);

            $fields = [
                'panFlowDosRedIp' => $data[0]['panFlowDosRedIp'],
            ];

            $tags = compact('rrd_def');
            $datastore->put($this->getDeviceArray(), 'panos-panFlowDosRedIp', $tags, $fields);

            $this->enableGraph('panos_panFlowDosRedIp');
        }
        if (is_numeric($data[0]['panFlowDosRedTcp'])) {
            $rrd_def = RrdDefinition::make()->addDataset('panFlowDosRedTcp', 'COUNTER', 0);

            $fields = [
                'panFlowDosRedTcp' => $data[0]['panFlowDosRedTcp'],
            ];

            $tags = compact('rrd_def');
            $datastore->put($this->getDeviceArray(), 'panos-panFlowDosRedTcp', $tags, $fields);

            $this->enableGraph('panos_panFlowDosRedTcp');
        }
        if (is_numeric($data[0]['panFlowDosRedUdp'])) {
            $rrd_def = RrdDefinition::make()->addDataset('panFlowDosRedUdp', 'COUNTER', 0);

            $fields = [
                'panFlowDosRedUdp' => $data[0]['panFlowDosRedUdp'],
            ];

            $tags = compact('rrd_def');
            $datastore->put($this->getDeviceArray(), 'panos-panFlowDosRedUdp', $tags, $fields);

            $this->enableGraph('panos_panFlowDosRedUdp');
        }
        if (is_numeric($data[0]['panFlowDosPbpDrop'])) {
            $rrd_def = RrdDefinition::make()->addDataset('panFlowDosPbpDrop', 'COUNTER', 0);

            $fields = [
                'panFlowDosPbpDrop' => $data[0]['panFlowDosPbpDrop'],
            ];

            $tags = compact('rrd_def');
            $datastore->put($this->getDeviceArray(), 'panos-panFlowDosPbpDrop', $tags, $fields);

            $this->enableGraph('panos_panFlowDosPbpDrop');
        }
        if (is_numeric($data[0]['panFlowDosRuleDeny'])) {
            $rrd_def = RrdDefinition::make()->addDataset('panFlowDosRuleDeny', 'COUNTER', 0);

            $fields = [
                'panFlowDosRuleDeny' => $data[0]['panFlowDosRuleDeny'],
            ];

            $tags = compact('rrd_def');
            $datastore->put($this->getDeviceArray(), 'panos-panFlowDosRuleDeny', $tags, $fields);

            $this->enableGraph('panos_panFlowDosRuleDeny');
        }
        if (is_numeric($data[0]['panFlowDosRuleDrop'])) {
            $rrd_def = RrdDefinition::make()->addDataset('panFlowDosRuleDrop', 'COUNTER', 0);

            $fields = [
                'panFlowDosRuleDrop' => $data[0]['panFlowDosRuleDrop'],
            ];

            $tags = compact('rrd_def');
            $datastore->put($this->getDeviceArray(), 'panos-panFlowDosRuleDrop', $tags, $fields);

            $this->enableGraph('panos_panFlowDosRuleDrop');
        }
        if (is_numeric($data[0]['panFlowDosZoneRedAct'])) {
            $rrd_def = RrdDefinition::make()->addDataset('panFlowDosZoneRedAct', 'COUNTER', 0);

            $fields = [
                'panFlowDosZoneRedAct' => $data[0]['panFlowDosZoneRedAct'],
            ];

            $tags = compact('rrd_def');
            $datastore->put($this->getDeviceArray(), 'panos-panFlowDosZoneRedAct', $tags, $fields);

            $this->enableGraph('panos_panFlowDosZoneRedAct');
        }
        if (is_numeric($data[0]['panFlowDosZoneRedMax'])) {
            $rrd_def = RrdDefinition::make()->addDataset('panFlowDosZoneRedMax', 'COUNTER', 0);

            $fields = [
                'panFlowDosZoneRedMax' => $data[0]['panFlowDosZoneRedMax'],
            ];

            $tags = compact('rrd_def');
            $datastore->put($this->getDeviceArray(), 'panos-panFlowDosZoneRedMax', $tags, $fields);

            $this->enableGraph('panos_panFlowDosZoneRedMax');
        }
        if (is_numeric($data[0]['panFlowDosSyncookieNotTcpSyn'])) {
            $rrd_def = RrdDefinition::make()->addDataset('panFlowDosSyncookieNotTcpSyn', 'COUNTER', 0);

            $fields = [
                'panFlowDosSyncookieNotTcpSyn' => $data[0]['panFlowDosSyncookieNotTcpSyn'],
            ];

            $tags = compact('rrd_def');
            $datastore->put($this->getDeviceArray(), 'panos-panFlowDosSyncookieNotTcpSyn', $tags, $fields);

            $this->enableGraph('panos_panFlowDosSyncookieNotTcpSyn');
        }
        if (is_numeric($data[0]['panFlowDosSyncookieNotTcpSynAck'])) {
            $rrd_def = RrdDefinition::make()->addDataset('panFlowDosSyncookieNotTcpSynAck', 'COUNTER', 0);

            $fields = [
                'panFlowDosSyncookieNotTcpSynAck' => $data[0]['panFlowDosSyncookieNotTcpSynAck'],
            ];

            $tags = compact('rrd_def');
            $datastore->put($this->getDeviceArray(), 'panos-panFlowDosSyncookieNotTcpSynAck', $tags, $fields);

            $this->enableGraph('panos_panFlowDosSyncookieNotTcpSynAck');
        }
        if (is_numeric($data[0]['panFlowDosBlkSwEntries'])) {
            $rrd_def = RrdDefinition::make()->addDataset('panFlowDosBlkSwEntries', 'GAUGE', 0);

            $fields = [
                'panFlowDosBlkSwEntries' => $data[0]['panFlowDosBlkSwEntries'],
            ];

            $tags = compact('rrd_def');
            $datastore->put($this->getDeviceArray(), 'panos-panFlowDosBlkSwEntries', $tags, $fields);

            $this->enableGraph('panos_panFlowDosBlkSwEntries');
        }
        if (is_numeric($data[0]['panFlowDosBlkHwEntries'])) {
            $rrd_def = RrdDefinition::make()->addDataset('panFlowDosBlkHwEntries', 'GAUGE', 0);

            $fields = [
                'panFlowDosBlkHwEntries' => $data[0]['panFlowDosBlkHwEntries'],
            ];

            $tags = compact('rrd_def');
            $datastore->put($this->getDeviceArray(), 'panos-panFlowDosBlkHwEntries', $tags, $fields);

            $this->enableGraph('panos_panFlowDosBlkHwEntries');
        }
    }

    protected function memValid($storage)
    {
        return $storage['hrStorageType'] == 'hrStorageOther'
            && Str::contains($storage['hrStorageDescr'], $this->validNetBufferMemory)
            || parent::memValid($storage);
    }
}
