<?php

$graph_type = 'storage_usage';

$where = 1;

$sql = ' FROM `storage` AS `S` LEFT JOIN `devices` AS `D` ON `S`.`device_id` = `D`.`device_id`';

if (is_admin() === false && is_read() === false) {
    $sql    .= ' LEFT JOIN `devices_perms` AS `DP` ON `S`.`device_id` = `DP`.`device_id`';
    $where  .= ' AND `DP`.`user_id`=?';
    $param[] = $_SESSION['user_id'];
}

$sql .= " WHERE $where";

if (isset($searchPhrase) && !empty($searchPhrase)) {
    $sql .= " AND (`hostname` LIKE '%$searchPhrase%' OR `storage_descr` LIKE '%$searchPhrase%')";
}

$count_sql = "SELECT COUNT(`storage_id`) $sql";

$count = dbFetchCell($count_sql, $param);
if (empty($count)) {
    $count = 0;
}

if (!isset($sort) || empty($sort)) {
    $sort = '`D`.`hostname`, `S`.`storage_descr`';
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

foreach (dbFetchRows($sql, $param) as $drive) {
    $perc  = round($drive['storage_perc'], 0);
    $total = formatStorage($drive['storage_size']);
    $free  = formatStorage($drive['storage_free']);
    $used  = formatStorage($drive['storage_used']);

    $graph_array['type']        = $graph_type;
    $graph_array['id']          = $drive['storage_id'];
    $graph_array['from']        = $config['time']['day'];
    $graph_array['to']          = $config['time']['now'];
    $graph_array['height']      = '20';
    $graph_array['width']       = '80';
    $graph_array_zoom           = $graph_array;
    $graph_array_zoom['height'] = '150';
    $graph_array_zoom['width']  = '400';
    $link       = 'graphs/id='.$graph_array['id'].'/type='.$graph_array['type'].'/from='.$graph_array['from'].'/to='.$graph_array['to'].'/';
    $mini_graph = overlib_link($link, generate_lazy_graph_tag($graph_array), generate_graph_tag($graph_array_zoom), null);
    $background = get_percentage_colours($perc);
    $bar_link   = overlib_link($link, print_percentage_bar(400, 20, $perc, "$used / $total", 'ffffff', $background['left'], $free, 'ffffff', $background['right']), generate_graph_tag($graph_array_zoom), null);

    $response[] = array(
        'hostname'      => generate_device_link($drive),
        'storage_descr' => $drive['storage_descr'],
        'graph'         => $mini_graph,
        'storage_used'  => $bar_link,
        'storage_perc'  => $perc.'%',
    );
    if ($_POST['view'] == 'graphs') {
        $graph_array['height'] = '100';
        $graph_array['width']  = '216';
        $graph_array['to']     = $config['time']['now'];
        $graph_array['id']     = $drive['storage_id'];
        $graph_array['type']   = $graph_type;

        $return_data = true;
        include 'includes/print-graphrow.inc.php';
        unset($return_data);
        $response[] = array(
            'hostname'      => $graph_data[0],
            'storage_descr' => $graph_data[1],
            'graph'         => $graph_data[2],
            'storage_used'  => $graph_data[3],
            'storage_perc'  => '',
        );
    } //end if
}//end foreach

$output = array(
    'current'  => $current,
    'rowCount' => $rowCount,
    'rows'     => $response,
    'total'    => $count,
);
echo _json_encode($output);
