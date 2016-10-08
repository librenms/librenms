<?php
/*
 * Copyright (C) 2015 Daniel Preussker <f0o@devilcode.org>
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
 */

/*
 * GPSD Statistics
 * @author Karl Shea <karl@karlshea.com>
 * @copyright 2016 Karl Shea, LibreNMS
 * @license GPL
 * @package LibreNMS
 * @subpackage Polling
 */

$name = 'gpsd';
$app_id = $app['app_id'];
if (!empty($agent_data['app'][$name]) && $app_id > 0) {
    echo ' '.$name;
    $gpsd = $agent_data['app'][$name];
    $gpsd_parsed  = array();

    foreach (explode("\n", $gpsd) as $line) {
        list ($field, $data) = explode(':', $line);
        $gpsd_parsed[$field] = $data;
    }

    $rrd_name = array('app', $name, $app_id);
    $rrd_def = array(
        'DS:mode:GAUGE:600:0:4',
        'DS:hdop:GAUGE:600:0:100',
        'DS:vdop:GAUGE:600:0:100',
        'DS:satellites:GAUGE:600:0:40',
        'DS:satellites_used:GAUGE:600:0:40',
    );

    $check_fields = array(
        'mode',
        'hdop',
        'vdop',
        'satellites',
        'satellites_used',
    );

    $fields = array();

    foreach ($check_fields as $field) {
        if (!empty($gpsd_parsed[$field])) {
            $fields[$field] = $gpsd_parsed[$field];
        }
    }

    $tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
    data_update($device, 'app', $tags, $fields);
}
