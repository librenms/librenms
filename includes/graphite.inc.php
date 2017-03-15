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
    global $graphite, $config;
    if ($graphite !== false) {

        $timestamp = time();
        $graphite_prefix = $config['graphite']['prefix'];
        // metrics will be built as prefix.hostname.measurement.field value timestamp
        // metric fields can not contain . as this is used by graphite as a field separator
        $hostname = preg_replace('/\./', '_', $device['hostname']);
        $measurement = preg_replace('/\./', '_', $measurement);
        foreach ($fields as $k => $v) {
          $metric = implode(".", array_filter(array($graphite_prefix, $hostname, $measurement, $k)));
          $line = implode(" ", array($metric, $v, $timestamp));
          fwrite($graphite, $line . "\n");
        }
    }
}

?>
