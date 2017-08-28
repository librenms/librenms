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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

use LibreNMS\Device\WirelessSensor;

$sensors = dbFetchColumn('SELECT `sensor_class` FROM `wireless_sensors` GROUP BY `sensor_class`');
$valid_wireless_types = array_intersect_key(WirelessSensor::getTypes(), array_flip($sensors));

$class = $vars['metric'];
if (!$class) {
    $class = key($valid_wireless_types);  // get current type in array (should be the first)
}
if (!$vars['view']) {
    $vars['view'] = "nographs";
}

$link_array = array('page' => 'wireless');

$pagetitle[] = "Wireless";

print_optionbar_start('', '');

echo('<span style="font-weight: bold;">Wireless</span> &#187; ');

$sep = '';
foreach ($valid_wireless_types as $type => $details) {
    echo($sep);
    if ($class == $type) {
        echo("<span class='pagemenu-selected'>");
    }

    echo(generate_link($details['short'], $link_array, array('metric'=> $type, 'view' => $vars['view'])));

    if ($class == $type) {
        echo("</span>");
    }

    $sep = ' | ';
}

unset($sep);

echo('<div style="float: right;">');

if ($vars['view'] == "graphs") {
    echo('<span class="pagemenu-selected">');
}
echo(generate_link("Graphs", $link_array, array('metric'=> $class, 'view' => "graphs")));
if ($vars['view'] == "graphs") {
    echo('</span>');
}

echo(' | ');

if ($vars['view'] != "graphs") {
    echo('<span class="pagemenu-selected">');
}

echo(generate_link("No Graphs", $link_array, array('metric'=> $class, 'view' => "nographs")));

if ($vars['view'] != "graphs") {
    echo('</span>');
}

echo('</div>');

print_optionbar_end();

if (isset($valid_wireless_types[$class])) {
    $graph_type = 'wireless_' . $class;
    $unit = $valid_wireless_types[$class]['unit'];

    include $config['install_dir'] . '/html/pages/wireless/sensors.inc.php';
} else {
    echo("No sensors of type " . $class . " found.");
}
