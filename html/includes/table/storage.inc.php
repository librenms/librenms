<?php

$sql = " FROM `storage` AS `S`, `devices` AS `D` WHERE `S`.`device_id` = `D`.`device_id`";

$count_sql = "SELECT COUNT(`storage_id`) $sql";

$total = dbFetchCell($count_sql,$param);
if (empty($total)) {
    $total = 0;
}

if (!isset($sort) || empty($sort)) {
    $sort = '`D`.`hostname`, `S`.`storage_descr`';
}

$sql .= " ORDER BY $sort";

if (isset($current)) {
    $limit_low = ($current * $rowCount) - ($rowCount);
    $limit_high = $rowCount;
}

if ($rowCount != -1) {
    $sql .= " LIMIT $limit_low,$limit_high";
}

$sql = "SELECT * $sql";

system("echo '$sql' >> /tmp/testing");
foreach (dbFetchRows($sql,$param) as $drive) {
    if (device_permitted($drive['device_id'])) {
        $skipdrive = 0;

        if ($drive["os"] == "junos") {
            foreach ($config['ignore_junos_os_drives'] as $jdrive) {
                if (preg_match($jdrive, $drive["storage_descr"])) {
                    $skipdrive = 1;
                }
            }
            $drive["storage_descr"] = preg_replace("/.*mounted on: (.*)/", "\\1", $drive["storage_descr"]);
        }

        if ($drive['os'] == "freebsd") {
            foreach ($config['ignore_bsd_os_drives'] as $jdrive) {
                if (preg_match($jdrive, $drive["storage_descr"])) {
                    $skipdrive = 1;
                }
            }
        }

        if ($skipdrive) {
            continue;
        }

        $perc  = round($drive['storage_perc'], 0);
        $total = formatStorage($drive['storage_size']);
        $free = formatStorage($drive['storage_free']);
        $used = formatStorage($drive['storage_used']);

        $graph_array['type']        = $graph_type;
        $graph_array['id']          = $drive['storage_id'];
        $graph_array['from']        = $config['time']['day'];
        $graph_array['to']          = $config['time']['now'];
        $graph_array['height']      = "20";
        $graph_array['width']       = "80";
        $graph_array_zoom           = $graph_array;
        $graph_array_zoom['height'] = "150";
        $graph_array_zoom['width']  = "400";
        $link = "graphs/id=" . $graph_array['id'] . "/type=" . $graph_array['type'] . "/from=" . $graph_array['from'] . "/to=" . $graph_array['to'] . "/";
        $mini_graph = overlib_link($link, generate_graph_tag($graph_array), generate_graph_tag($graph_array_zoom), NULL);
        $bar_link = overlib_link($link, print_percentage_bar (400, 20, $perc, "$used / $total", "ffffff", $background['left'], $free, "ffffff", $background['right']), generate_graph_tag($graph_array_zoom), NULL);

        $background = get_percentage_colours($perc);

        $response[] = array('device_id' => generate_device_link($drive),
                            'storage_descr' => $drive['storage_descr'],
                            'graph' => $mini_graph,
                            'usage' => $bar_link,
                            'storage_used' => $perc . "%");
        if ($_POST['view'] == "graphs") {
            system("echo 'test' >> /tmp/testing");
            $graph_array['height'] = "100";
            $graph_array['width']  = "216";
            $graph_array['to']     = $config['time']['now'];
            $graph_array['id']     = $drive['storage_id'];
            $graph_array['type']   = $graph_type;

            $return_data = true;
            include("includes/print-graphrow.inc.php");
            unset($return_data);
            $response[] = array('device_id' => $graph_data[0],
                                'storage_descr' => $graph_data[1],
                                'graph' => $graph_data[2],
                                'usage' => $graph_data[3],
                                'storage_used' => '');

        } # endif graphs
    }
}

$output = array('current'=>$current,'rowCount'=>$rowCount,'rows'=>$response,'total'=>$total);
echo _json_encode($output);