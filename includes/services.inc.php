<?php

use App\Facades\LibrenmsConfig;
use App\Models\Eventlog;
use App\Models\Service;
use LibreNMS\Alert\AlertRules;
use LibreNMS\Enum\Severity;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Util\Clean;
use LibreNMS\Util\IP;

function add_service($device, $type, $desc, $ip = '', $param = '', $ignore = 0, $disabled = 0, $template_id = '', $name = '')
{
    $device = DeviceCache::get(is_array($device) ? $device['device_id'] : $device);

    if (empty($ip)) {
        $ip = $device->pollerTarget();
    }

    $insert = ['device_id' => $device->device_id, 'service_ip' => $ip, 'service_type' => $type, 'service_desc' => $desc, 'service_param' => $param, 'service_ignore' => $ignore, 'service_status' => 3, 'service_message' => 'Service not yet checked', 'service_ds' => '{}', 'service_disabled' => $disabled, 'service_template_id' => $template_id, 'service_name' => $name];

    return Service::create($insert);
}

function service_get($device = null, $service = null)
{
    if (! is_null($service)) {
        // Add a service filter to the SQL query.
        $services = Service::query()->where('service_id', $service)->get();
    } elseif (! is_null($device)) {
        $services = Service::query()->where('device_id', $device)->get();
    } else {
        $services = Service::query()->get();
    }

    d_echo('Service Array: ' . print_r($services, true) . "\n");

    return $services->toArray();
}

function edit_service($update = [], $service = null)
{
    if (! is_numeric($service)) {
        return false;
    }

    return Service::query()->where('service_id', $service)->update($update);
}

function delete_service($service = null)
{
    if (! is_numeric($service)) {
        return false;
    }

    return Service::query()->where('service_id', $service)->delete();
}

function discover_service($device, $service)
{
    if (Service::query()->where('service_type', $service)->where('device_id', $device['device_id'])->doesntExist()) {
        add_service($device, $service, "$service Monitoring (Auto Discovered)", null, null, 0, 0, 0, "AUTO: $service");
        Eventlog::log('Autodiscovered service: type ' . $service, $device['device_id'], 'service', Severity::Info);
        echo '+';
    }
    echo "$service ";
}

function poll_service($service)
{
    $update = [];
    $old_status = $service['service_status'];
    $service['service_type'] = Clean::fileName($service['service_type']);
    $service['service_ip'] = IP::isValid($service['service_ip']) ? $service['service_ip'] : Clean::fileName($service['service_ip']);
    $service['hostname'] = IP::isValid($service['hostname']) ? $service['hostname'] : Clean::fileName($service['hostname']);
    $service['overwrite_ip'] = IP::isValid($service['overwrite_ip']) ? $service['overwrite_ip'] : Clean::fileName($service['overwrite_ip']);
    $check_cmd = '';

    // if we have a script for this check, use it.
    $check_script = LibrenmsConfig::get('install_dir') . '/includes/services/check_' . strtolower((string) $service['service_type']) . '.inc.php';
    if (is_file($check_script)) {
        include $check_script;
    }

    // If we do not have a cmd from the check script, build one.
    if ($check_cmd == '') {
        $check_cmd = LibrenmsConfig::get('nagios_plugins') . '/check_' . $service['service_type'] . ' -H ' . ($service['service_ip'] ?: $service['hostname']);
        $check_cmd .= ' ' . $service['service_param'];
    }

    $service_id = $service['service_id'];
    // Some debugging
    d_echo("\nNagios Service - $service_id\n");
    // the check_service function runs $check_cmd through escapeshellcmd, so
    [$new_status, $msg, $perf] = check_service($check_cmd);
    d_echo("Response: $msg\n");

    // If we have performance data we will store it.
    if (count($perf) > 0) {
        // Yes, We have perf data.
        $rrd_name = ['services', $service_id];

        // Set the DS in the DB if it is blank.
        $DS = [];
        foreach ($perf as $k => $v) {
            $DS[$k] = $v['uom'];
        }
        d_echo('Service DS: ' . json_encode($DS, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n");
        if (($service['service_ds'] == '{}') || ($service['service_ds'] == '')) {
            $update['service_ds'] = json_encode($DS);
        }

        // rrd definition
        $rrd_def = new RrdDefinition();
        foreach ($perf as $k => $v) {
            if (($v['uom'] == 'c') && ! preg_match('/[Uu]ptime/', (string) $k)) {
                // This is a counter, create the DS as such
                $rrd_def->addDataset($k, 'COUNTER', 0);
            } else {
                // Not a counter, must be a gauge
                $rrd_def->addDataset($k, 'GAUGE', 0);
            }
        }

        // Update data
        $fields = [];
        foreach ($perf as $k => $v) {
            $fields[$k] = $v['value'];
        }

        $tags = ['service_id' => $service_id, 'rrd_name' => $rrd_name, 'rrd_def' => $rrd_def];
        //TODO not sure if we have $device at this point, if we do replace faked $device
        app('Datastore')->put($service, 'services', $tags, $fields);
    }

    if ($old_status != $new_status) {
        // Status has changed, update.
        $update['service_changed'] = time();
        $update['service_status'] = $new_status;
        $update['service_message'] = $msg;

        // TODO: Put the 3 lines below in a function getStatus(int) ?
        $status_text = [0 => 'OK', 1 => 'Warning', 3 => 'Unknown'];
        $old_status_text = $status_text[$old_status] ?? 'Critical';
        $new_status_text = $status_text[$new_status] ?? 'Critical';

        Eventlog::log(
            "Service {$service['service_name']} ({$service['service_type']})' changed status from $old_status_text to $new_status_text - {$service['service_desc']} - $msg",
            $service['device_id'],
            'service',
            Severity::Warning,
            $service['service_id']
        );

        // Run alert rules due to status changed
        $rules = new AlertRules;
        $rules->runRules($service['device_id']);
    }

    if ($service['service_message'] != $msg) {
        // Message has changed, update.
        $update['service_message'] = $msg;
    }

    if (count($update) > 0) {
        edit_service($update, $service['service_id']);
    }

    return true;
}

function check_service($command)
{
    // This array is used to test for valid UOM's to be used for graphing.
    // Valid values from: https://nagios-plugins.org/doc/guidelines.html#AEN200
    // Note: This array must be decend from 2 char to 1 char so that the search works correctly.
    $valid_uom = ['us', 'ms', 'KB', 'MB', 'GB', 'TB', 'c', 's', '%', 'B'];

    // Make our command safe.
    $parts = preg_split('~(?:\'[^\']*\'|"[^"]*")(*SKIP)(*F)|\h+~', trim((string) $command));
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
    [$response, $perf] = explode('|', $response_string, 2) + ['', ''];

    // Split performance metrics into an array
    preg_match_all('/\'[^\']*\'\S*|\S+/', $perf, $perf_arr);
    // preg_match_all returns a 2D array, we only need the first one
    $perf_arr = $perf_arr[0];

    // Create an array for our metrics.
    $metrics = [];

    // Loop through the perf string extracting our metric data
    foreach ($perf_arr as $string) {
        // Separate the DS and value: DS=value
        [$ds,$values] = array_pad(explode('=', trim($string)), 2, '');

        // Keep the first value, discard the others.
        $value = $values ? explode(';', trim($values)) : [];
        $value = trim($value[0] ?? '');

        // Set an empty uom
        $uom = '';

        // is the UOM valid - https://nagios-plugins.org/doc/guidelines.html#AEN200
        foreach ($valid_uom as $v) {
            if ((strlen($value) - strlen($v)) === strpos($value, $v)) {
                // Yes, store and strip it off the value
                $uom = $v;
                $value = substr($value, 0, -strlen($v));
                break;
            }
        }

        if ($ds != '') {
            // Normalize ds for rrd : ds-name must be 1 to 19 characters long in the characters [a-zA-Z0-9_]
            // http://oss.oetiker.ch/rrdtool/doc/rrdcreate.en.html
            $normalized_ds = preg_replace('/[^a-zA-Z0-9_]/', '', $ds);
            // if ds_name is longer than 19 characters, only use the first 19
            if (strlen((string) $normalized_ds) > 19) {
                $normalized_ds = substr((string) $normalized_ds, 0, 19);
                d_echo($ds . ' exceeded 19 characters, renaming to ' . $normalized_ds . "\n");
            }
            if ($ds != $normalized_ds) {
                // ds has changed. check if normalized_ds is already in the array
                if (isset($metrics[$normalized_ds])) {
                    d_echo($normalized_ds . " collides with an existing index\n");
                    $perf_unique = 0;
                    // Try to generate a unique name
                    for ($i = 0; $i < 10; $i++) {
                        $tmp_ds_name = substr((string) $normalized_ds, 0, 18) . $i;
                        if (! isset($metrics[$tmp_ds_name])) {
                            d_echo($normalized_ds . " collides with an existing index\n");
                            $normalized_ds = $tmp_ds_name;
                            $perf_unique = 1;
                            break;
                        }
                    }
                    if ($perf_unique == 0) {
                        // Try harder to generate a unique name
                        for ($i = 0; $i < 10; $i++) {
                            for ($j = 0; $j < 10; $j++) {
                                $tmp_ds_name = substr((string) $normalized_ds, 0, 17) . $j . $i;
                                if (! isset($perf[$tmp_ds_name])) {
                                    $normalized_ds = $tmp_ds_name;
                                    $perf_unique = 1;
                                    break 2;
                                }
                            }
                        }
                    }
                    if ($perf_unique == 0) {
                        d_echo('could not generate a unique ds-name for ' . $ds . "\n");
                    }
                }
                $ds = $normalized_ds;
            }
            // We have a DS. Add an entry to the array.
            d_echo('Perf Data - DS: ' . $ds . ', Value: ' . $value . ', UOM: ' . $uom . "\n");
            $metrics[$ds] = ['value' => $value, 'uom' => $uom];
        } else {
            // No DS. Don't add an entry to the array.
            d_echo("Perf Data - None.\n");
        }
    }

    return [$status, $response, $metrics];
}

/**
 * List all available services from nagios plugins directory
 *
 * @return array
 */
function list_available_services()
{
    return \LibreNMS\Services::list();
}
