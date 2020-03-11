<?php

use LibreNMS\Config;

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

function influxdb_connect()
{
    $influxdb_cred = '';
    if (!empty(Config::get('influxdb.username')) && !empty(Config::get('influxdb.password'))) {
        $influxdb_cred = Config::get('influxdb.username') . ':' . Config::get('influxdb.password') . '@';
        d_echo('Using authentication for InfluxDB');
    }
    $influxdb_url = $influxdb_cred . Config::get('influxdb.host') . ':' . Config::get('influxdb.port') . '/' . Config::get('influxdb.db');
    d_echo(Config::get('influxdb.transport') . " transport being used");
    if (Config::get('influxdb.transport') == 'http') {
        $influxdb_conn = 'influxdb';
    } elseif (Config::get('influxdb.transport') == 'https') {
        $influxdb_conn = 'https+influxdb';
    } elseif (Config::get('influxdb.transport') == 'udp') {
        $influxdb_conn = 'udp+influxdb';
    } else {
        echo 'InfluxDB support enabled but no valid transport details provided';
        return false;
    }

    $db = \InfluxDB\Client::fromDSN($influxdb_conn . '://' . $influxdb_url, Config::get('influxdb.timeout'), Config::get('influxdb.verifySSL'));
    return($db);
}// end influxdb_connect

function influx_update($device, $measurement, $tags, $fields)
{
    global $influxdb;
    if ($influxdb !== false) {
        $tmp_fields = array();
        $tmp_tags['hostname'] = $device['hostname'];
        foreach ($tags as $k => $v) {
            $v = preg_replace(array('/ /','/,/','/=/'), array('\ ','\,','\='), $v);
            if (empty($v)) {
                $v = '_blank_';
            }
            $tmp_tags[$k] = $v;
        }
        foreach ($fields as $k => $v) {
            if ($k == 'time') {
                $k = 'rtime';
            }
            $tmp_fields[$k] = force_influx_data($v);
            if ($tmp_fields[$k] === null) {
                unset($tmp_fields[$k]);
            }
        }
        
        d_echo("\nInfluxDB data:\n");
        d_echo($measurement);
        d_echo($tmp_tags);
        d_echo($tmp_fields);
        d_echo("\nEND\n");

        if (Config::get('noinfluxdb') !== true) {
            $points = array(
                new InfluxDB\Point(
                    $measurement,
                    null, // the measurement value
                    $tmp_tags,
                    $tmp_fields // optional additional fields
                )
            );
            try {
                $result = $influxdb->writePoints($points);
            } catch (Exception $e) {
                d_echo("Caught exception: " . $e->getMessage() . PHP_EOL);
                d_echo($e->getTrace());
            }
        } else {
            c_echo("[%gInfluxDB Disabled%n]\n");
        }//end if
    }//end if
}// end influx_update
