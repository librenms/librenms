<?php

$where = 1;
$param = array();

if ($_POST['search_type'] == 'ipv4') {
    $sql = " FROM `ipv4_addresses` AS A, `ports` AS I, `devices` AS D, `ipv4_networks` AS N WHERE I.port_id = A.port_id AND I.device_id = D.device_id AND N.ipv4_network_id = A.ipv4_network_id ";
} elseif ($_POST['search_type'] == 'ipv6') {
    $sql = " FROM `ipv6_addresses` AS A, `ports` AS I, `devices` AS D, `ipv6_networks` AS N WHERE I.port_id = A.port_id AND I.device_id = D.device_id AND N.ipv6_network_id = A.ipv6_network_id ";
} elseif ($_POST['search_type'] == 'mac') {
    $sql = " FROM `ports` AS I, `devices` AS D WHERE I.device_id = D.device_id AND `ifPhysAddress` LIKE ? ";
    $param = array("%".str_replace(array(':', ' ', '-', '.', '0x'),'',mres($_POST['address']))."%");
}
if (is_numeric($_POST['device_id'])) {
    $sql  .= " AND I.device_id = ?";
    $param[] = $_POST['device_id'];
}
if ($_POST['interface']) {
    $sql .= " AND I.ifDescr LIKE '?'";
    $param[] = $_POST['interface'];
}

if ($_POST['search_type'] == 'ipv4') {
    $count_sql = "SELECT COUNT(`ipv4_address_id`) $sql";
} elseif ($_POST['search_type'] == 'ipv6') {
    $count_sql = "SELECT COUNT(`ipv6_address_id`) $sql";
} elseif ($_POST['search_type'] == 'mac') {
     $count_sql = "SELECT COUNT(`port_id`) $sql";
}
$total = dbFetchCell($count_sql,$param);

if (!isset($sort) || empty($sort)) {
    $sort = '`hostname` ASC';
}

$sql .= " ORDER BY $sort";

if (isset($current)) {
    $limit_low = ($current * $rowCount) - ($rowCount);
    $limit_high = $rowCount;
}

if ($rowCount != -1) {
    $sql .= " LIMIT $limit_low,$limit_high";
}

$sql = "SELECT *,`I`.`ifDescr` AS `interface` $sql";

foreach (dbFetchRows($sql, $param) as $interface) {
    if ($_POST['address']) {
        list($addy, $mask) = explode("/", $_POST['address']);
        if ($_POST['search_type']) {
            $tmp_mask = '128';
            if (!Net_IPv6::isInNetmask($interface['ipv6_address'], $addy, $mask)) {
                $ignore = 1;
            } else {
                $ignore = 0;
            }
        } else {
            $tmp_mask = '32';
            if (!match_network($addy . "/" . $mask, $interface['ipv4_address'])) {
                $ignore = 1;
            }
        }
        if (!$mask) {
            $mask = $tmp_mask;
        }
    }
    if (!$ignore) {
        $speed = humanspeed($interface['ifSpeed']);
        $type = humanmedia($interface['ifType']);

        if ($_POST['search_type'] == 'ipv6') {
            list($prefix, $length) = explode("/", $interface['ipv6_network']);
            $address = Net_IPv6::compress($interface['ipv6_address']) . '/'.$length;
        } elseif ($_POST['search_type'] == 'mac') {
            $address = formatMac($interface['ifPhysAddress']);
        } else {
            list($prefix, $length) = explode("/", $interface['ipv4_network']);
            $address = $interface['ipv4_address'] . '/' .$length;
        }

        if ($interface['in_errors'] > 0 || $interface['out_errors'] > 0) {
            $error_img = generate_port_link($interface,"<img src='images/16/chart_curve_error.png' alt='Interface Errors' border=0>",errors);
        } else {
            $error_img = "";
        }
        if (port_permitted($interface['port_id'])) {
            $interface = ifLabel ($interface, $interface);
            $response[] = array('hostname'=>generate_device_link($interface),
                                'interface'=>generate_port_link($interface) . ' ' . $error_img,
                                'address'=>$address,
                                'description'=>$interface['ifAlias']);
        }
    }
    unset($ignore);
}

$output = array('current'=>$current,'rowCount'=>$rowCount,'rows'=>$response,'total'=>$total);
echo _json_encode($output);
