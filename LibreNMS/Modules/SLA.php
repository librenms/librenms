<?php
/**
 * PrinterSupplies.php
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
// use App\Observers\ModuleModelObserver;
use Illuminate\Support\Collection;
// use Illuminate\Support\Str;
// use LibreNMS\DB\SyncsModels;
// use LibreNMS\Enum\Alert;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\RRD\RrdDefinition;
use Log;

class SLA implements Module
{
    use SyncsModels;

    /**
     * Discover this module. Heavier processes can be run here
     * Run infrequently (default 4 times a day)
     *
     * @param OS $os
     */
    public function discover(OS $os)
    {
        $device = $os->getDeviceArray();

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

        // TOCHANGE
        // Get existing SLAs
        //$existing_slas = dbFetchColumn('SELECT `sla_id` FROM `slas` WHERE `device_id` = :device_id AND `deleted` = 0', ['device_id' => $device['device_id']]);
        $existing_slas = Sla::select('sla_id')
            ->where('device_id', $device['device_id'])
            ->where('deleted', 0)
            ->get();
        foreach ($existing_slas as $existing_sla) {}
           echo "SLA_ID TEST : " . $existing_sla;
        }

        $query_data = [
            'device_id' => $device['device_id'],
        ];

        // TOCHANGE
        // To ensure unity of mock sla_nr field
        $max_sla_nr = dbFetchCell('SELECT MAX(`sla_nr`) FROM `slas` WHERE `device_id` = :device_id', $query_data);
        $i = 1;

        foreach ($sla_table as $sla_key => $sla_config) {
            // To get right owner index and test name from $sla_table key
            $prop_id = explode('.', $sla_key);
            $owner = $prop_id[0];
            $test = $prop_id[1];

            $query_data = [
                'device_id' => $device['device_id'],
                'owner'     => $owner,
                'tag'       => $test,
            ];
            // TOCHANGE
            $sla_data = dbFetchRows('SELECT `sla_id`, `sla_nr` FROM `slas` WHERE `device_id` = :device_id AND `owner` = :owner AND `tag` = :tag', $query_data);
            $sla_id = $sla_data[0]['sla_id'];
            $sla_nr = $sla_data[0]['sla_nr'];

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
        
            $data['rtt_type'] = retrieveJuniperType($data['rtt_type'])

            if (! $sla_id) {
                $data['sla_nr'] = $max_sla_nr + $i;
                // TOCHANGE
                $sla_id = dbInsert($data, 'slas');
                $i++;
                echo '+';
            } else {
                // Remove from the list
                $existing_slas = array_diff($existing_slas, [$sla_id]);
    
                // TOCHANGE
                dbUpdate($data, 'slas', 'sla_id = ?', [$sla_id]);
                echo '.';
                //TOTRY
                ModuleModelObserver::observe(Slas::class);
                $this->syncModels($os->getDevice(), 'slas', $data);
            }
        }
        // Mark all remaining SLAs as deleted
        foreach ($existing_slas as $existing_sla) {
            // TOCHANGE
            dbUpdate(['deleted' => 1], 'slas', 'sla_id = ?', [$existing_sla]);
            echo '-';
        }

        echo "\n";
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

        // TOCHANGE
        // Gather our SLA's from the DB.
        $slas = dbFetchRows('SELECT * FROM `slas` WHERE `device_id` = ? AND `deleted` = 0', [$device['device_id']]);

        $slas = $os->getDevice()->slas;

        if (count($slas) > 0) {
        // We have SLA's, lets go!!!
        
            // Go get some data from the device.
            $pingCtlResults = snmp_walk($device, 'pingMIB.pingObjects.pingCtlTable.pingCtlEntry', '-OQUs', '+DISMAN-PING-MIB', $mibdir);
            $pingResults = snmp_walk($device, 'pingMIB.pingObjects.pingResultsTable.pingResultsEntry', '-OQUs', '+DISMAN-PING-MIB', $mibdir);
            $jnxPingResults = snmp_walk($device, 'jnxPingResultsEntry', '-OQUs', '+JUNIPER-PING-MIB', $mibdir);
        
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
                $time = fixdate($jnxPingResults_table[$owner . '.' . $test]['jnxPingResultsTime']);
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
                            // 'rtt_sense' => $pingResults_table[$owner . "." .$test]['jnxPingResults'],
                            'ProbeResponses' => $pingResults_table[$owner . '.' . $test]['pingResultsProbeResponses'],
                            'ProbeLoss' => $pingResults_table[$owner . '.' . $test]['pingResultsSentProbes'] - $pingResults_table[$owner . '.' . $test]['pingResultsProbeResponses'],
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
                    $updated = dbUpdate($update, 'slas', '`sla_id` = ?', [$sla['sla_id']]);
                }

        }

        $toner_snmp = snmp_get_multi_oid($device, $toner_data->pluck('supply_oid')->toArray());

        foreach ($toner_data as $toner) {
            echo 'Checking toner ' . $toner['supply_descr'] . '... ';

            $raw_toner = $toner_snmp[$toner['supply_oid']];
            $tonerperc = self::getTonerLevel($device, $raw_toner, $toner['supply_capacity']);
            echo $tonerperc . " %\n";

            $tags = [
                'rrd_def'     => RrdDefinition::make()->addDataset('toner', 'GAUGE', 0, 20000),
                'rrd_name'    => ['toner', $toner['supply_index']],
                'rrd_oldname' => ['toner', $toner['supply_descr']],
                'index'       => $toner['supply_index'],
            ];
            data_update($device, 't
            oner', $tags, $tonerperc);

            // Log empty supplies (but only once)
            if ($tonerperc == 0 && $toner['supply_current'] > 0) {
                Log::event(
                    'Toner ' . $toner['supply_descr'] . ' is empty',
                    $os->getDevice(),
                    'toner',
                    Alert::ERROR,
                    $toner['supply_id']
                );
            }

            // Log toner swap
            if ($tonerperc > $toner['supply_current']) {
                Log::event(
                    'Toner ' . $toner['supply_descr'] . ' was replaced (new level: ' . $tonerperc . '%)',
                    $os->getDevice(),
                    'toner',
                    Alert::NOTICE,
                    $toner['supply_id']
                 );
            }

            $toner->supply_current = $tonerperc;
            $toner->supply_capacity = $toner['supply_capacity'];
            $toner->save();
        }
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

    /**
     * Retrieve specific Juniper PingCtlType
     */
    private function retrieveJuniperType($mib_location)
    {
        $rtt_type = NULL;
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
            default:
                $rtt_type = NULL;
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
