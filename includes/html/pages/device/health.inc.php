<?php
/**
 * includes/html/pages/device/health.inc.php
 *
 * piece of code responssible for display health information on device page
 *
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
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2022 Peca Nesovanovic
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 */

use App\Facades\DeviceCache;
use App\Models\DiskIo;
use App\Models\Mempool;
use App\Models\Processor;
use App\Models\Sensor;
use App\Models\Storage;
use LibreNMS\Component;
use LibreNMS\Enum\SensorClass;

/*
# QFP count for cisco devices
*/

$qfp = 0;
if ($device['os_group'] == 'cisco') {
    $component = new Component();
    $components = $component->getComponents($device['device_id'], ['type' => 'cisco-qfp']);
    $components = $components[$device['device_id']];
    $qfp = isset($components) ? count($components) : 0;
}

unset($datas);
$datas[] = 'overview';

if (Processor::where('device_id', $device['device_id'])->count()) {
    $datas[] = 'processor';
}

if ($qfp) {
    $datas[] = 'qfp';
}

if (Mempool::where('device_id', $device['device_id'])->count()) {
    $datas[] = 'mempool';
}

if (Storage::where('device_id', $device['device_id'])->count()) {
    $datas[] = 'storage';
}

if (DiskIo::where('device_id', $device['device_id'])->count()) {
    $datas[] = 'diskio';
}
// TODO:  The filling of two arrays are ugly but works for the moment
foreach (DeviceCache::getPrimary()->sensors()->distinct()->select('sensor_class')->get() as $sensor) {
    $datas[] = $sensor->sensor_class;
    $type_text[$sensor->sensor_class] = $sensor->classDescr($sensor_class);
}

$type_text['overview'] = __('Overview');
$type_text['qfp'] = __('QFP');
$type_text['processor'] = __('Processor');
$type_text['mempool'] = __('Memory');
$type_text['storage'] = __('Disk Usage');
$type_text['diskio'] = __('Disk I/O');

$link_array = [
    'page' => 'device',
    'device' => $device['device_id'],
    'tab' => 'health',
];

print_optionbar_start();

echo '<span style="font-weight: bold;">' . __('Health') . '</span> &#187; ';

if (empty($vars['metric'])) {
    $vars['metric'] = 'overview';
}

$sep = '';
foreach ($datas as $type) {
    echo $sep;
    if ($vars['metric'] == $type) {
        echo '<span class="pagemenu-selected">';
    }

    echo generate_link($type_text[$type], $link_array, ['metric' => $type]);
    if ($vars['metric'] == $type) {
        echo '</span>';
    }

    $sep = ' | ';
}

print_optionbar_end();

$metric = basename($vars['metric']);
if (SensorClass::all()->contains($metric)) {
    $class = $metric;
    $unit = SensorClass::unit($metric);
    $graph_type = 'sensor_' . $metric;
    include 'includes/html/pages/device/health/sensors.inc.php';
} elseif (is_file('includes/html/pages/device/health/' . $metric . '.inc.php')) {
    include 'includes/html/pages/device/health/' . $metric . '.inc.php';
} else {
    foreach ($datas as $type) {
        if ($type != 'overview') {
            $graph_title = $type_text[$type];
            $graph_array['type'] = 'device_' . $type;
            include 'includes/html/print-device-graph.php';
        }
    }
}

$pagetitle[] = 'Health';
