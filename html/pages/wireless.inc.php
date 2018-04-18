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

use App\Models\WirelessSensor;

$pagetitle[] = "Wireless";

$valid_wireless_types = WirelessSensor::getTypes(true);

$active_type = $vars['metric'];
if (!$active_type) {
    $active_type = $valid_wireless_types->keys()->first();  // get current type in array (should be the first)
}
if (!$vars['view']) {
    $vars['view'] = "nographs";
}


$link_array = array('page' => 'wireless');

$linkoptions = '<span style="font-weight: bold;">Wireless</span> &#187; ';
$sep = '';
foreach ($valid_wireless_types as $type => $details) {
    $linkoptions .= $sep;
    if ($active_type == $type) {
        $linkoptions .= '<span class="pagemenu-selected">';
    }

    $linkoptions .= generate_link($details['short'], $link_array, array('metric'=> $type, 'view' => $vars['view']));

    if ($active_type == $type) {
        $linkoptions .= '</span>';
    }

    $sep = ' | ';
}
unset($sep);

$displayoptions = '';
if ($vars['view'] == "graphs") {
    $displayoptions .= '<span class="pagemenu-selected">';
}
$displayoptions .= generate_link("Graphs", $link_array, array("metric"=> $active_type, "view" => "graphs"));
if ($vars['view'] == "graphs") {
    $displayoptions .= '</span>';
}

$displayoptions .= ' | ';

if ($vars['view'] != "graphs") {
    $displayoptions .= '<span class="pagemenu-selected">';
}

$displayoptions .= generate_link("No Graphs", $link_array, array("metric"=> $active_type, "view" => "nographs"));

if ($vars['view'] != "graphs") {
    $displayoptions .= '</span>';
}

if (isset($valid_wireless_types[$active_type])) {
    $graph_type = 'wireless_' . $active_type;
    $unit = $valid_wireless_types[$active_type]['unit'];
    $pagetitle[] = "Wireless :: ".$active_type;
    include $config['install_dir'] . '/html/pages/wireless/sensors.inc.php';
} else {
    echo("No sensors of type " . $active_type . " found.");
}
