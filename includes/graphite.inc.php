<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2017 Falk Stern <https://github.com/fstern/ >
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

function graphite_update($device, $measurement, $tags, $fields)
{
    global $graphite;
    if ($graphite != false) {
        $timestamp = time();
        $graphite_prefix = \LibreNMS\Config::get('graphite.prefix');
        // metrics will be built as prefix.hostname.measurement.field value timestamp
        // metric fields can not contain . as this is used by graphite as a field separator
        $hostname = preg_replace('/\./', '_', $device['hostname']);
        $measurement = preg_replace(array('/\./', '/\//'), '_', $measurement);
        $measurement = preg_replace('/\|/', '.', $measurement);
        $measurement_name = preg_replace('/\./', '_', $tags['rrd_name']);
        if (is_array($measurement_name)) {
            $ms_name = implode(".", $measurement_name);
        } else {
            $ms_name = $measurement_name;
        }
        // remove the port-id tags from the metric
        if (preg_match('/^port-id\d+/', $ms_name)) {
            $ms_name = "";
        }

        foreach ($fields as $k => $v) {
            // Send zero for fields without values
            if (empty($v)) {
                $v = 0;
            }
            $metric = implode(".", array_filter(array($graphite_prefix, $hostname, $measurement, $ms_name, $k)));
            // Further sanitize the full metric before sending, whitespace isn't allowed
            $metric = preg_replace('/\s+/', '_', $metric);
            $line = implode(" ", array($metric, $v, $timestamp));
            d_echo("Sending $line\n");
            fwrite($graphite, $line . "\n");
        }
    }
}
