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
 * @link       https://www.librenms.org
 * @copyright  2018 LibreNMS
 * @author     LibreNMS Contributors
*/

use LibreNMS\Util\StringHelpers;

$graph_type = 'toner_usage';

$param = [];
$sql = 'SELECT * FROM `printer_supplies` AS S, `devices` AS D WHERE S.device_id = D.device_id';

if (! empty($searchPhrase)) {
    $sql .= ' AND (`D`.`hostname` LIKE ? OR `supply_descr` LIKE ?)';
    $param[] = "%$searchPhrase%";
    $param[] = "%$searchPhrase%";
}

$count_sql = 'SELECT COUNT(*) FROM `printer_supplies`';
// FIXME not restricted to device access

$count = dbFetchCell($count_sql, $param);
if (empty($count)) {
    $count = 0;
}

if (empty($sort)) {
    $sort = '`D`.`hostname`, `supply_descr`';
} else {
    // toner_used is an alias for toner_perc
    $sort = str_replace('toner_used', 'supply_current', $sort);
}

$sql .= " ORDER BY $sort";

if (isset($current)) {
    $limit_low = (($current * $rowCount) - ($rowCount));
    $limit_high = $rowCount;
}

if ($rowCount != -1) {
    $sql .= " LIMIT $limit_low,$limit_high";
}

foreach (dbFetchRows($sql, $param) as $toner) {
    if (device_permitted($toner['device_id'])) {
        $perc = $toner['supply_current'];
        $type = $toner['supply_type'];

        $graph_array['type'] = $graph_type;
        $graph_array['id'] = $toner['supply_id'];
        $graph_array['from'] = \LibreNMS\Config::get('time.day');
        $graph_array['to'] = \LibreNMS\Config::get('time.now');
        $graph_array['height'] = '20';
        $graph_array['width'] = '80';
        $graph_array_zoom = $graph_array;
        $graph_array_zoom['height'] = '150';
        $graph_array_zoom['width'] = '400';
        $link = 'graphs/id=' . $graph_array['id'] . '/type=' . $graph_array['type'] . '/from=' . $graph_array['from'] . '/to=' . $graph_array['to'] . '/';
        $mini_graph = \LibreNMS\Util\Url::overlibLink($link, \LibreNMS\Util\Url::graphTag($graph_array), \LibreNMS\Util\Url::graphTag($graph_array_zoom));
        $background = \LibreNMS\Util\Colors::percentage(100 - $perc, null);
        $bar_link = print_percentage_bar(400, 20, $perc, "$perc%", 'ffffff', $background['left'], $free, 'ffffff', $background['right']);

        $response[] = [
            'hostname' => generate_device_link($toner),
            'supply_descr' => $toner['supply_descr'],
            'graph' => $mini_graph,
            'toner_used' => $bar_link,
            'supply_type' => StringHelpers::camelToTitle($type == 'opc' ? 'organicPhotoConductor' : $type),
            'supply_current' => $perc . '%',
        ];

        if ($vars['view'] == 'graphs') {
            $graph_array['height'] = '100';
            $graph_array['width'] = '216';
            $graph_array['to'] = \LibreNMS\Config::get('time.now');
            $graph_array['id'] = $toner['supply_id'];
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
        }
    }
}

$output = [
    'current'  => $current,
    'rowCount' => $rowCount,
    'rows'     => $response,
    'total'    => $count,
];
echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
