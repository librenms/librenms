<?php

use LibreNMS\Util\IP;

$param = [];

if (! Auth::user()->hasGlobalRead()) {
    $device_ids = Permissions::devicesForUser()->toArray() ?: [0];
    $where .= ' AND `D`.`device_id` IN ' . dbGenPlaceholders(count($device_ids));
    $param = array_merge($param, $device_ids);
}

[$address,$prefix] = explode('/', $vars['address']);
if ($vars['search_type'] == 'ipv4') {
    $sql = ' FROM `ipv4_addresses` AS A, `ports` AS I, `ipv4_networks` AS N, `devices` AS D';
    $sql .= " WHERE I.port_id = A.port_id AND I.device_id = D.device_id AND N.ipv4_network_id = A.ipv4_network_id $where ";
    if (! empty($address)) {
        $sql .= ' AND ipv4_address LIKE ?';
        $param[] = "%$address%";
    }

    if (! empty($prefix)) {
        $sql .= " AND ipv4_prefixlen='?'";
        $param[] = [$prefix];
    }
} elseif ($vars['search_type'] == 'ipv6') {
    $sql = ' FROM `ipv6_addresses` AS A, `ports` AS I, `ipv6_networks` AS N, `devices` AS D';
    $sql .= " WHERE I.port_id = A.port_id AND I.device_id = D.device_id AND N.ipv6_network_id = A.ipv6_network_id $where ";
    if (! empty($address)) {
        $sql .= ' AND (ipv6_address LIKE ? OR ipv6_compressed LIKE ?)';
        $param[] = "%$address%";
        $param[] = "%$address%";
    }

    if (! empty($prefix)) {
        $sql .= " AND ipv6_prefixlen = '$prefix'";
    }
} elseif ($vars['search_type'] == 'mac') {
    $sql = ' FROM `ports` AS I, `devices` AS D';
    $sql .= " WHERE I.device_id = D.device_id AND `ifPhysAddress` LIKE '%" . str_replace([':', ' ', '-', '.', '0x'], '', $vars['address']) . "%' $where ";
}//end if
if (is_numeric($vars['device_id'])) {
    $sql .= ' AND I.device_id = ?';
    $param[] = $vars['device_id'];
}

if ($vars['interface']) {
    $sql .= ' AND I.ifDescr LIKE ?';
    $param[] = $vars['interface'];
}

if ($vars['search_type'] == 'ipv4') {
    $count_sql = "SELECT COUNT(`ipv4_address_id`) $sql";
} elseif ($vars['search_type'] == 'ipv6') {
    $count_sql = "SELECT COUNT(`ipv6_address_id`) $sql";
} elseif ($vars['search_type'] == 'mac') {
    $count_sql = "SELECT COUNT(`port_id`) $sql";
}

$total = dbFetchCell($count_sql, $param);
if (empty($total)) {
    $total = 0;
}

if (! isset($sort) || empty($sort)) {
    $sort = '`hostname` ASC';
}

$sql .= " ORDER BY $sort";

if (isset($current)) {
    $limit_low = (($current * $rowCount) - ($rowCount));
    $limit_high = $rowCount;
}

if ($rowCount != -1) {
    $sql .= " LIMIT $limit_low,$limit_high";
}

$sql = "SELECT *,`I`.`ifDescr` AS `interface` $sql";

foreach (dbFetchRows($sql, $param) as $interface) {
    $speed = \LibreNMS\Util\Number::formatSi($interface['ifSpeed'], 2, 3, 'bps');
    $type = \LibreNMS\Util\Rewrite::normalizeIfType($interface['ifType']);

    if ($vars['search_type'] == 'ipv6') {
        $address = (string) IP::parse($interface['ipv6_address'], true) . '/' . $interface['ipv6_prefixlen'];
    } elseif ($vars['search_type'] == 'mac') {
        $address = \LibreNMS\Util\Rewrite::readableMac($interface['ifPhysAddress']);
        $mac_oui = \LibreNMS\Util\Rewrite::readableOUI($interface['ifPhysAddress']);
    } else {
        $address = (string) IP::parse($interface['ipv4_address'], true) . '/' . $interface['ipv4_prefixlen'];
    }

    if ($interface['in_errors'] > 0 || $interface['out_errors'] > 0) {
        $error_img = generate_port_link($interface, "<i class='fa fa-flag fa-lg' style='color:red' aria-hidden='true'></i>", 'errors');
    } else {
        $error_img = '';
    }

    if (port_permitted($interface['port_id'])) {
        $interface = cleanPort($interface, $interface);
        $row = [
            'hostname'    => generate_device_link($interface),
            'interface'   => generate_port_link($interface) . ' ' . $error_img,
            'address'     => $address,
            'description' => $interface['ifAlias'],
        ];
        if ($vars['search_type'] == 'mac') {
            $row['mac_oui'] = $mac_oui;
        }
        $response[] = $row;
    }
}//end foreach

$output = [
    'current'  => $current,
    'rowCount' => $rowCount,
    'rows'     => $response,
    'total'    => $total,
];
echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
