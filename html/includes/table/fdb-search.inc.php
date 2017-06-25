<?php
$param = array();

$sql .= ' FROM `ports_fdb` AS F, `ports` AS P, `devices` AS D, `vlans` as V ';

if (is_admin() === false && is_read() === false) {
    $sql    .= ' LEFT JOIN `devices_perms` AS `DP` ON `D`.`device_id` = `DP`.`device_id`';
    $where  .= ' AND `DP`.`user_id`=?';
    $param[] = $_SESSION['user_id'];
}

$sql .= " WHERE F.port_id = P.port_id AND P.device_id = D.device_id AND F.vlan_id = V.vlan_id $where ";

if (is_numeric($_POST['device_id'])) {
    $sql    .= ' AND P.device_id = ?';
    $param[] = $_POST['device_id'];
}

if (is_numeric($_POST['port_id'])) {
    $sql    .= ' AND P.port_id = ?';
    $param[] = $_POST['port_id'];
}

if (isset($_POST['searchPhrase']) && !empty($_POST['searchPhrase'])) {
    $vlan_search = mres(trim($_POST['searchPhrase']));
    $mac_search = '%'.str_replace(array(':', ' ', '-', '.', '0x'), '', mres($_POST['searchPhrase'])).'%';

    if (isset($_POST['searchby']) && $_POST['searchby'] == 'vlan') {
        $sql    .= ' AND `vlan_vlan` LIKE ?';
        $param[] = $vlan_search;
    } elseif (isset($_POST['searchby']) && $_POST['searchby'] == 'mac') {
        $sql    .= ' AND `mac_address` LIKE ?';
        $param[] = $mac_search;
    } else {
        $sql .= ' AND (`vlan_vlan` LIKE ? OR `mac_address` LIKE ?)';
        $param[] = $vlan_search;
        $param[] = $mac_search;
    }
}

$count_sql = "SELECT COUNT(`F`.`port_id`) $sql";

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

$sql = "SELECT *,`P`.`ifDescr` AS `interface`,`V`.`vlan_vlan` $sql";

foreach (dbFetchRows($sql, $param) as $entry) {
    $entry = cleanPort($entry);
    if (!$ignore) {
        if ($entry['ifInErrors'] > 0 || $entry['ifOutErrors'] > 0) {
            $error_img = generate_port_link($entry, "<i class='fa fa-flag fa-lg' style='color:red' aria-hidden='true'></i>", port_errors);
        } else {
            $error_img = '';
        }

        $fdb_host = dbFetchRow('SELECT * FROM ports_fdb AS F, ipv4_mac AS M WHERE F.mac_address = ? AND M.mac_address = F.mac_address', array($entry['mac_address']));
        $response[] = array(
            'os'               => $entry['os'],
            'mac_address'      => formatMac($entry['mac_address']),
            'ipv4_address'     => $fdb_host['ipv4_address'],
            'hostname'         => generate_device_link($entry),
            'interface'        => generate_port_link($entry, makeshortif(fixifname(cleanPort($entry['label'])))).' '.$error_img,
            'vlan'             => $entry['vlan_vlan'],
        );
    }//end if

    unset($ignore);
}//end foreach

$output = array(
    'current'  => $current,
    'rowCount' => $rowCount,
    'rows'     => $response,
    'total'    => $total,
);
echo _json_encode($output);
