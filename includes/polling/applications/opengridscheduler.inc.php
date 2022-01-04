<?php
/*
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @package    LibreNMS
 * @subpackage opengridscheduler
 * @link       https://www.librenms.org
 * @copyright  2017 LibreNMS
 * @author     SvennD <svennd@svennd.be>
*/

use LibreNMS\RRD\RrdDefinition;

$name = 'ogs';
$app_id = $app['app_id'];
$oid = '.1.3.6.1.4.1.8072.1.3.2.3.1.2.3.111.103.115';

echo ' ' . $name;

// get data through snmp
$ogs_data = snmp_get($device, $oid, '-Oqv');

// define the rrd
$rrd_name = ['app', $name, $app_id];
$rrd_def = RrdDefinition::make()
    ->addDataset('running_jobs', 'GAUGE', 0)
    ->addDataset('pending_jobs', 'GAUGE', 0)
    ->addDataset('suspend_jobs', 'GAUGE', 0)
    ->addDataset('zombie_jobs', 'GAUGE', 0);

// parse the data from the script
$data = explode("\n", $ogs_data);
$fields = [
    'running_jobs' => $data[0],
    'pending_jobs' => $data[1],
    'suspend_jobs' => $data[2],
    'zombie_jobs' => $data[3],
];

// push the data in an array and into the rrd
$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);
update_application($app, $ogs_data, $fields);

// cleanup
unset($ogs_data, $rrd_name, $rrd_def, $data, $fields, $tags);
