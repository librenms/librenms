<?php
/*
 * LibreNMS
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @package    LibreNMS
 * @subpackage webui
 * @link       http://librenms.org
 * @copyright  2019 LibreNMS
 * @author     Cercel Valentin <crc@nuamchefazi.ro>
*/

foreach ($graphs as $key => $text) {
    $graph_type = $key;
    $custom_values = [];
    $graph_array = array_merge(apps_default_graphs_value($key, $app['app_id'], $config['time']['now']), $custom_values);

    print_optionbar_start();
    echo "<span class='devices-font-bold'>" . $text . "</span>";
    print_optionbar_end();
    echo '<div class="row">';
    require 'includes/print-graphrow.inc.php';
    echo '</div>';
}
