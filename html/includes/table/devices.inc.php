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
 * @link       http://librenms.org
 * @copyright  2018 LibreNMS
 * @author     LibreNMS Contributors
*/

use LibreNMS\Authentication\LegacyAuth;

$where = 1;
$param = array();

$sql = ' FROM `devices` LEFT JOIN locations ON devices.location_id = locations.id';

if (!LegacyAuth::user()->hasGlobalRead()) {
    $sql .= ' LEFT JOIN `devices_perms` AS `DP` ON `devices`.`device_id` = `DP`.`device_id`';
    $where .= ' AND `DP`.`user_id`=?';
    $param[] = LegacyAuth::id();
}

if (!empty($vars['group']) && is_numeric($vars['group'])) {
    $sql .= " LEFT JOIN `device_group_device` AS `DG` ON `DG`.`device_id`=`devices`.`device_id`";
    $where .= " AND `DG`.`device_group_id`=?";
    $param[] = $vars['group'];
}

$sql .= " WHERE $where ";

if (!empty($vars['searchquery'])) {
    $sql .= ' AND (sysName LIKE ? OR hostname LIKE ? OR hardware LIKE ? OR os LIKE ? OR location LIKE ?)';
    $param += array_fill(count($param), 5, '%' . $vars['searchquery'] . '%');
}

if (!empty($vars['os'])) {
    $sql .= ' AND os = ?';
    $param[] = $vars['os'];
}

if (!empty($vars['version'])) {
    $sql .= ' AND version = ?';
    $param[] = $vars['version'];
}

if (!empty($vars['hardware'])) {
    $sql .= ' AND hardware = ?';
    $param[] = $vars['hardware'];
}

if (!empty($vars['features'])) {
    $sql .= ' AND features = ?';
    $param[] = $vars['features'];
}

if (!empty($vars['type'])) {
    if ($vars['type'] == 'generic') {
        $sql .= " AND ( type = ? OR type = '')";
        $param[] = $vars['type'];
    } else {
        $sql .= ' AND type = ?';
        $param[] = $vars['type'];
    }
}

if (isset($vars['state']) && $vars['state'] !== "") {
    $sql .= ' AND status= ?';
    if (is_numeric($vars['state'])) {
        $param[] = $vars['state'];
    } else {
        $param[] = str_replace(array('up', 'down'), array(1, 0), $vars['state']);
    }
}

if (!empty($vars['disabled'])) {
    $sql .= ' AND disabled= ?';
    $param[] = $vars['disabled'];
}

if (!empty($vars['ignore'])) {
    $sql .= ' AND `ignore`= ?';
    $param[] = $vars['ignore'];
}

if (!empty($vars['location']) && $vars['location'] == 'Unset') {
    $location_filter = '';
}

if (!empty($vars['location'])) {
    $sql .= " AND (`location` = ? OR `location_id` = ?)";
    $param[] = $vars['location'];
    $param[] = $vars['location'];
}

$count_sql = "SELECT COUNT(`devices`.`device_id`) $sql";

$total = (int)dbFetchCell($count_sql, $param);

if (!isset($sort) || empty($sort)) {
    $sort = '`hostname` DESC';
}

$sql .= " ORDER BY $sort";

if (isset($current)) {
    $limit_low = (($current * $rowCount) - ($rowCount));
    $limit_high = $rowCount;
}

if ($rowCount != -1) {
    $sql .= " LIMIT $limit_low,$limit_high";
}

$sql = "SELECT DISTINCT(`devices`.`device_id`),`devices`.*,locations.location $sql";

if (!isset($vars['format'])) {
    $vars['format'] = 'list_detail';
}

list($format, $subformat) = explode('_', $vars['format']);

foreach (dbFetchRows($sql, $param) as $device) {
    if (isset($bg) && $bg == $config['list_colour']['odd']) {
        $bg = $config['list_colour']['even'];
    } else {
        $bg = $config['list_colour']['odd'];
    }

    if ($device['status'] == '0') {
        $extra = 'label-danger';
    } else {
        $extra = 'label-success';
    }

    if ($device['ignore'] == '1') {
        $extra = 'label-default';
        if ($device['status'] == '1') {
            $extra = 'label-warning';
        }
    }

    if ($device['disabled'] == '1') {
        $extra = 'label-default';
    }

    $type = strtolower($device['os']);

    $image = getIconTag($device);

    if ($device['os'] == 'ios') {
        formatCiscoHardware($device, true);
    }

    $device['os_text'] = $config['os'][$device['os']]['text'];
    $port_count = dbFetchCell('SELECT COUNT(*) FROM `ports` WHERE `device_id` = ?', array($device['device_id']));
    $sensor_count = dbFetchCell('SELECT COUNT(*) FROM `sensors` WHERE `device_id` = ?', array($device['device_id']));
    $wireless_count = dbFetchCell('SELECT COUNT(*) FROM `wireless_sensors` WHERE `device_id` = ?', array($device['device_id']));

    $actions = '
        <div class="container-fluid">
            <div class="row">
                <div class="col-xs-1"><a href="' . generate_device_url($device) . '"> <i class="fa fa-id-card fa-lg icon-theme" title="View device"></i></a></div>
                <div class="col-xs-1"><a href="' . generate_device_url($device, array('tab' => 'alerts')) . '"> <i class="fa fa-exclamation-circle fa-lg icon-theme" title="View alerts"></i></a></div>
    ';

    if (LegacyAuth::user()->hasGlobalAdmin()) {
        $actions .= '<div class="col-xs-1"><a href="' . generate_device_url($device, array('tab' => 'edit')) . '"> <i class="fa fa-pencil fa-lg icon-theme" title="Edit device"></i></a></div>';
    }

    if ($subformat == 'detail') {
        $actions .= '</div><div class="row">';
    }


        $actions .= '
                    <div class="col-xs-1"><a href="telnet://' . $device['hostname'] . '"><i class="fa fa-terminal fa-lg icon-theme" title="Telnet to ' . $device['hostname'] . '"></i></a></div>
                    ';
    if (isset($config['gateone']['server'])) {
        if ($config['gateone']['use_librenms_user'] == true) {
                    $actions .= '<div class="col-xs-1"><a href="' . $config['gateone']['server'] . '?ssh=ssh://' . LegacyAuth::user()->username . '@' . $device['hostname'] . '&location=' . $device['hostname'] .'" target="_blank" rel="noopener"><i class="fa fa-lock fa-lg icon-theme" title="SSH to ' . $device['hostname'] . '"></i></a></div>';
        } else {
                    $actions .= '<div class="col-xs-1"><a href="' . $config['gateone']['server'] . '?ssh=ssh://' . $device['hostname'] . '&location=' . $device['hostname'] .'" target="_blank" rel="noopener"><i class="fa fa-lock fa-lg icon-theme" title="SSH to ' . $device['hostname'] . '"></i></a></div>';
        }
    } else {
        $actions .= '<div class="col-xs-1"><a href="ssh://' . $device['hostname'] . '"><i class="fa fa-lock fa-lg icon-theme" title="SSH to ' . $device['hostname'] . '"></i></a></div>
        ';
    }
        $actions .= '<div class="col-xs-1"><a href="https://' . $device['hostname'] . '" target="_blank" rel="noopener"><i class="fa fa-globe fa-lg icon-theme" title="Launch browser https://' . $device['hostname'] . '"></i></a></div>
                </div>
            </div>
        ';

    $hostname = generate_device_link($device);

    if (extension_loaded('mbstring')) {
        $location = mb_substr($device['location'], 0, 32, 'utf8');
    } else {
        $location = substr($device['location'], 0, 32);
    }

    if ($subformat == 'detail') {
        $platform = $device['hardware'];
        $os = $device['os_text'] . '<br>' . $device['version'] . (empty($device['features'])? "" : " ({$device['features']})");
        $device['ip'] = inet6_ntop($device['ip']);
        $uptime = formatUptime($device['uptime'], 'short');
        $hostname .= '<br />' . get_device_name($device);

        $metrics = array();
        if ($port_count) {
            $port_widget = '<a href="' . generate_device_url($device, array('tab' => 'ports')) . '">';
            $port_widget .= '<span><i class="fa fa-link fa-lg icon-theme"></i> ' . $port_count;
            $port_widget .= '</span></a> ';
            $metrics[] = $port_widget;
        }

        if ($sensor_count) {
            $sensor_widget = '<a href="' . generate_device_url($device, array('tab' => 'health')) . '">';
            $sensor_widget .= '<span><i class="fa fa-dashboard fa-lg icon-theme"></i> ' . $sensor_count;
            $sensor_widget .= '</span></a> ';
            $metrics[] = $sensor_widget;
        }

        if ($wireless_count) {
            $wireless_widget = '<a href="' . generate_device_url($device, array('tab' => 'wireless')) . '">';
            $wireless_widget .= '<span><i class="fa fa-wifi fa-lg icon-theme"></i> ' . $wireless_count;
            $wireless_widget .= '</span></a> ';
            $metrics[] = $wireless_widget;
        }

        $col_port = '<div class="device-table-metrics">';
        $col_port .= implode(count($metrics) == 2 ? '<br />' : '', $metrics);
        $col_port .= '</div>';
    } else {
        $platform = $device['hardware'];
        $os = $device['os_text'] . ' ' . $device['version'];
        $uptime = formatUptime($device['uptime'], 'short');
        $col_port = '';
    }

    $response[] = array(
        'extra' => $extra,
        'list_type' => $subformat,
        'icon' => $image,
        'hostname' => $hostname,
        'ports' => $col_port,
        'hardware' => $platform,
        'os' => $os,
        'uptime' => $uptime,
        'location' => $location,
        'actions' => $actions,
    );
}//end foreach

$output = array(
    'current' => $current,
    'rowCount' => $rowCount,
    'rows' => $response,
    'total' => $total,
);
echo _json_encode($output);
