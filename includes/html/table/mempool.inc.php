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

$graph_type = 'mempool_usage';
$where = 1;
$sql = ' FROM `mempools` AS `M` LEFT JOIN `devices` AS `D` ON `M`.`device_id` = `D`.`device_id`';
$param = [];

if (! Auth::user()->hasGlobalRead()) {
    $device_ids = Permissions::devicesForUser()->toArray() ?: [0];
    $where .= ' AND `D`.`device_id` IN ' . dbGenPlaceholders(count($device_ids));
    $param = array_merge($param, $device_ids);
}

$sql .= " WHERE $where";
if (isset($searchPhrase) && ! empty($searchPhrase)) {
    $sql .= ' AND (`hostname` LIKE ? OR `mempool_descr` LIKE ?)';
    $param[] = "%$searchPhrase%";
    $param[] = "%$searchPhrase%";
}

$count_sql = "SELECT COUNT(`mempool_id`) $sql";
$count = dbFetchCell($count_sql, $param);
if (empty($count)) {
    $count = 0;
}

if (! isset($sort) || empty($sort)) {
    $sort = '`D`.`hostname`, `M`.`mempool_descr`';
}

$sql .= " ORDER BY $sort";
if (isset($current)) {
    $limit_low = (($current * $rowCount) - ($rowCount));
    $limit_high = $rowCount;
}

if ($rowCount != -1) {
    $sql .= " LIMIT $limit_low,$limit_high";
}

$sql = "SELECT * $sql";
foreach (dbFetchRows($sql, $param) as $mempool) {
    $perc = round($mempool['mempool_perc'], 0);
    $total = formatStorage($mempool['mempool_total']);
    $free = formatStorage($mempool['mempool_free']);
    $used = formatStorage($mempool['mempool_used']);
    $graph_array['type'] = $graph_type;
    $graph_array['id'] = $mempool['mempool_id'];
    $graph_array['from'] = \LibreNMS\Config::get('time.day');
    $graph_array['to'] = \LibreNMS\Config::get('time.now');
    $graph_array['height'] = '20';
    $graph_array['width'] = '80';
    $graph_array_zoom = $graph_array;
    $graph_array_zoom['height'] = '150';
    $graph_array_zoom['width'] = '400';
    $link = 'graphs/id=' . $graph_array['id'] . '/type=' . $graph_array['type'] . '/from=' . $graph_array['from'] . '/to=' . $graph_array['to'] . '/';
    $mini_graph = overlib_link($link, generate_lazy_graph_tag($graph_array), generate_graph_tag($graph_array_zoom), null);
    $background = get_percentage_colours($perc, $mempool['mempool_perc_warn']);
    $bar_link = overlib_link($link, print_percentage_bar(400, 20, $perc, "$used / $total", 'ffffff', $background['left'], $free, 'ffffff', $background['right']), generate_graph_tag($graph_array_zoom), null);

    $response[] = [
        'hostname'      => generate_device_link($mempool),
        'mempool_descr' => $mempool['mempool_descr'],
        'graph'         => $mini_graph,
        'mempool_used'  => $bar_link,
        'mempool_perc'  => $perc . '%',
    ];
    if ($vars['view'] == 'graphs') {
        $graph_array['height'] = '100';
        $graph_array['width'] = '216';
        $graph_array['to'] = \LibreNMS\Config::get('time.now');
        $graph_array['id'] = $mempool['mempool_id'];
        $graph_array['type'] = $graph_type;
        $return_data = true;
        include 'includes/html/print-graphrow.inc.php';
        unset($return_data);
        $response[] = [
            'hostname'      => $graph_data[0],
            'mempool_descr' => $graph_data[1],
            'graph'         => $graph_data[2],
            'mempool_used'  => $graph_data[3],
            'mempool_perc'  => '',
        ];
    } //end if
}//end foreach

$output = [
    'current'  => $current,
    'rowCount' => $rowCount,
    'rows'     => $response,
    'total'    => $count,
];
echo _json_encode($output);
