<?php

$param = [];

$sql .= ' FROM `ipv4_mac` AS M, `ports` AS P, `devices` AS D ';

if (! Auth::user()->hasGlobalRead()) {
    $device_ids = Permissions::devicesForUser()->toArray() ?: [0];
    $where .= ' AND `D`.`device_id` IN ' . dbGenPlaceholders(count($device_ids));
    $param = array_merge($param, $device_ids);
}

$sql .= " WHERE M.port_id = P.port_id AND P.device_id = D.device_id $where ";

if (is_numeric($vars['device_id'])) {
    $sql .= ' AND P.device_id = ?';
    $param[] = $vars['device_id'];
}

if (is_numeric($vars['port_id'])) {
    $sql .= ' AND P.port_id = ?';
    $param[] = $vars['port_id'];
}

if (isset($vars['searchPhrase']) && ! empty($vars['searchPhrase'])) {
    $ip_search = '%' . trim($vars['searchPhrase']) . '%';
    $mac_search = '%' . str_replace([':', ' ', '-', '.', '0x'], '', $vars['searchPhrase']) . '%';

    if (isset($vars['searchby']) && $vars['searchby'] == 'ip') {
        $sql .= ' AND `ipv4_address` LIKE ?';
        $param[] = $ip_search;
    } elseif (isset($vars['searchby']) && $vars['searchby'] == 'mac') {
        $sql .= ' AND `mac_address` LIKE ?';
        $param[] = $mac_search;
    } else {
        $sql .= ' AND (`ipv4_address` LIKE ? OR `mac_address` LIKE ?)';
        $param[] = $ip_search;
        $param[] = $mac_search;
    }
}

$count_sql = "SELECT COUNT(`M`.`port_id`) $sql";

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

$sql = "SELECT *,`P`.`ifDescr` AS `interface` $sql";

foreach (dbFetchRows($sql, $param) as $entry) {
    $entry = cleanPort($entry);
    if (! $ignore) {
        if ($entry['ifInErrors'] > 0 || $entry['ifOutErrors'] > 0) {
            $error_img = generate_port_link($entry, "<i class='fa fa-flag fa-lg' style='color:red' aria-hidden='true'></i>", 'port_errors');
        } else {
            $error_img = '';
        }

        $arp_host = dbFetchRow('SELECT * FROM ipv4_addresses AS A, ports AS I, devices AS D WHERE A.ipv4_address = ? AND I.port_id = A.port_id AND D.device_id = I.device_id', [$entry['ipv4_address']]);
        if ($arp_host) {
            $arp_name = generate_device_link($arp_host);
        } else {
            unset($arp_name);
        }

        if ($arp_host) {
            $arp_host = cleanPort($arp_host);
            $arp_if = generate_port_link($arp_host);
        } else {
            unset($arp_if);
        }

        if ($arp_host['device_id'] == $entry['device_id']) {
            $arp_name = 'Localhost';
        }

        if ($arp_host['port_id'] == $entry['port_id']) {
            $arp_if = 'Local port';
        }

        $response[] = [
            'mac_address'      => \LibreNMS\Util\Rewrite::readableMac($entry['mac_address']),
            'mac_oui'          => \LibreNMS\Util\Rewrite::readableOUI($entry['mac_address']),
            'ipv4_address'     => $entry['ipv4_address'],
            'hostname'         => generate_device_link($entry),
            'interface'        => generate_port_link($entry, makeshortif($entry['label'])) . ' ' . $error_img,
            'remote_device'    => $arp_name,
            'remote_interface' => $arp_if,
        ];
    }//end if

    unset($ignore);
}//end foreach

$output = [
    'current'  => $current,
    'rowCount' => $rowCount,
    'rows'     => $response,
    'total'    => $total,
];
echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
