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
$app_id = $app['app_id'];

echo " $name\n";

if ($app_id > 0) {
    if (! empty($agent_data['app'][$name])) {
        $gpsd = $agent_data['app'][$name];

        $gpsd_parsed = [];

        foreach (explode("\n", $gpsd) as $line) {
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

        $fields = [];

        foreach ($check_fields as $field) {
            if (! empty($gpsd_parsed[$field])) {
                $fields[$field] = $gpsd_parsed[$field];
            }
        }
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
                $gpsd['data']['satellites'], $gpsd['data']['satellites_used']] = explode("\n", $legacy);
        } catch (JsonAppException $e) {
            // Set Empty metrics and error message

            echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
            update_application($app, $e->getCode() . ':' . $e->getMessage(), []);

            return;
        }

        // Set Fields
        $fields = [
            'mode' => $gpsd['data']['mode'],
            'hdop' => $gpsd['data']['hdop'],
            'vdop' => $gpsd['data']['vdop'],
            'satellites' => $gpsd['data']['satellites'],
            'satellites_used' => $gpsd['data']['satellites_used'],
        ];
    }

    // Generate RRD Def

    $rrd_name = ['app', $name, $app_id];
    $rrd_def = RrdDefinition::make()
        ->addDataset('mode', 'GAUGE', 0, 4)
        ->addDataset('hdop', 'GAUGE', 0, 100)
        ->addDataset('vdop', 'GAUGE', 0, 100)
        ->addDataset('satellites', 'GAUGE', 0, 40)
        ->addDataset('satellites_used', 'GAUGE', 0, 40);

    // Update Application
    $tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
    data_update($device, 'app', $tags, $fields);

    if (! empty($agent_data['app'][$name])) {
        update_application($app, $gpsd, $fields);
    } else {
        update_application($app, 'OK', $fields);
    }
}
