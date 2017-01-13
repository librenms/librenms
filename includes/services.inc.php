<?php

function get_service_status($device = null)
{
    $sql_query = "SELECT service_status, count(service_status) as count FROM services WHERE";
    $sql_param = array();
    $add = 0;

    if (!is_null($device)) {
        // Add a device filter to the SQL query.
        $sql_query .= " `device_id` = ?";
        $sql_param[] = $device;
        $add++;
    }

    if ($add == 0) {
        // No filters, remove " WHERE" -6
        $sql_query = substr($sql_query, 0, strlen($sql_query)-6);
    }
    $sql_query .= " GROUP BY service_status";

    // $service is not null, get only what we want.
    $result = dbFetchRows($sql_query, $sql_param);

    // Set our defaults to 0
    $service_count = array(0 => 0, 1 => 0, 2 => 0);
    // Rebuild the array in a more convenient method
    foreach ($result as $v) {
        $service_count[$v['service_status']] = $v['count'];
    }

    return $service_count;
}

function add_service($device, $type, $desc, $ip = 'localhost', $param = "", $ignore = 0)
{

    if (!is_array($device)) {
        $device = device_by_id_cache($device);
    }

    if (empty($ip)) {
        $ip = $device['hostname'];
    }

    $insert = array('device_id' => $device['device_id'], 'service_ip' => $ip, 'service_type' => $type, 'service_changed' => array('UNIX_TIMESTAMP(NOW())'), 'service_desc' => $desc, 'service_param' => $param, 'service_ignore' => $ignore, 'service_status' => 3, 'service_message' => 'Service not yet checked', 'service_ds' => '{}');
    return dbInsert($insert, 'services');
}

function service_get($device = null, $service = null)
{
    $sql_query = "SELECT `service_id`,`device_id`,`service_ip`,`service_type`,`service_desc`,`service_param`,`service_ignore`,`service_status`,`service_changed`,`service_message`,`service_disabled`,`service_ds` FROM `services` WHERE";
    $sql_param = array();
    $add = 0;

    d_echo("SQL Query: ".$sql_query);
    if (!is_null($service)) {
        // Add a service filter to the SQL query.
        $sql_query .= " `service_id` = ? AND";
        $sql_param[] = $service;
        $add++;
    }
    if (!is_null($device)) {
        // Add a device filter to the SQL query.
        $sql_query .= " `device_id` = ? AND";
        $sql_param[] = $device;
        $add++;
    }

    if ($add == 0) {
        // No filters, remove " WHERE" -6
        $sql_query = substr($sql_query, 0, strlen($sql_query)-6);
    } else {
        // We have filters, remove " AND" -4
        $sql_query = substr($sql_query, 0, strlen($sql_query)-4);
    }
    d_echo("SQL Query: ".$sql_query);

    // $service is not null, get only what we want.
    $services = dbFetchRows($sql_query, $sql_param);
    d_echo("Service Array: ".print_r($services, true)."\n");

    return $services;
}

function edit_service($update = array(), $service = null)
{
    if (!is_numeric($service)) {
        return false;
    }

    return dbUpdate($update, 'services', '`service_id`=?', array($service));
}

function delete_service($service = null)
{
    if (!is_numeric($service)) {
        return false;
    }

    return dbDelete('services', '`service_id` =  ?', array($service));
}

function discover_service($device, $service)
{
    if (! dbFetchCell('SELECT COUNT(service_id) FROM `services` WHERE `service_type`= ? AND `device_id` = ?', array($service, $device['device_id']))) {
        add_service($device, $service, "(Auto discovered) $service");
        log_event('Autodiscovered service: type '.mres($service), $device, 'service');
        echo '+';
    }
    echo "$service ";
}

function poll_service($service)
{
    global $config;

    $update = array();
    $old_status = $service['service_status'];
    $check_cmd = "";

    // if we have a script for this check, use it.
    $check_script = $config['install_dir'].'/includes/services/check_'.strtolower($service['service_type']).'.inc.php';
    if (is_file($check_script)) {
        include $check_script;
    }

    // If we do not have a cmd from the check script, build one.
    if ($check_cmd == "") {
        $check_cmd = $config['nagios_plugins'] . "/check_" . $service['service_type'] . " -H " . ($service['service_ip'] ? $service['service_ip'] : $service['hostname']);
        $check_cmd .= " " . $service['service_param'];
    }

    $service_id = $service['service_id'];
    // Some debugging
    d_echo("\nNagios Service - $service_id\n");
    // the check_service function runs $check_cmd through escapeshellcmd, so
    // echo the command as it will be run after being escaped
    $escaped_check_cmd = escapeshellcmd($check_cmd);
    d_echo("Request:  $escaped_check_cmd\n");
    list($new_status, $msg, $perf) = check_service($check_cmd);
    d_echo("Response: $msg\n");

    // If we have performance data we will store it.
    if (count($perf) > 0) {
        // Yes, We have perf data.
        $rrd_name = array('services', $service_id);

        // Set the DS in the DB if it is blank.
        $DS = array();
        foreach ($perf as $k => $v) {
            $DS[$k] = $v['uom'];
        }
        d_echo("Service DS: "._json_encode($DS)."\n");
        if ($service['service_ds'] == "") {
            $update['service_ds'] = json_encode($DS);
        }

        // rrd definition
        $rrd_def = array();
        foreach ($perf as $k => $v) {
            if (($v['uom'] == 'c') && !(preg_match('/[Uu]ptime/', $k))) {
                // This is a counter, create the DS as such
                $rrd_def[] = "DS:".$k.":COUNTER:600:0:U";
            } else {
                // Not a counter, must be a gauge
                $rrd_def[] = "DS:".$k.":GAUGE:600:0:U";
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
    $valid_uom = array ('us', 'ms', 'KB', 'MB', 'GB', 'TB', 'c', 's', '%', 'B');

    // Make our command safe.
    $command = 'LC_NUMERIC="C" '. escapeshellcmd($command);

    // Run the command and return its response.
    exec($command, $response_array, $status);

    // exec returns an array, lets implode it back to a string.
    $response_string = implode("\n", $response_array);

    // Split out the response and the performance data.
    list($response, $perf) = explode("|", $response_string);

    // Split each performance metric
    $perf_arr = explode(' ', $perf);

    // Create an array for our metrics.
    $metrics = array();

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
