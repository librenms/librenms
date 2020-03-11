<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2017 Yacine Benamsili <https://github.com/yac01/librenms.git >
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

function opentsdb_update($device, $measurement, $tags, $fields)
{

    global $opentsdb;
    if (\LibreNMS\Config::get('opentsdb.enable') == true) {
        if ($opentsdb != true) {
            $opentsdb = fsockopen(\LibreNMS\Config::get('opentsdb.host'), \LibreNMS\Config::get('opentsdb.port'));
        }
        if ($opentsdb == true) {
            d_echo("Connection to OpenTSDB is done\n");
        } else {
            d_echo("Connection to OpenTSDB has failed\n");
        }

        $flag = \LibreNMS\Config::get('opentsdb.co');
          $timestamp = time();
          $tmp_tags = "hostname=".$device['hostname'];

        foreach ($tags as $k => $v) {
                 $v = str_replace(array(' ',',','='), '_', $v);
            if (!empty($v)) {
                $tmp_tags = $tmp_tags ." ". $k ."=".$v;
            }
        }

        if ($measurement == 'port') {
            foreach ($fields as $k => $v) {
                     $measurement = $k;
                if ($flag == true) {
                     $measurement = $measurement.".".$device['co'];
                }
                     $line = sprintf('put net.port.%s %d %f %s', strtolower($measurement), $timestamp, $v, $tmp_tags);
                     d_echo("Sending to OPenTSDB: $line\n");
                     fwrite($opentsdb, $line . "\n"); // send $line into OpenTSDB
            }
        } else {
            if ($flag == true) {
                $measurement = $measurement.'.'.$device['co'];
            }

            foreach ($fields as $k => $v) {
                     $tmp_tags_key = $tmp_tags ." ". "key" ."=".$k;
                     $line = sprintf('put net.%s %d %f %s', strtolower($measurement), $timestamp, $v, $tmp_tags_key);
                     d_echo("Sending to OPenTSDB: $line\n");
                     fwrite($opentsdb, $line . "\n"); // send $line into OpenTSDB
            }
        }
    }
}
