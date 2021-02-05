<?php

/*

LibreNMS Application for monitoring power consumption and cost

@link       https://www.upaya.net.au/
@copyright  2021 Ben Carbery
@author     Ben Carbery <yrebrac@upaya.net.au>

LICENSE - GPLv3

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
version 3. See https://www.gnu.org/licenses/gpl-3.0.txt

*/

use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\RRD\RrdDefinition;

$name = 'powermon';
$app_id = $app['app_id'];

echo $name;

try {
    $result = json_app_get($device, $name);
} catch (JsonAppException $e) {
    echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
    update_application($app, $e->getCode() . ':' . $e->getMessage(), []);
    // Set empty metrics and error message
    log_event('application ' . $name . ' caught JsonAppException');

    return;
}
// should be doing something with error codes/messages returned in the snmp
// result or will they be caught above?

$rrd_name = ['app', $name, $app_id];
$rrd_def = RrdDefinition::make()
    ->addDataset('watts-gauge', 'GAUGE', 0)
    ->addDataset('watts-abs', 'ABSOLUTE', 0)
    ->addDataset('rate', 'GAUGE', 0);

$fields = [
    'watts-gauge'       => $result['data']['reading'],
    'watts-abs'         => $result['data']['reading'],
    'rate'              => $result['data']['supply']['rate'],
];

/*
log_event(
      "watts-gauage: " . $result['data']['reading']
    . ", watts-abs: " . $result['data']['reading']
);
 */

$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);
update_application($app, 'OK', $fields);
