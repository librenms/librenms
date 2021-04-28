<?php
/**
 * SLA.php
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
 */

namespace LibreNMS\Modules;

use App\Models\Sla;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Util\IP;

class Slas implements Module
{
    //use SyncsModels;

    /**
     * Discover this module. Heavier processes can be run here
     * Run infrequently (default 4 times a day)
     *
     * @param OS $os
     */
    public function discover(OS $os)
    {
        $device = $os->getDeviceArray();

        if ($device['os_group'] == 'cisco') {
            $this->discoverSlas($device);
        } elseif ($device['os'] == 'junos') {
            $this->discoverRpms($device);
        }
        // } else if $device['os'] == 'huawei'
        // {
        //    $this->discoverHuaweis($device);
        // }
    }

    /**
     * Poll data for this module and update the DB / RRD.
     * Try to keep this efficient and only run if discovery has indicated there is a reason to run.
     * Run frequently (default every 5 minutes)
     *
     * @param OS $os
     */
    public function poll(OS $os)
    {
        $device = $os->getDeviceArray();

        if ($device['os_group'] == 'cisco') {
            $this->pollSlas($device);
        } elseif ($device['os'] == 'junos') {
            $this->pollRpms($device);
        }
        // } else if $device['os'] == 'huawei'
        // {
        //    $this->pollHuaweis($device);
        // }
    }

    /**
     * Remove all DB data for this module.
     * This will be run when the module is disabled.
     *
     * @param OS $os
     */
    public function cleanup(OS $os)
    {
        $os->getDevice()->printerSupplies()->delete();
    }

    private function discoverRpms($device)
    {
        $slas = snmp_walk($device, 'pingMIB.pingObjects.pingCtlTable.pingCtlEntry', '-OQUs', '+DISMAN-PING-MIB');

        // Index the MIB information
        $sla_table = [];
        foreach (explode("\n", $slas) as $sla) {
            $key_val = explode(' ', $sla, 3);

            $key = $key_val[0];
            $value = $key_val[2];

            $prop_id = explode('.', $key);

            $property = $prop_id[0];
            $owner = $prop_id[1];
            $test = $prop_id[2];

            $sla_table[$owner . '.' . $test][$property] = $value;
        }

        // Get existing SLAs
        $existing_slas = Sla::select('sla_id')
            ->where('device_id', $device['device_id'])
            ->where('deleted', 0)
            ->get();

        $query_data = [
            'device_id' => $device['device_id'],
        ];

        // To ensure unity of mock sla_nr field
        $max_sla_nr = Sla::where('device_id', $device['device_id'])
            ->max('sla_nr');
        $i = 1;

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
            $data['rtt_type'] = $this->retrieveJuniperType($data['rtt_type']);

            if (! $sla_id) {
                $data['sla_nr'] = $max_sla_nr + $i;

                Sla::insert($data);

                $i++;
                echo '+';
            } else {
                // Remove from the list
                $existing_slas = $existing_slas->except([$sla_id]);

                Sla::where('sla_id', $sla_id)
                    ->update($data);
                echo '.';
                //TOTRY
                // ModuleModelObserver::observe(Slas::class);
                // $this->syncModels($os->getDevice(), 'slas', $data);
            }
        }

        // Mark all remaining SLAs as deleted
        foreach ($existing_slas as $existing_sla) {
            Sla::where('sla_id', $existing_sla->sla_id)
                ->update(['deleted' => 1]);
            echo '-';
        }

        echo "\n";
    }

    private function discoverSlas($device)
    {
        $slas = snmp_walk($device, 'ciscoRttMonMIB.ciscoRttMonObjects.rttMonCtrl', '-Osq', '+CISCO-RTTMON-MIB');

        $sla_table = [];
        foreach (explode("\n", $slas) as $sla) {
            $key_val = explode(' ', $sla, 2);
            if (count($key_val) != 2) {
                $key_val[] = '';
            }

            $key = $key_val[0];
            $value = $key_val[1];

            $prop_id = explode('.', $key);
            if ((count($prop_id) != 2) || ! ctype_digit($prop_id[1])) {
                continue;
            }

            $property = $prop_id[0];
            $id = intval($prop_id[1]);

            $sla_table[$id][$property] = trim($value);
        }

        // Get existing SLAs
        $existing_slas = Sla::select('sla_id')
            ->where('device_id', $device['device_id'])
            ->where('deleted', 0)
            ->get();

        foreach ($sla_table as $sla_nr => $sla_config) {
            $sla_data = Sla::select('sla_id')
                ->where('device_id', $device['device_id'])
                ->where('sla_nr', $sla_nr)
                ->get();
            $sla_id = $sla_data[0]->sla_id;

            $data = [
                'device_id' => $device['device_id'],
                'sla_nr'    => $sla_nr,
                'owner'     => $sla_config['rttMonCtrlAdminOwner'],
                'tag'       => $sla_config['rttMonCtrlAdminTag'],
                'rtt_type'  => $sla_config['rttMonCtrlAdminRttType'],
                'status'    => ($sla_config['rttMonCtrlAdminStatus'] == 'active') ? 1 : 0,
                'opstatus'  => ($sla_config['rttMonLatestRttOperSense'] == 'ok') ? 0 : 2,
                'deleted'   => 0,
            ];

            // Some fallbacks for when the tag is empty
            if (! $data['tag']) {
                switch ($data['rtt_type']) {
                    case 'http':
                        $data['tag'] = $sla_config['rttMonEchoAdminURL'];
                        break;

                    case 'dns':
                        $data['tag'] = $sla_config['rttMonEchoAdminTargetAddressString'];
                        break;

                    case 'echo':
                        $data['tag'] = IP::fromHexString($sla_config['rttMonEchoAdminTargetAddress'], true);
                        break;

                    case 'jitter':
                        if ($sla_config['rttMonEchoAdminCodecType'] != 'notApplicable') {
                            $codec_info = ' (' . $sla_config['rttMonEchoAdminCodecType'] . ' @ ' . preg_replace('/milliseconds/', 'ms', $sla_config['rttMonEchoAdminCodecInterval']) . ')';
                        } else {
                            $codec_info = '';
                        }
                        $data['tag'] = IP::fromHexString($sla_config['rttMonEchoAdminTargetAddress'], true) . ':' . $sla_config['rttMonEchoAdminTargetPort'] . $codec_info;
                        break;
                }//end switch
            }//end if

            if (! $sla_id) {
                Sla::insert($data);
                echo '+';
            } else {
                // Remove from the list
                $existing_slas = $existing_slas->except([$sla_id]);

                Sla::where('sla_id', $sla_id)
                    ->update($data);
                echo '.';
            }
        }//end foreach

        // Mark all remaining SLAs as deleted
        foreach ($existing_slas as $existing_sla) {
            Sla::where('sla_id', $existing_sla->sla_id)
                ->update(['deleted' => 1]);
            echo '-';
        }

        echo "\n";
    }

    private function pollRpms($device)
    {
        // Gather our SLA's from the DB.
        $slas = Sla::where('device_id', $device['device_id'])
            ->where('deleted', 0)
            ->get();

        if (count($slas) > 0) {
            // We have SLA's, lets go!!!

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

            $pingResults_table = [];
            foreach (explode("\n", $pingResults) as $line) {
                $key_val = explode(' ', $line, 3);

                $key = $key_val[0];
                $value = $key_val[2];

                // To get owner index and test name
                $prop_id = explode('.', $key);
                $property = $prop_id[0];
                $owner = $prop_id[1];
                $test = $prop_id[2];

                $pingResults_table[$owner . '.' . $test][$property] = $value;
            }

            $pingCtlResults_table = [];
            foreach (explode("\n", $pingCtlResults) as $line) {
                $key_val = explode(' ', $line, 3);

                $key = $key_val[0];
                $value = $key_val[2];

                // To get owner index and test name
                $prop_id = explode('.', $key);
                $property = $prop_id[0];
                $owner = $prop_id[1];
                $test = $prop_id[2];

                $pingCtlResults_table[$owner . '.' . $test][$property] = $value;
            }

            // Get the needed informations
            $uptime = snmp_get($device, 'sysUpTime.0', '-Otv', 'SNMPv2-MIB');
            $time_offset = (time() - intval($uptime) / 100);

            foreach ($slas as $sla) {
                $sla_nr = $sla['sla_nr'];
                $rtt_type = $sla['rtt_type'];
                $owner = $sla['owner'];
                $test = $sla['tag'];

                // Lets process each SLA
                $time = $this->fixdate($jnxPingResults_table[$owner . '.' . $test]['jnxPingResultsTime']);
                $update = [];

                // Use DISMAN-PING Status codes.
                $opstatus = $pingCtlResults_table[$owner . '.' . $test]['pingCtlRowStatus'];

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
                $rrd_name = ['sla', $sla_nr];
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
                            'StdDevRttUs' => $pingResults_table[$owner . '.' . $test]['jnxPingResultsStdDevRttUs'] / 1000,
                            'ProbeResponses' => $pingResults_table[$owner . '.' . $test]['pingResultsProbeResponses'],
                            'ProbeLoss' => (int)$pingResults_table[$owner . '.' . $test]['pingResultsSentProbes'] - (int)$pingResults_table[$owner . '.' . $test]['pingResultsProbeResponses'],
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
    }

    private function pollSlas($device)
    {
        // Gather our SLA's from the DB.
        $slas = Sla::where('device_id', $device['device_id'])
            ->where('deleted', 0)
            ->get();

        if (count($slas) > 0) {
            // We have SLA's, lets go!!!

            // Go get some data from the device.
            $rttMonLatestRttOperTable = snmpwalk_array_num($device, '.1.3.6.1.4.1.9.9.42.1.2.10.1', 1);
            $rttMonLatestOper = snmpwalk_array_num($device, '.1.3.6.1.4.1.9.9.42.1.5', 1);

            $uptime = snmp_get($device, 'sysUpTime.0', '-Otv');
            $time_offset = (time() - intval($uptime) / 100);

            foreach ($slas as $sla) {
                $sla_id = $sla['sla_id'];
                $sla_nr = $sla['sla_nr'];
                $rtt_type = $sla['rtt_type'];

                // Lets process each SLA
                $unixtime = intval(($rttMonLatestRttOperTable['1.3.6.1.4.1.9.9.42.1.2.10.1.5'][$sla_nr] / 100 + $time_offset));
                $time = strftime('%Y-%m-%d %H:%M:%S', $unixtime);
                $update = [];

                // Use Nagios Status codes.
                $opstatus = $rttMonLatestRttOperTable['1.3.6.1.4.1.9.9.42.1.2.10.1.2'][$sla_nr];
                if ($opstatus == 1) {
                    $opstatus = 0;        // 0=Good
                } else {
                    $opstatus = 2;        // 2=Critical
                }

                // Populating the update array means we need to update the DB.
                if ($opstatus != $sla['opstatus']) {
                    $update['opstatus'] = $opstatus;
                }

                $rtt = $rttMonLatestRttOperTable['1.3.6.1.4.1.9.9.42.1.2.10.1.1'][$sla_nr];
                echo 'SLA ' . $sla_nr . ': ' . $rtt_type . ' ' . $sla['owner'] . ' ' . $sla['tag'] . '... ' . $rtt . 'ms at ' . $time . '\n';

                $fields = [
                    'rtt' => $rtt,
                ];

                // The base RRD
                $rrd_name = ['sla', $sla_nr];
                $rrd_def = RrdDefinition::make()->addDataset('rtt', 'GAUGE', 0, 300000);
                $tags = compact('sla_nr', 'rrd_name', 'rrd_def');
                data_update($device, 'sla', $tags, $fields);

                // Let's gather some per-type fields.
                switch ($rtt_type) {
                    case 'jitter':
                        $jitter = [
                            'PacketLossSD' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.2.1.26'][$sla_nr],
                            'PacketLossDS' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.2.1.27'][$sla_nr],
                            'PacketOutOfSequence' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.2.1.28'][$sla_nr],
                            'PacketMIA' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.2.1.29'][$sla_nr],
                            'PacketLateArrival' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.2.1.30'][$sla_nr],
                            'MOS' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.2.1.42'][$sla_nr] / 100,
                            'ICPIF' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.2.1.43'][$sla_nr],
                            'OWAvgSD' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.2.1.49'][$sla_nr],
                            'OWAvgDS' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.2.1.50'][$sla_nr],
                            'AvgSDJ' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.2.1.47'][$sla_nr],
                            'AvgDSJ' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.2.1.48'][$sla_nr],
                        ];
                        $rrd_name = ['sla', $sla_nr, $rtt_type];
                        $rrd_def = RrdDefinition::make()
                            ->addDataset('PacketLossSD', 'GAUGE', 0)
                            ->addDataset('PacketLossDS', 'GAUGE', 0)
                            ->addDataset('PacketOutOfSequence', 'GAUGE', 0)
                            ->addDataset('PacketMIA', 'GAUGE', 0)
                            ->addDataset('PacketLateArrival', 'GAUGE', 0)
                            ->addDataset('MOS', 'GAUGE', 0)
                            ->addDataset('ICPIF', 'GAUGE', 0)
                            ->addDataset('OWAvgSD', 'GAUGE', 0)
                            ->addDataset('OWAvgDS', 'GAUGE', 0)
                            ->addDataset('AvgSDJ', 'GAUGE', 0)
                            ->addDataset('AvgDSJ', 'GAUGE', 0);
                        $tags = compact('rrd_name', 'rrd_def', 'sla_nr', 'rtt_type');
                        data_update($device, 'sla', $tags, $jitter);
                        $fields = array_merge($fields, $jitter);
                        break;
                    case 'icmpjitter':
                        $icmpjitter = [
                            'PacketLoss' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.4.1.26'][$sla_nr],
                            'PacketOosSD' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.4.1.28'][$sla_nr],
                            'PacketOosDS' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.4.1.29'][$sla_nr],
                            'PacketLateArrival' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.4.1.32'][$sla_nr],
                            'JitterAvgSD' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.4.1.45'][$sla_nr],
                            'JitterAvgDS' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.4.1.46'][$sla_nr],
                            'LatencyOWAvgSD' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.4.1.47'][$sla_nr],
                            'LatencyOWAvgDS' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.4.1.48'][$sla_nr],
                            'JitterIAJOut' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.4.1.49'][$sla_nr],
                            'JitterIAJIn' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.4.1.50'][$sla_nr],
                        ];
                        $rrd_name = ['sla', $sla_nr, $rtt_type];
                        $rrd_def = RrdDefinition::make()
                            ->addDataset('PacketLoss', 'GAUGE', 0)
                            ->addDataset('PacketOosSD', 'GAUGE', 0)
                            ->addDataset('PacketOosDS', 'GAUGE', 0)
                            ->addDataset('PacketLateArrival', 'GAUGE', 0)
                            ->addDataset('JitterAvgSD', 'GAUGE', 0)
                            ->addDataset('JitterAvgDS', 'GAUGE', 0)
                            ->addDataset('LatencyOWAvgSD', 'GAUGE', 0)
                            ->addDataset('LatencyOWAvgDS', 'GAUGE', 0)
                            ->addDataset('JitterIAJOut', 'GAUGE', 0)
                            ->addDataset('JitterIAJIn', 'GAUGE', 0);
                        $tags = compact('rrd_name', 'rrd_def', 'sla_nr', 'rtt_type');
                        data_update($device, 'sla', $tags, $icmpjitter);
                        $fields = array_merge($fields, $icmpjitter);
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
    }

    /**
     * Retrieve specific Juniper PingCtlType
     */
    private function retrieveJuniperType($mib_location)
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
