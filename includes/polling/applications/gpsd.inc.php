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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/*
 * GPSD Statistics
 * @author Karl Shea <karl@karlshea.com>
 * @copyright 2016 Karl Shea, LibreNMS
* @author Mike Centola <mcentola@appliedengdesign.com>
* @copyright 2019 Mike Centola, LibreNMS
 * @license GPL
 * @package LibreNMS
 * @subpackage Polling
 */

// TODO: Add Metrics for Lat/Long/Altitude (Additions commented out)

/* Example Agent Data
   hdop:X.XX
   vdop:X.XX
   satellites:XX
   satellites_used:XX
*/

/*
 Example SNMP Extend Data
 {
   "data": {
       "mode": "X",
       "hdop": "X.XX",
       "vdop": "X.XX",
       "latitude": "XX.XXXXXXXX",
       "longitude": "XX.XXXXXXXXX",
       "altitude": "XXX.X",
       "satellites": "XX",
       "satellites_used": "XX"
   },
   "error:"0", "errorString":"", "version":"X.XX-X"
}
*/

use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\Exceptions\JsonAppParsingFailedException;
use LibreNMS\RRD\RrdDefinition;

$name = 'gpsd';

$legacy = false;

if (! empty($agent_data['app'][$name])) {
    $gpsd = $agent_data['app'][$name];

    $gpsd_parsed = [];

    foreach (explode("\n", (string) $gpsd) as $line) {
        [$field, $data] = explode(':', $line);
        $gpsd_parsed[$field] = $data;
    }

    // Set Fields

    $check_fields = [
        'mode',
        'hdop',
        'vdop',
        'satellites',
        'satellites_used',
    ];

    $basic_fields = [];

    foreach ($check_fields as $field) {
        if (! empty($gpsd_parsed[$field])) {
            $fields[$field] = $gpsd_parsed[$field];
        }
    }

    // old version did not include location info
    $metrics = $basic_fields;
    $legacy = true;
} else {
    // Use json_app_get to grab JSON formatted GPSD data
    try {
        $gpsd = json_app_get($device, $name);
    } catch (JsonAppParsingFailedException $e) {
        $legacy = $e->getOutput();

        $gpsd = [
            'data' => [],
        ];

        [$gpsd['data']['mode'], $gpsd['data']['hdop'], $gpsd['data']['vdop'],
            $gpsd['data']['latitude'], $gpsd['data']['longitude'], $gpsd['data']['altitude'],
            $gpsd['data']['satellites'], $gpsd['data']['satellites_used']] = explode("\n", (string) $legacy);

        $legacy = true;
    } catch (JsonAppException $e) {
        // Set Empty metrics and error message

        echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
        update_application($app, $e->getCode() . ':' . $e->getMessage(), []);

        return;
    }

    // Set Fields
    $basic_fields = [
        'mode' => $gpsd['data']['mode'],
        'hdop' => $gpsd['data']['hdop'],
        'vdop' => $gpsd['data']['vdop'],
        'satellites' => $gpsd['data']['satellites'],
        'satellites_used' => $gpsd['data']['satellites_used'],
    ];

    $location_fields = [
        'altitude' => $gpsd['data']['altitude'],
        'latitude' => $gpsd['data']['latitude'],
        'longitude' => $gpsd['data']['longitude'],
    ];

    // build the metrics
    $metrics = $basic_fields;
    if (! $legacy) {
        $metrics['altitude'] = $gpsd['data']['altitude'];
        $metrics['latitude'] = $gpsd['data']['latitude'];
        $metrics['longitude'] = $gpsd['data']['longitude'];
        $gpsd['data']['has_location'] = true;
    }

    // save the data chunk of the return to app data
    $app->data = $gpsd['data'];
}

// Generate basic RRD def
$basic_rrd_def = RrdDefinition::make()
    ->addDataset('mode', 'GAUGE', 0, 4)
    ->addDataset('hdop', 'GAUGE', 0, 100)
    ->addDataset('vdop', 'GAUGE', 0, 100)
    ->addDataset('satellites', 'GAUGE', 0, 40)
    ->addDataset('satellites_used', 'GAUGE', 0, 40);

// Update basic RRD
$basic_tags = [
    'name' => $name,
    'app_id' => $app->app_id,
    'rrd_name' => ['app', $name, $app->app_id],
    'rrd_def' => $basic_rrd_def,
];
app('Datastore')->put($device, 'app', $basic_tags, $basic_fields);

// if not legacy, we will have location info, save that via rrd
if (! $legacy) {
    // Generate location RRD def
    $location_rrd_def = RrdDefinition::make()
        ->addDataset('altitude', 'GAUGE')
        ->addDataset('latitude', 'GAUGE')
        ->addDataset('longitude', 'GAUGE');

    // Update location RRD
    $location_tags = [
        'name' => $name,
        'app_id' => $app->app_id,
        'rrd_name' => ['app', $name, $app->app_id, 'location'],
        'rrd_def' => $location_rrd_def,
    ];
    app('Datastore')->put($device, 'app', $location_tags, $location_fields);
}

if (! empty($agent_data['app'][$name])) {
    update_application($app, $gpsd, $metrics);
} else {
    update_application($app, 'OK', $metrics);
}
