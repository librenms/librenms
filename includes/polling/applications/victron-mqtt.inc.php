<?php

/**
 * victron-mqtt.inc.php
 *
 * LibreNMS application poller for Victron GX devices via MQTT
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 */

use LibreNMS\RRD\RrdDefinition;

$name = 'victron-mqtt';

// Get the MQTT host from application instance or device hostname
// Application should be configured with instance name as the MQTT host/IP
$mqtt_host = $app->app_instance ?: $device['hostname'];
$mqtt_port = 1883;

// Execute the MQTT collector script
$script = \LibreNMS\Config::get('install_dir') . '/scripts/victron-mqtt.py';
$output = shell_exec("python3 $script " . escapeshellarg($mqtt_host) . " $mqtt_port 2>&1");

if (empty($output)) {
    echo "ERROR: No output from victron-mqtt.py\n";
    update_application($app, 'ERROR: No output', []);
    return;
}

$data = json_decode($output, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo "ERROR: Invalid JSON from victron-mqtt.py\n";
    update_application($app, 'ERROR: Invalid JSON', []);
    return;
}

if (isset($data['error'])) {
    echo "ERROR: " . $data['error'] . "\n";
    update_application($app, 'ERROR: ' . $data['error'], []);
    return;
}

// Define RRD datasets for the metrics we collect
$rrd_def = RrdDefinition::make()
    // Battery
    ->addDataset('battery_soc', 'GAUGE', 0, 100)
    ->addDataset('battery_voltage', 'GAUGE', 0)
    ->addDataset('battery_current', 'GAUGE', -1000, 1000)
    ->addDataset('battery_power', 'GAUGE', -50000, 50000)
    ->addDataset('battery_time_to_go', 'GAUGE', 0)
    // AC Input
    ->addDataset('ac_in_l1_voltage', 'GAUGE', 0, 500)
    ->addDataset('ac_in_l1_current', 'GAUGE', -100, 100)
    ->addDataset('ac_in_l1_power', 'GAUGE', -50000, 50000)
    ->addDataset('ac_in_l1_frequency', 'GAUGE', 0, 100)
    // AC Output
    ->addDataset('ac_out_l1_voltage', 'GAUGE', 0, 500)
    ->addDataset('ac_out_l1_current', 'GAUGE', -100, 100)
    ->addDataset('ac_out_l1_power', 'GAUGE', -50000, 50000)
    ->addDataset('ac_out_l1_frequency', 'GAUGE', 0, 100)
    // Inverter DC
    ->addDataset('inverter_dc_voltage', 'GAUGE', 0, 100)
    ->addDataset('inverter_dc_current', 'GAUGE', -1000, 1000)
    ->addDataset('inverter_dc_power', 'GAUGE', -50000, 50000)
    // PV/Solar
    ->addDataset('pv_power', 'GAUGE', 0, 100000)
    ->addDataset('pv_current', 'GAUGE', 0, 1000)
    ->addDataset('pv_string_0_power', 'GAUGE', 0, 50000)
    ->addDataset('pv_string_0_voltage', 'GAUGE', 0, 500)
    ->addDataset('pv_string_1_power', 'GAUGE', 0, 50000)
    ->addDataset('pv_string_1_voltage', 'GAUGE', 0, 500)
    // Consumption
    ->addDataset('consumption_l1', 'GAUGE', 0, 50000)
    ->addDataset('consumption_out_l1', 'GAUGE', 0, 50000)
    // Grid
    ->addDataset('grid_l1_power', 'GAUGE', -50000, 50000)
    // Alarms (0=ok, 1=warning, 2=alarm)
    ->addDataset('alarm_grid_lost', 'GAUGE', 0, 2)
    ->addDataset('alarm_high_temp', 'GAUGE', 0, 2)
    ->addDataset('alarm_overload', 'GAUGE', 0, 2);

// Build fields array from collected data
$fields = [
    'battery_soc' => $data['battery_soc'] ?? null,
    'battery_voltage' => $data['battery_voltage'] ?? null,
    'battery_current' => $data['battery_current'] ?? null,
    'battery_power' => $data['battery_power'] ?? null,
    'battery_time_to_go' => isset($data['battery_time_to_go']) && $data['battery_time_to_go'] !== null ? $data['battery_time_to_go'] / 60 : null,
    'ac_in_l1_voltage' => $data['ac_in_l1_voltage'] ?? null,
    'ac_in_l1_current' => $data['ac_in_l1_current'] ?? null,
    'ac_in_l1_power' => $data['ac_in_l1_power'] ?? null,
    'ac_in_l1_frequency' => $data['ac_in_l1_frequency'] ?? null,
    'ac_out_l1_voltage' => $data['ac_out_l1_voltage'] ?? null,
    'ac_out_l1_current' => $data['ac_out_l1_current'] ?? null,
    'ac_out_l1_power' => $data['ac_out_l1_power'] ?? null,
    'ac_out_l1_frequency' => $data['ac_out_l1_frequency'] ?? null,
    'inverter_dc_voltage' => $data['inverter_dc_voltage'] ?? null,
    'inverter_dc_current' => $data['inverter_dc_current'] ?? null,
    'inverter_dc_power' => $data['inverter_dc_power'] ?? null,
    'pv_power' => $data['pv_power'] ?? null,
    'pv_current' => $data['pv_current'] ?? null,
    'pv_string_0_power' => $data['pv_string_0_power'] ?? null,
    'pv_string_0_voltage' => $data['pv_string_0_voltage'] ?? null,
    'pv_string_1_power' => $data['pv_string_1_power'] ?? null,
    'pv_string_1_voltage' => $data['pv_string_1_voltage'] ?? null,
    'consumption_l1' => $data['ac_consumption_l1_power'] ?? null,
    'consumption_out_l1' => $data['ac_consumption_out_l1_power'] ?? null,
    'grid_l1_power' => $data['grid_l1_power'] ?? null,
    'alarm_grid_lost' => $data['alarm_grid_lost'] ?? 0,
    'alarm_high_temp' => $data['alarm_high_temp'] ?? 0,
    'alarm_overload' => $data['alarm_overload'] ?? 0,
];

// Remove null values
$fields = array_filter($fields, function($v) { return $v !== null; });

$tags = [
    'name' => $name,
    'app_id' => $app->app_id,
    'rrd_name' => ['app', $name, $app->app_id],
    'rrd_def' => $rrd_def,
];

app('Datastore')->put($device, 'app', $tags, $fields);
update_application($app, $output, $fields);
