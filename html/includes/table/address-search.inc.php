<?php

use LibreNMS\Util\IP;
use LibreNMS\Authentication\LegacyAuth;

$param = array();

if (!LegacyAuth::user()->hasGlobalRead()) {
    $perms_sql .= ' LEFT JOIN `devices_perms` AS `DP` ON `D`.`device_id` = `DP`.`device_id`';
    $where     .= ' AND `DP`.`user_id`=?';
    $param[]    = array(LegacyAuth::id());
}

list($address,$prefix) = explode('/', $vars['address']);
if ($vars['search_type'] == 'ipv4') {
    $sql  = ' FROM `ipv4_addresses` AS A, `ports` AS I, `ipv4_networks` AS N, `devices` AS D';
    $sql .= $perms_sql;
    $sql .= " WHERE I.port_id = A.port_id AND I.device_id = D.device_id AND N.ipv4_network_id = A.ipv4_network_id $where ";
    if (!empty($address)) {
        $sql .= " AND ipv4_address LIKE '%".$address."%'";
    }

    if (!empty($prefix)) {
        $sql    .= " AND ipv4_prefixlen='?'";
        $param[] = array($prefix);
    }
} elseif ($vars['search_type'] == 'ipv6') {
    $sql  = ' FROM `ipv6_addresses` AS A, `ports` AS I, `ipv6_networks` AS N, `devices` AS D';
    $sql .= $perms_sql;
    $sql .= " WHERE I.port_id = A.port_id AND I.device_id = D.device_id AND N.ipv6_network_id = A.ipv6_network_id $where ";
    if (!empty($address)) {
        $sql .= " AND (ipv6_address LIKE '%".$address."%' OR ipv6_compressed LIKE '%".$address."%')";
    }

    if (!empty($prefix)) {
        $sql .= " AND ipv6_prefixlen = '$prefix'";
    }
} elseif ($vars['search_type'] == 'mac') {
    $sql  = ' FROM `ports` AS I, `devices` AS D';
    $sql .= $perms_sql;
    $sql .= " WHERE I.device_id = D.device_id AND `ifPhysAddress` LIKE '%".str_replace(array(':', ' ', '-', '.', '0x'), '', mres($vars['address']))."%' $where ";
}//end if
if (is_numeric($vars['device_id'])) {
    $sql    .= ' AND I.device_id = ?';
    $param[] = array($vars['device_id']);
}

if ($vars['interface']) {
    $sql    .= " AND I.ifDescr LIKE '?'";
    $param[] = array($vars['interface']);
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

if (!isset($sort) || empty($sort)) {
    $sort = '`hostname` ASC';
}

$sql .= " ORDER BY $sort";

if (isset($current)) {
    $limit_low  = (($current * $rowCount) - ($rowCount));
    $limit_high = $rowCount;
}

if ($rowCount != -1) {
    $sql .= " LIMIT $limit_low,$limit_high";
}

$sql = "SELECT *,`I`.`ifDescr` AS `interface` $sql";

foreach (dbFetchRows($sql, $param) as $interface) {
    $speed = humanspeed($interface['ifSpeed']);
    $type  = humanmedia($interface['ifType']);

    if ($vars['search_type'] == 'ipv6') {
        $address = (string)IP::parse($interface['ipv6_network'], true);
    } elseif ($vars['search_type'] == 'mac') {
        $address = formatMac($interface['ifPhysAddress']);
    } else {
        $address = (string)IP::parse($interface['ipv4_network'], true);
    }

    if ($interface['in_errors'] > 0 || $interface['out_errors'] > 0) {
        $error_img = generate_port_link($interface, "<i class='fa fa-flag fa-lg' style='color:red' aria-hidden='true'></i>", 'errors');
    } else {
        $error_img = '';
    }

    if (port_permitted($interface['port_id'])) {
        $interface  = cleanPort($interface, $interface);
        $response[] = array(
            'hostname'    => generate_device_link($interface),
            'interface'   => generate_port_link($interface).' '.$error_img,
            'address'     => $address,
            'description' => $interface['ifAlias'],
        );
    }
}//end foreach

$output = array(
    'current'  => $current,
    'rowCount' => $rowCount,
    'rows'     => $response,
    'total'    => $total,
);
echo _json_encode($output);
