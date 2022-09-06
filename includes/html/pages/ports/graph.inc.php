<?php

echo '<div class="panel panel-default panel-condensed">';
echo '<div class="panel-heading">';
echo $displayLists;
echo '</div>';
echo '<div class="panel-body">';
echo '<div style="padding-bottom: 10px;">';
echo stripcslashes($output);
echo '</div>';

$param = [];
$where = '';
$ignore_filter = 0;
$disabled_filter = 0;

foreach ($vars as $var => $value) {
    if ($value != '') {
        switch ($var) {
            case 'hostname':
                $where .= ' AND D.hostname LIKE ?';
                $param[] = '%' . $value . '%';
                break;
            case 'location':
                if (is_int($value)) {
                    $where .= ' AND L.id = ?';
                    $param[] = $value;
                } else {
                    $where .= ' AND L.location LIKE ?';
                    $param[] = '%' . $value . '%';
                }
                break;
            case 'device_id':
                $where .= ' AND D.device_id = ?';
                $param[] = $value;
                break;
            case 'deleted':
                if ($value == 1 || $value == 'yes') {
                    $where .= ' AND `I`.`deleted` = 1';
                    $ignore_filter = 1;
                }
                break;
            case 'ignore':
                if ($value == 1 || $value == 'yes') {
                    $where .= ' AND (I.ignore = 1 OR D.ignore = 1) AND I.deleted = 0';
                    $ignore_filter = 1;
                }
                break;
            case 'disabled':
                if ($value == 1 || $value == 'yes') {
                    $where .= ' AND `I`.`disabled` = 1 AND `I`.`deleted` = 0';
                    $disabled_filter = 1;
                }
                break;
            case 'ifSpeed':
                if (is_numeric($value)) {
                    $where .= " AND I.$var = ?";
                    $param[] = $value;
                }
                break;
            case 'ifType':
                $where .= " AND I.$var = ?";
                $param[] = $value;
                break;
            case 'ifAlias':
            case 'port_descr_type':
                $where .= " AND I.$var LIKE ?";
                $param[] = '%' . $value . '%';
                break;
            case 'errors':
                if ($value == 1 || $value == 'yes') {
                    $where .= " AND (I.`ifInErrors_delta` > '0' OR I.`ifOutErrors_delta` > '0')";
                }
                break;
            case 'state':
                if ($value == 'down') {
                    $where .= ' AND I.ifAdminStatus = ? AND I.ifOperStatus = ?';
                    $param[] = 'up';
                    $param[] = 'down';
                } elseif ($value == 'up') {
                    $where .= ' AND I.ifAdminStatus = ? AND I.ifOperStatus = ?';
                    $param[] = 'up';
                    $param[] = 'up';
                } elseif ($value == 'admindown') {
                    $where .= ' AND I.ifAdminStatus = ? AND D.ignore = 0';
                    $param[] = 'down';
                }
                break;
            case 'group':
                $where .= ' AND port_id IN (SELECT `port_id` FROM `port_group_port` WHERE `port_group_id` = ?)';
                $param[] = $vars['group'];
                break;
        }
    }
}

if ($ignore_filter == 0 && $disabled_filter == 0) {
    $where .= ' AND `I`.`ignore` = 0 AND `I`.`disabled` = 0 AND `I`.`deleted` = 0';
}

$query = 'SELECT * FROM `ports` AS I, `devices` AS D LEFT JOIN `locations` AS L ON D.location_id = L.id WHERE I.device_id = D.device_id' . $where;

// only grab list of ports for graph pages, table uses ajax
$ports = array_map(function ($value) {
    return (array) $value;
}, DB::select($query, $param));

switch ($vars['sort'] ?? '') {
    case 'traffic':
        $ports = array_sort_by_column($ports, 'ifOctets_rate', SORT_DESC);
        break;
    case 'traffic_in':
        $ports = array_sort_by_column($ports, 'ifInOctets_rate', SORT_DESC);
        break;
    case 'traffic_out':
        $ports = array_sort_by_column($ports, 'ifOutOctets_rate', SORT_DESC);
        break;
    case 'packets':
        $ports = array_sort_by_column($ports, 'ifUcastPkts_rate', SORT_DESC);
        break;
    case 'packets_in':
        $ports = array_sort_by_column($ports, 'ifInUcastOctets_rate', SORT_DESC);
        break;
    case 'packets_out':
        $ports = array_sort_by_column($ports, 'ifOutUcastOctets_rate', SORT_DESC);
        break;
    case 'errors':
        $ports = array_sort_by_column($ports, 'ifErrors_rate', SORT_DESC);
        break;
    case 'speed':
        $ports = array_sort_by_column($ports, 'ifSpeed', SORT_DESC);
        break;
    case 'port':
        $ports = array_sort_by_column($ports, 'ifDescr', SORT_ASC);
        break;
    case 'media':
        $ports = array_sort_by_column($ports, 'ifType', SORT_ASC);
        break;
    case 'descr':
        $ports = array_sort_by_column($ports, 'ifAlias', SORT_ASC);
        break;
    case 'device':
    default:
        $ports = array_sort_by_column($ports, 'hostname', SORT_ASC);
}

foreach ($ports as $port) {
    $speed = \LibreNMS\Util\Number::formatSi($port['ifSpeed'], 2, 3, 'bps');
    $type = \LibreNMS\Util\Rewrite::normalizeIfType($port['ifType']);

    $port['in_rate'] = \LibreNMS\Util\Number::formatSi(($port['ifInOctets_rate'] * 8), 2, 3, 'bps');
    $port['out_rate'] = \LibreNMS\Util\Number::formatSi(($port['ifOutOctets_rate'] * 8), 2, 3, 'bps');

    if ($port['ifInErrors_delta'] > 0 || $port['ifOutErrors_delta'] > 0) {
        $error_img = generate_port_link($port, "<i class='fa fa-flag fa-lg' style='color:red' aria-hidden='true'></i>", 'errors');
    } else {
        $error_img = '';
    }

    if (port_permitted($port['port_id'], $port['device_id'])) {
        $port = cleanPort($port, $device ?? null);

        $graph_type = 'port_' . $subformat;

        if (session('widescreen')) {
            $width = 357;
        } else {
            $width = 315;
        }

        if (session('widescreen')) {
            $width_div = 438;
        } else {
            $width_div = 393;
        }

        $graph_array = [];
        $graph_array['height'] = 100;
        $graph_array['width'] = 210;
        $graph_array['to'] = \LibreNMS\Config::get('time.now');
        $graph_array['id'] = $port['port_id'];
        $graph_array['type'] = $graph_type;
        $graph_array['from'] = \LibreNMS\Config::get('time.day');
        $graph_array['legend'] = 'no';

        $link_array = $graph_array;
        $link_array['page'] = 'graphs';
        unset($link_array['height'], $link_array['width'], $link_array['legend']);
        $link = \LibreNMS\Util\Url::generate($link_array);
        $overlib_content = generate_overlib_content($graph_array, $port['hostname'] . ' - ' . $port['label']);
        $graph_array['title'] = 'yes';
        $graph_array['width'] = $width;
        $graph_array['height'] = 119;
        $graph = \LibreNMS\Util\Url::lazyGraphTag($graph_array);

        echo "<div class='graph-all-common' style='min-width: " . $width_div . 'px;max-width:' . $width_div . "px;'>";
        echo \LibreNMS\Util\Url::overlibLink($link, $graph, $overlib_content);
        echo '</div>';
    }
}

echo '</div>';
