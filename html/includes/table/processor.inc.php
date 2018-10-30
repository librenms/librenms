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
 * @copyright  2018 LibreNMS
 * @author     LibreNMS Contributors
*/

use LibreNMS\Authentication\LegacyAuth;

$graph_type = 'processor_usage';
$where      = 1;
$sql        = ' FROM `processors` AS `P` LEFT JOIN `devices` AS `D` ON `P`.`device_id` = `D`.`device_id`';
if (!LegacyAuth::user()->hasGlobalRead()) {
    $sql    .= ' LEFT JOIN `devices_perms` AS `DP` ON `P`.`device_id` = `DP`.`device_id`';
    $where  .= ' AND `DP`.`user_id`=?';
    $param[] = LegacyAuth::id();
}

$sql .= " WHERE $where";
if (isset($searchPhrase) && !empty($searchPhrase)) {
    $sql .= " AND (`hostname` LIKE '%$searchPhrase%' OR `processor_descr` LIKE '%$searchPhrase%')";
}

$count_sql = "SELECT COUNT(`processor_id`) $sql";
$total     = dbFetchCell($count_sql, $param);
if (empty($total)) {
    $total = 0;
}

if (!isset($sort) || empty($sort)) {
    $sort = '`D`.`hostname`, `P`.`processor_descr`';
}

$sql .= " ORDER BY $sort";
if (isset($current)) {
    $limit_low  = (($current * $rowCount) - ($rowCount));
    $limit_high = $rowCount;
}

if ($rowCount != -1) {
    $sql .= " LIMIT $limit_low,$limit_high";
}

$sql = "SELECT * $sql";
foreach (dbFetchRows($sql, $param) as $processor) {
    $perc                  = round($processor['processor_usage'], 0);
    $graph_array['type']   = $graph_type;
    $graph_array['id']     = $processor['processor_id'];
    $graph_array['from']   = $config['time']['day'];
    $graph_array['to']     = $config['time']['now'];
    $graph_array['height'] = '20';
    $graph_array['width']  = '80';
    $graph_array_zoom      = $graph_array;
    $graph_array_zoom['height'] = '150';
    $graph_array_zoom['width']  = '400';
    $link       = 'graphs/id='.$graph_array['id'].'/type='.$graph_array['type'].'/from='.$graph_array['from'].'/to='.$graph_array['to'].'/';
    $mini_graph = overlib_link($link, generate_lazy_graph_tag($graph_array), generate_graph_tag($graph_array_zoom), null);
    $background = get_percentage_colours($perc, $processor['processor_perc_warn']);
    $bar_link   = overlib_link($link, print_percentage_bar(400, 20, $perc, $perc.'%', 'ffffff', $background['left'], (100 - $perc).'%', 'ffffff', $background['right']), generate_graph_tag($graph_array_zoom), null);

    $response[] = array(
        'hostname'        => generate_device_link($processor),
        'processor_descr' => $processor['processor_descr'],
        'graph'           => $mini_graph,
        'processor_usage' => $bar_link,
    );
    if ($vars['view'] == 'graphs') {
        $graph_array['height'] = '100';
        $graph_array['width']  = '216';
        $graph_array['to']     = $config['time']['now'];
        $graph_array['id']     = $processor['processor_id'];
        $graph_array['type']   = $graph_type;
        $return_data           = true;
        include 'includes/print-graphrow.inc.php';
        unset($return_data);
        $response[] = array(
            'hostname'        => $graph_data[0],
            'processor_descr' => $graph_data[1],
            'graph'           => $graph_data[2],
            'processor_usage' => $graph_data[3],
        );
    } //end if
}//end foreach

$output = array(
    'current'  => $current,
    'rowCount' => $rowCount,
    'rows'     => $response,
    'total'    => $total,
);
echo _json_encode($output);
