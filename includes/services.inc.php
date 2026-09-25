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
    // keep legacy signature, delegate to modern implementation
    return \LibreNMS\Services::addService($device, $type, $desc, $ip, $param, $ignore, $disabled, $template_id, $name);
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

function poll_service($service)
{
    $update = [];
    $old_status = $service['service_status'];
    $service['service_type'] = Clean::fileName($service['service_type']);
    $service['service_ip'] = IP::isValid($service['service_ip']) ? $service['service_ip'] : Clean::fileName($service['service_ip']);
    $service['hostname'] = IP::isValid($service['hostname']) ? $service['hostname'] : Clean::fileName($service['hostname']);
    $service['overwrite_ip'] = IP::isValid($service['overwrite_ip']) ? $service['overwrite_ip'] : Clean::fileName($service['overwrite_ip']);
    $check_cmd = '';
    $check_parser = null;

    // if we have a script for this check, use it.
    $check_script = \LibreNMS\Services::customCheckPath($service['service_type']);
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
    [$new_status, $msg, $perf] = check_service($check_cmd, $check_parser ?? null);
    $update['service_checked'] = time();
    d_echo("Response: $msg\n");

    // If we have performance data we will store it.
    if (count($perf) > 0) {
        // Yes, We have perf data.
        $rrd_name = ['services', $service_id];

        // Set the DS in the DB if it is blank.
        $DS = [];
        foreach ($perf as $k => $v) {
            $DS[$k] = ['uom' => $v['uom'], 'full_name' => $v['full_name']];
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
        $rules = new AlertRules($service['device_id']);
        $rules->run();
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

function check_service($command, ?callable $parser = null)
{
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

    $metrics = \LibreNMS\Services::parsePerfdata($perf);

    if ($parser) {
        $metrics = $parser($response_string, $metrics);
    } elseif (empty($metrics)) {
        $metrics = \LibreNMS\Services::parseStats($response_string);
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
