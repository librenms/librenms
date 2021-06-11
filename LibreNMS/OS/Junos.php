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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       https://www.librenms.org
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\Device;
use App\Models\Sla;
use Carbon\Carbon;
use LibreNMS\Interfaces\Discovery\SlaDiscovery;
use LibreNMS\Interfaces\Polling\OSPolling;
use LibreNMS\Interfaces\Polling\SlaPolling;
use LibreNMS\RRD\RrdDefinition;

class Junos extends \LibreNMS\OS implements SlaDiscovery, OSPolling, SlaPolling
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

    public function discoverSlas()
    {
        $slas = collect();
        $sla_table = snmpwalk_cache_oid($this->getDeviceArray(), 'pingCtlTable', [], 'DISMAN-PING-MIB');

        if (! empty($sla_table)) {
            $sla_table = snmpwalk_cache_oid($this->getDeviceArray(), 'jnxPingResultsRttUs', $sla_table, 'JUNIPER-PING-MIB');
        }

        foreach ($sla_table as $sla_key => $sla_config) {
            [$owner, $test] = explode('.', $sla_key, 2);

            $slas->push(new Sla([
                'sla_nr' => hexdec(hash('crc32', $owner . $test)), // indexed by owner+test, convert to int
                'owner' => $owner,
                'tag' => $test,
                'rtt_type' => $this->retrieveJuniperType($sla_config['pingCtlType']),
                'rtt' => isset($sla_config['jnxPingResultsRttUs']) ? $sla_config['jnxPingResultsRttUs'] / 1000 : null,
                'status' => ($sla_config['pingCtlAdminStatus'] == 'enabled') ? 1 : 0,
                'opstatus' => ($sla_config['pingCtlRowStatus'] == 'active') ? 0 : 2,
            ]));
        }

        return $slas;
    }

    public function pollSlas($slas)
    {
        $device = $this->getDeviceArray();

        // Go get some data from the device.
        $data = snmpwalk_group($device, 'pingCtlRowStatus', 'DISMAN-PING-MIB', 2);
        $data = snmpwalk_group($device, 'pingResultsProbeResponses', 'DISMAN-PING-MIB', 2, $data);
        $data = snmpwalk_group($device, 'pingResultsSentProbes', 'DISMAN-PING-MIB', 2, $data);
        $data = snmpwalk_group($device, 'jnxPingResultsTable', 'JUNIPER-PING-MIB', 2, $data);

        // Get the needed information
        foreach ($slas as $sla) {
            $sla_nr = $sla->sla_nr;
            $rtt_type = $sla->rtt_type;
            $owner = $sla->owner;
            $test = $sla->tag;

            // Lets process each SLA

            // Use DISMAN-PING Status codes. 0=Good 2=Critical
            $sla->opstatus = $data[$owner][$test]['pingCtlRowStatus'] == '1' ? 0 : 2;

            $sla->rtt = $data[$owner][$test]['jnxPingResultsRttUs'] / 1000;
            $time = Carbon::parse($data[$owner][$test]['jnxPingResultsTime'])->toDateTimeString();
            echo 'SLA : ' . $rtt_type . ' ' . $owner . ' ' . $test . '... ' . $sla->rtt . 'ms at ' . $time . "\n";

            $fields = [
                'rtt' => $sla->rtt,
            ];

            // The base RRD
            $rrd_name = ['sla', $sla['sla_nr']];
            $rrd_def = RrdDefinition::make()->addDataset('rtt', 'GAUGE', 0, 300000);
            $tags = compact('sla_nr', 'rrd_name', 'rrd_def');
            data_update($device, 'sla', $tags, $fields);

            // Let's gather some per-type fields.
            switch ($rtt_type) {
                case 'DnsQuery':
                case 'HttpGet':
                case 'HttpGetMetadata':
                    break;
                case 'IcmpEcho':
                case 'IcmpTimeStamp':
                    $icmp = [
                        'MinRttUs' => $data[$owner][$test]['jnxPingResultsMinRttUs'] / 1000,
                        'MaxRttUs' => $data[$owner][$test]['jnxPingResultsMaxRttUs'] / 1000,
                        'StdDevRttUs' => $data[$owner][$test]['jnxPingResultsStdDevRttUs'] / 1000,
                        'ProbeResponses' => $data[$owner][$test]['pingResultsProbeResponses'],
                        'ProbeLoss' => (int) $data[$owner][$test]['pingResultsSentProbes'] - (int) $data[$owner][$test]['pingResultsProbeResponses'],
                    ];
                    $rrd_name = ['sla', $sla_nr, $rtt_type];
                    $rrd_def = RrdDefinition::make()
                        ->addDataset('MinRttUs', 'GAUGE', 0, 300000)
                        ->addDataset('MaxRttUs', 'GAUGE', 0, 300000)
                        ->addDataset('StdDevRttUs', 'GAUGE', 0, 300000)
                        ->addDataset('ProbeResponses', 'GAUGE', 0, 300000)
                        ->addDataset('ProbeLoss', 'GAUGE', 0, 300000);
                    $tags = compact('rrd_name', 'rrd_def', 'sla_nr', 'rtt_type');
                    data_update($device, 'sla', $tags, $icmp);
                    $fields = array_merge($fields, $icmp);
                    break;
                case 'NtpQuery':
                case 'UdpTimestamp':
                    break;
            }

            d_echo('The following datasources were collected for #' . $sla['sla_nr'] . ":\n");
            d_echo($fields);
        }
    }

    private function calculateSlaNr($key): int
    {
        $sum = 0;
        $length = strlen($key);
        for ($i = 0; $i < $length; $i++) {
            $sum += ord($key[$i]);
        }

        return $sum;
    }

    /**
     * Retrieve specific Juniper PingCtlType
     */
    private function retrieveJuniperType($rtt_type)
    {
        switch ($rtt_type) {
            case 'enterprises.2636.3.7.2.1':
                return 'IcmpTimeStamp';
            case 'enterprises.2636.3.7.2.2':
                return 'HttpGet';
            case 'enterprises.2636.3.7.2.3':
                return 'HttpGetMetadata';
            case 'enterprises.2636.3.7.2.4':
                return 'DnsQuery';
            case 'enterprises.2636.3.7.2.5':
                return 'NtpQuery';
            case 'enterprises.2636.3.7.2.6':
                return 'UdpTimestamp';
            default:
                return str_replace('ping', '', $rtt_type);
        }
    }
}
