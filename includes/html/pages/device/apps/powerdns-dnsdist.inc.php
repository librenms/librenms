<?php
/*
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @package    LibreNMS
 * @subpackage webui
 * @link       https://www.librenms.org
 * @copyright  2017 LibreNMS
 * @author     Cercel Valentin <crc@nuamchefazi.ro>
*/

$graphs = [
    'powerdns-dnsdist_latency' => 'Latency',
    'powerdns-dnsdist_cache' => 'Cache',
    'powerdns-dnsdist_downstream' => 'Downstream servers',
    'powerdns-dnsdist_dynamic_blocks' => 'Dynamic blocks',
    'powerdns-dnsdist_rules_stats' => 'Rules stats',
    'powerdns-dnsdist_queries_stats' => 'Queries stats',
    'powerdns-dnsdist_queries_latency' => 'Queries latency',
    'powerdns-dnsdist_queries_drop' => 'Queries drop',
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
