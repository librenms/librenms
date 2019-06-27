<?php
/**
 * ServicePoll.php
 *
 * Service monitoring polling operations.
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
 * @package     LibreNMS
 * @link        http://librenms.org
 * @copyright   2019, KanREN Inc
 * @author      Heath Barnhart <hbarnhart@kanren.net>
 */

use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Service\ServiceDB;
use LibreNMS\Config;
use Log;

Class ServicePoll
{

    /**
     * Makes a service poll for a device
     * @param array $service Array of service attributes.
    **/
    function __construct($service)
    {

        $update = array();
        $old_status = $service['service_status'];
        $check_cmd = "";

        // if we have a script for this check, use it.
        // TODO convert individual scripts into classes, maybe similar to snmp trap handlers.
        $check_script = Cnfig::get('install_dir') . '/includes/services/check_' . strtolower($service['service_type']) . '.inc.php';
        if (is_file($check_script)) {
            include $check_script;
        }

        // If we do not have a cmd from the check script, build one.
        if ($check_cmd == "") {
            $check_cmd = Config::get('nagios_plugins') . "/check_" . $service['service_type'] . " -H " . ($service['service_ip'] ? $service['service_ip'] : $service['hostname']);
            $check_cmd .= " " . $service['service_param'];
        }

        $service_id = $service['service_id'];
        // Some debugging
        d_echo("\nNagios Service - $service_id\n");
        // the check_service function runs $check_cmd through escapeshellcmd, so
        list($new_status, $msg, $perf) = Self::checkService($check_cmd);
        d_echo("Response: $msg\n");

        // If we have performance data we will store it.
        if (count($perf) > 0) {
            // Yes, We have perf data.
            $rrd_name = array('services', $service_id);

            // Set the DS in the DB if it is blank.
            $DS = array();/
            foreach ($perf as $k => $v) {
                $DS[$k] = $v['uom'];
            }
            d_echo("Service DS: "._json_encode($DS)."\n");
            if (($service['service_ds'] == "{}") || ($service['service_ds'] == "")) {
                $update['service_ds'] = json_encode($DS);
            }

            // rrd definition
            $rrd_def = new RrdDefinition();
            foreach ($perf as $k => $v) {
                if (($v['uom'] == 'c') && !(preg_match('/[Uu]ptime/', $k))) {
                    // This is a counter, create the DS as such
                    $rrd_def->addDataset($k, 'COUNTER', 0);
                } else {
                    // Not a counter, must be a gauge
                    $rrd_def->addDataset($k, 'GAUGE', 0);
                }
            }

            // Update data
            $fields = array();
            foreach ($perf as $k => $v) {
                $fields[$k] = $v['value'];
            }

            $tags = compact('service_id', 'rrd_name', 'rrd_def');
                        //TODO not sure if we have $device at this point, if we do replace faked $device
            data_update(array('hostname' => $service['hostname']), 'services', $tags, $fields);
        }

        if ($old_status != $new_status) {
            // Status has changed, update.
            $update['service_changed'] = time();
            $update['service_status'] = $new_status;
            $update['service_message'] = $msg;

            list($oldStatusText, $newStatusText, $severity)  = Self::getSeverity($old_status, $new_status);

            Log::event(
                "Service '{$service['service_type']}' changed status from $oldStatusText to $newStatusText - $msg",
                $service['device_id'],
                'service',
                $severity,
                $service['service_id']
            );
        }

        if ($service['service_message'] != $msg) {
            // Message has changed, update.
            $update['service_message'] = $msg;
        }

        if (count($update) > 0) {
            ServiceDB::editService($update, $service['service_id']);
        }

        return true;
    }

    /**
     * Runs the Nagios Plugins and custom scripts, then formats results
     * If the plugin's response code is not OK and then it skips the check
     * and returns the response code.
     *
     * @param string $command The Nagios plugin or script to execute
     * @return mixed Returns the formated values and responses.
    **/
    static function checkService($command)
    {
        // This array is used to test for valid UOM's to be used for graphing.
        // Valid values from: https://nagios-plugins.org/doc/guidelines.html#AEN200
        // Note: This array must be decend from 2 char to 1 char so that the search works correctly.
        $valid_uom = array ('us', 'ms', 'KB', 'MB', 'GB', 'TB', 'c', 's', '%', 'B');

        // Make our command safe.
        $parts = preg_split('~(?:\'[^\']*\'|"[^"]*")(*SKIP)(*F)|\h+~', trim($command));
        $safe_command = implode(' ', array_map(function ($part) {
            $trimmed = preg_replace('/^(\'(.*)\'|"(.*)")$/', '$2$3', $part);
            return escapeshellarg($trimmed);
        }, $parts));

        d_echo("Request:  $safe_command\n");

        // Run the command and return its response.
        exec('LC_NUMERIC="C" ' . $safe_command, $response_array, $status);

        // exec returns an array, lets implode it back to a string.
        $response_string = implode("\n", $response_array);

        // Split out the response and the performance data.
        list($response, $perf) = explode("|", $response_string);

        // Split each performance metric
        $perf_arr = explode(' ', $perf);
        
                // Create an array for our metrics.
        $metrics = array();

        // Check to see if pluggin has returned status OK. If not and first run of service we do not want to poll
        if ($status != 0 && is_null($service['service_ds'])) {
            return array ($status, trim($response), $metrics);
        }

        // Loop through the perf string extracting our metric data
        foreach ($perf_arr as $string) {
            // Separate the DS and value: DS=value
            list ($ds,$values) = explode('=', trim($string));

            // Keep the first value, discard the others.
            list($value,,,) = explode(';', trim($values));
            $value = trim($value);

            // Set an empty uom
            $uom = '';

            // is the UOM valid - https://nagios-plugins.org/doc/guidelines.html#AEN200
            foreach ($valid_uom as $v) {
                if ((strlen($value)-strlen($v)) === strpos($value, $v)) {
                    // Yes, store and strip it off the value
                    $uom = $v;
                    $value = substr($value, 0, -strlen($v));
                    break;
                }
            }

            if ($ds != "") {
                // Normalize ds for rrd : ds-name must be 1 to 19 characters long in the characters [a-zA-Z0-9_]
                // http://oss.oetiker.ch/rrdtool/doc/rrdcreate.en.html
                $normalized_ds = preg_replace('/[^a-zA-Z0-9_]/', '', $ds);
                // if ds_name is longer than 19 characters, only use the first 19
                if (strlen($normalized_ds) > 19) {
                    $normalized_ds = substr($normalized_ds, 0, 19);
                    d_echo($ds . " exceeded 19 characters, renaming to " . $normalized_ds . "\n");
                }
                if ($ds != $normalized_ds) {
                    // ds has changed. check if normalized_ds is already in the array
                    if (isset($metrics[$normalized_ds])) {
                        d_echo($normalized_ds . " collides with an existing index\n");
                        $perf_unique = 0;
                        // Try to generate a unique name
                        for ($i = 0; $i<10; $i++) {
                            $tmp_ds_name = substr($normalized_ds, 0, 18) . $i;
                            if (!isset($metrics[$tmp_ds_name])) {
                                d_echo($normalized_ds . " collides with an existing index\n");
                                $normalized_ds = $tmp_ds_name;
                                $perf_unique = 1;
                                break 1;
                            }
                        }
                        if ($perf_unique == 0) {
                            // Try harder to generate a unique name
                            for ($i = 0; $i<10; $i++) {
                                for ($j = 0; $j<10; $j++) {
                                    $tmp_ds_name = substr($normalized_ds, 0, 17) . $j . $i;
                                    if (!isset($perf[$tmp_ds_name])) {
                                    $normalized_ds = $tmp_ds_name;
                                        $perf_unique = 1;
                                        break 2;
                                    }
                                }
                            }
                        }
                        if ($perf_unique == 0) {
                            d_echo("could not generate a unique ds-name for " . $ds . "\n");
                        }
                    }
                    $ds = $normalized_ds ;
                }
                // We have a DS. Add an entry to the array.
                d_echo("Perf Data - DS: ".$ds.", Value: ".$value.", UOM: ".$uom."\n");
                $metrics[$ds] = array ('value'=>$value, 'uom'=>$uom);
            } else {
                // No DS. Don't add an entry to the array.
                d_echo("Perf Data - None.\n");
            }
        }

        return array ($status, $response, $metrics);
    }
}
