<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2014 Neil Lathwood <https://github.com/laf/ http://www.lathwood.co.uk/fa>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

function opentsdb_connect()
{
    global $config;

    $opentsdb = fsockopen($config['opentsdb']['host'], $config['opentsdb']['port']);
    if ($opentsdb !== false) {
        echo "Connection made to {$config['opentsdb']['host']} for OpenTSDB support\n";
    } else {
        echo "Connection to {$config['opentsdb']['host']} has failed, OpenTSDB support disabled\n";
        $config['noopentsdb'] = true;
    }
}// end opentsdb_connect

function opentsdb_update($device, $measurement, $tags, $fields)
{
    global $opentsdb;

    if ($opentsdb !== false) {
        $timestamp = time();
        $hostname_tag = sprintf('%s=%s ', 'hostname', $device['hostname']);
        $tmp_tags = array($hostname_tag);

        foreach ($tags as $k => $v) {
            $v = preg_replace(array('/ /','/,/','/=/'), '_', $v);
            if (!empty($v)) {
                array_push($tmp_tags, sprintf('%s=%s ', $k, $v));
            }
        }

        foreach ($fields as $k => $v) {
            $line = sprintf('put %s %d %d %s', $measurement, $timestamp, $v, $tmp_tags);
            d_echo("Sending to OPenTSDB: $line\n");
            fwrite($opentsdb, $line . "\n");
        }
    }
}// end opentsdb_update
