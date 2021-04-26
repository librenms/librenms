<?php
/*
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @package    LibreNMS
 * @subpackage pi-hole
 * @link       https://www.librenms.org
 * @copyright  2017 LibreNMS
 * @author     crcro <crc@nuamchefazi.ro>
*/

$graphs = [
    'pi-hole_query_types' => 'Query Types',
    'pi-hole_destinations' => 'Destinations',
    'pi-hole_query_results' => 'Query Results',
    'pi-hole_block_percent' => 'Block Percentage',
    'pi-hole_blocklist' => 'Blocklist Domains',
];

foreach ($graphs as $key => $text) {
    $graph_type = $key;
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = \LibreNMS\Config::get('time.now');
    $graph_array['id'] = $app['app_id'];
    $graph_array['type'] = 'application_' . $key;

    echo '<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">' . $text . '</h3>
    </div>
    <div class="panel-body">
    <div class="row">';
    include 'includes/html/print-graphrow.inc.php';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}
