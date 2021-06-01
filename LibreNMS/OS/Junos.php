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
use Illuminate\Database\Eloquent\Collection;
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
        $device = $this->getDeviceArray();

        $slas = collect();
        $data = snmp_walk($device, 'pingMIB.pingObjects.pingCtlTable.pingCtlEntry', '-OQUs', '+DISMAN-PING-MIB');

        // Index the MIB information
        $sla_table = [];
        foreach (explode("\n", $data) as $index) {
            $key_val = explode(' ', $index, 3);

            $key = $key_val[0];
            $value = $key_val[2];

            $prop_id = explode('.', $key);

            $property = $prop_id[0];
            $owner = $prop_id[1];
            $test = $prop_id[2];

            $sla_table[$owner . '.' . $test][$property] = $value;
        }

        foreach ($sla_table as $sla_key => $sla_config) {
            // To get right owner index and test name from $sla_table key
            $prop_id = explode('.', $sla_key);
            $owner = $prop_id[0];
            $test = $prop_id[1];

            $sla_data = Sla::select('sla_id', 'sla_nr')
                ->where('device_id', $device['device_id'])
                ->where('owner', $owner)
                ->where('tag', $test)
                ->get();

            $sla_id = $sla_data[0]->sla_id;
            $sla_nr = $sla_data[0]->sla_nr;

            $data = [
                'device_id' => $device['device_id'],
                'sla_nr'    => $sla_nr,
                'owner'     => $owner,
                'tag'       => $test,
                'rtt_type'  => $sla_config['pingCtlType'],
                'status'    => ($sla_config['pingCtlAdminStatus'] == 'enabled') ? 1 : 0,
                'opstatus'  => ($sla_config['pingCtlRowStatus'] == 'active') ? 0 : 2,
                'deleted'   => 0,
            ];

            // If it is a standard type delete ping preffix
            $data['rtt_type'] = str_replace('ping', '', $data['rtt_type']);

            // To retrieve specific Juniper PingCtlType
            if ($device['os'] == 'junos') {
                $data['rtt_type'] = $this->retrieveJuniperType($data['rtt_type']);
            }

            $slas->push($data);
        }

        return $slas;
    }

    public function pollSlas($slas)
    {
        $device = $this->getDeviceArray();

        // Go get some data from the device.
        $pingCtlResults = snmp_walk($device, 'pingMIB.pingObjects.pingCtlTable.pingCtlEntry', '-OQUs', '+DISMAN-PING-MIB');
        $pingResults = snmp_walk($device, 'pingMIB.pingObjects.pingResultsTable.pingResultsEntry', '-OQUs', '+DISMAN-PING-MIB');
        $jnxPingResults = snmp_walk($device, 'jnxPingResultsEntry', '-OQUs', '+JUNIPER-PING-MIB');

        // Instanciate index foreach MIB to query field more easily
        $jnxPingResults_table = [];
        foreach (explode("\n", $jnxPingResults) as $line) {
            $key_val = explode(' ', $line, 3);

            $key = $key_val[0];
            $value = $key_val[2];

            // To get owner index and test name
            $prop_id = explode('.', $key);
            $property = $prop_id[0];
            $owner = $prop_id[1];
            $test = $prop_id[2];

            $jnxPingResults_table[$owner . '.' . $test][$property] = $value;
        }

        // Getting only ProbeResponses and SentProbes
        $pingResultsProbeResponses = [];
        $pingResultsSentProbes = [];
        foreach (explode("\n", $pingResults) as $line) {
            $key_val = explode(' ', $line, 3);

            $key = $key_val[0];
            $value = $key_val[2];

            // To get owner index and test name
            $prop_id = explode('.', $key);
            $property = $prop_id[0];
            $owner = $prop_id[1];
            $test = $prop_id[2];

            if ($property == 'pingResultsProbeResponses') {
                $pingResultsProbeResponses[$owner . '.' . $test] = $value;
            } elseif ($property == 'pingResultsSentProbes') {
                $pingResultsSentProbes[$owner . '.' . $test] = $value;
            }
        }

        // Getting only pingCtlRowStatuses
        $pingCtlRowStatuses = [];
        foreach (explode("\n", $pingCtlResults) as $line) {
            $key_val = explode(' ', $line, 3);

            $key = $key_val[0];
            $value = $key_val[2];

            // To get owner index and test name
            $prop_id = explode('.', $key);
            $property = $prop_id[0];
            $owner = $prop_id[1];
            $test = $prop_id[2];

            if ($property == 'pingCtlRowStatus') {
                $pingCtlRowStatuses[$owner . '.' . $test] = $value;
            }
        }

        // Get the needed informations
        foreach ($slas as $sla) {
            $sla_id = $sla['sla_id'];
            $sla_nr = $sla['sla_nr'];
            $rtt_type = $sla['rtt_type'];
            $owner = $sla['owner'];
            $test = $sla['tag'];

            // Lets process each SLA
            $time = $this->fixdate($jnxPingResults_table[$owner . '.' . $test]['jnxPingResultsTime']);
            $update = [];

            // Use DISMAN-PING Status codes.
            $opstatus = $pingCtlRowStatuses[$owner . '.' . $test];

            if ($opstatus == 'active') {
                $opstatus = 0;        // 0=Good
            } else {
                $opstatus = 2;        // 2=Critical
            }

            // Populating the update array means we need to update the DB.
            if ($opstatus != $sla['opstatus']) {
                $update['opstatus'] = $opstatus;
            }

            $rtt = $jnxPingResults_table[$owner . '.' . $test]['jnxPingResultsRttUs'] / 1000;
            echo 'SLA : ' . $rtt_type . ' ' . $owner . ' ' . $test . '... ' . $rtt . 'ms at ' . $time . "\n";

            $fields = [
                'rtt' => $rtt,
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
                        'MinRttUs' => $jnxPingResults_table[$owner . '.' . $test]['jnxPingResultsMinRttUs'] / 1000,
                        'MaxRttUs' => $jnxPingResults_table[$owner . '.' . $test]['jnxPingResultsMaxRttUs'] / 1000,
                        'StdDevRttUs' => $jnxPingResults_table[$owner . '.' . $test]['jnxPingResultsStdDevRttUs'] / 1000,
                        'ProbeResponses' => $pingResultsProbeResponses[$owner . '.' . $test],
                        'ProbeLoss' => (int) $pingResultsSentProbes[$owner . '.' . $test] - (int) $pingResultsProbeResponses[$owner . '.' . $test],
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

            // Update the DB if necessary
            if (count($update) > 0) {
                Sla::where('sla_id', $sla_id)
                ->update($update);
            }
        }
    }

    /**
     * Retrieve specific Juniper PingCtlType
     */
    public function retrieveJuniperType($mib_location)
    {
        // Return without changes if not in the list
        $rtt_type = $mib_location;

        switch ($mib_location) {
            case 'enterprises.2636.3.7.2.1':
                $rtt_type = 'IcmpTimeStamp';
                break;

            case 'enterprises.2636.3.7.2.2':
                $rtt_type = 'HttpGet';
                break;

            case 'enterprises.2636.3.7.2.3':
                $rtt_type = 'HttpGetMetadata';
                break;

            case 'enterprises.2636.3.7.2.4':
                $rtt_type = 'DnsQuery';
                break;

            case 'enterprises.2636.3.7.2.5':
                $rtt_type = 'NtpQuery';
                break;
            case 'enterprises.2636.3.7.2.6':
                $rtt_type = 'UdpTimestamp';
                break;
        }

        return $rtt_type;
    }

    /**
     * Function to fix the 0 missing before digit on a date from the MIB
     */
    private function fixdate($string)
    {
        $datetime = explode(',', $string);
        $date = explode('-', $datetime[0]);
        $time = explode(':', $datetime[1]);

        // If one digit, add a 0 before
        foreach ($date as &$field) {
            if ((int) $field < 10) {
                $field = '0' . $field;
            }
        }
        foreach ($time as &$field) {
            if ((int) $field < 10) {
                $field = '0' . $field;
            }
        }
        // To remove the decisecond
        $time[2] = explode('.', $time[2])[0];

        return $date[0] . '-' . $date[1] . '-' . $date[2] . ' ' . $time[0] . ':' . $time[1] . ':' . $time[2];
    }
}
