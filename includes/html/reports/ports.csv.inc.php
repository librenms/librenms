<?php

$param = [];

if (! isset($vars['ignore'])) {
    $vars['ignore'] = 0;
}

if (! isset($vars['disabled'])) {
    $vars['disabled'] = 0;
}

if (! isset($vars['deleted'])) {
    $vars['deleted'] = 0;
}

$where = '';

foreach ($vars as $var => $value) {
    $value = trim($value);
    if ($value != '') {
        switch ($var) {
            case 'hostname':
                $where .= ' AND D.hostname LIKE ?';
                $param[] = '%' . $value . '%';
                break;

            case 'location':
                $where .= ' AND D.location LIKE ?';
                $param[] = '%' . $value . '%';
                break;

            case 'device_id':
                $where .= ' AND D.device_id = ?';
                $param[] = $value;
                break;

            case 'deleted':
                if ($value == 1 || $value == 'yes') {
                    $where .= ' AND I.deleted = 1';
                }
                break;

            case 'disabled':
                if ($value == 1 || $value == 'yes') {
                    $where .= ' AND I.disabled = 1';
                }
                break;

            case 'ignore':
                if ($value == 1 || $value == 'yes') {
                    $where .= ' AND (I.ignore = 1 OR D.ignore = 1)';
                }
                break;

            case 'disable':
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
                if ($value == 1 || $value = 'yes') {
                    $where .= " AND (I.`ifInErrors_delta` > '0' OR I.`ifOutErrors_delta` > '0')";
                }
                break;

            case 'state':
                if ($value == 'down') {
                    $where .= ' AND I.ifAdminStatus = ? AND I.ifOperStatus = ?';
                    $param[] = 'up';
                    $param[] = 'down';
                } elseif ($value == 'up') {
                    $where .= ' AND I.ifAdminStatus = ? AND I.ifOperStatus = ?  AND I.ignore = 0 AND D.ignore = 0 AND I.deleted = 0';
                    $param[] = 'up';
                    $param[] = 'up';
                } elseif ($value == 'admindown') {
                    $where .= ' AND I.ifAdminStatus = ? AND D.ignore = 0';
                    $param[] = 'down';
                }
                break;
        }//end switch
    }//end if
}//end foreach

$query = 'SELECT * FROM `ports` AS I, `devices` AS D WHERE I.device_id = D.device_id ' . $where . ' ' . $query_sort;

$row = 1;

[$format, $subformat] = explode('_', $vars['format']);

$ports = dbFetchRows($query, $param);

switch ($vars['sort']) {
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
}//end switch

$csv[] = [
    'Device',
    'Port',
    'Speed',
    'Down',
    'Up',
    'Media',
    'Description',
];

foreach ($ports as $port) {
    if (port_permitted($port['port_id'], $port['device_id'])) {
        $speed = \LibreNMS\Util\Number::formatSi($port['ifSpeed'], 2, 3, 'bps');
        $type = \LibreNMS\Util\Rewrite::normalizeIfType($port['ifType']);
        $port['in_rate'] = \LibreNMS\Util\Number::formatSi(($port['ifInOctets_rate'] * 8), 2, 3, 'bps');
        $port['out_rate'] = \LibreNMS\Util\Number::formatSi(($port['ifOutOctets_rate'] * 8), 2, 3, 'bps');
        $port = cleanPort($port, $device);
        $csv[] = [
            format_hostname($port, $port['hostname']),
            \LibreNMS\Util\Rewrite::normalizeIfName($port['label']),
            $speed,
            $port['in_rate'],
            $port['out_rate'],
            $type,
            \LibreNMS\Util\Clean::html($port['ifAlias'], []),
        ];
    }
}
