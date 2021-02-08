<?php
/**
 * wireless.inc.php
 *
 * Wireless Sensors table view
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
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */
$pagetitle[] = 'Wireless';

use LibreNMS\Device\WirelessSensor;

$sensors = dbFetchColumn('SELECT `sensor_class` FROM `wireless_sensors` GROUP BY `sensor_class`');
$valid_wireless_types = array_intersect_key(WirelessSensor::getTypes(), array_flip($sensors));

$class = basename($vars['metric'] ?? key($valid_wireless_types));
$vars['view'] = basename($vars['view'] ?? 'nographs');

$link_array = ['page' => 'wireless'];

$linkoptions = '<span style="font-weight: bold;">Wireless</span> &#187; ';
$sep = '';
foreach ($valid_wireless_types as $type => $details) {
    $linkoptions .= $sep;
    if ($class == $type) {
        $linkoptions .= '<span class="pagemenu-selected">';
    }

    $linkoptions .= generate_link(__("wireless.$type.short"), $link_array, ['metric'=> $type, 'view' => $vars['view']]);

    if ($class == $type) {
        $linkoptions .= '</span>';
    }

    $sep = ' | ';
}
unset($sep);

$displayoptions = '';
if ($vars['view'] == 'graphs') {
    $displayoptions .= '<span class="pagemenu-selected">';
}
$displayoptions .= generate_link('Graphs', $link_array, ['metric'=> $class, 'view' => 'graphs']);
if ($vars['view'] == 'graphs') {
    $displayoptions .= '</span>';
}

$displayoptions .= ' | ';

if ($vars['view'] != 'graphs') {
    $displayoptions .= '<span class="pagemenu-selected">';
}

$displayoptions .= generate_link('No Graphs', $link_array, ['metric'=> $class, 'view' => 'nographs']);

if ($vars['view'] != 'graphs') {
    $displayoptions .= '</span>';
}

if (isset($valid_wireless_types[$class])) {
    $graph_type = 'wireless_' . $class;
    $unit = __("wireless.$class.unit");
    $pagetitle[] = 'Wireless :: ' . $class;
    include \LibreNMS\Config::get('install_dir') . '/includes/html/pages/wireless/sensors.inc.php';
} else {
    echo 'No sensors of type ' . $class . ' found.';
}
