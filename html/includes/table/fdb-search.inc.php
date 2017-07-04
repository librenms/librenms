<?php
$param = array();

$sql = ' FROM `ports_fdb` AS `F`';

if (is_admin() === false && is_read() === false) {
    $sql    .= ' LEFT JOIN `devices_perms` AS `DP` ON `D`.`device_id` = `DP`.`device_id`';
    $where  .= ' AND `DP`.`user_id`=?';
    $param[] = $_SESSION['user_id'];
}

$sql .= " LEFT JOIN `ports` AS `P` ON `F`.`port_id`=`P`.`port_id`";
$sql .= " LEFT JOIN `devices` AS `D` ON `F`.`device_id`=`D`.`device_id`";
$sql .= " LEFT JOIN `vlans` AS `V` ON `F`.`vlan_id`=`V`.`vlan_id`";
$sql .= " LEFT JOIN `ipv4_mac` ON `F`.`mac_address`=`ipv4_mac`.`mac_address`";

$sql .= " WHERE 1";
if (is_numeric($_POST['device_id'])) {
    $sql    .= ' AND `F`.`device_id`=?';
    $param[] = $_POST['device_id'];
}

if (is_numeric($_POST['port_id'])) {
    $sql    .= ' AND `F`.`port_id`=?';
    $param[] = $_POST['port_id'];
}

if (isset($_POST['searchPhrase']) && !empty($_POST['searchPhrase'])) {
    $vlan_search = mres(trim($_POST['searchPhrase']));
    $mac_search = '%'.str_replace(array(':', ' ', '-', '.', '0x'), '', mres($_POST['searchPhrase'])).'%';

    if (isset($_POST['searchby']) && $_POST['searchby'] == 'vlan') {
        $sql    .= ' AND `V`.`vlan_vlan` = ?';
        $param[] = $vlan_search;
    } elseif (isset($_POST['searchby']) && $_POST['searchby'] == 'mac') {
        $sql    .= ' AND `F`.`mac_address` LIKE ?';
        $param[] = $mac_search;
    } else {
        $sql .= ' AND (`V`.`vlan_vlan` = ? OR `F`.`mac_address` LIKE ?)';
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
    $sort = '`F`.`port_id` ASC';
}

$sql .= " ORDER BY $sort";

if (isset($current)) {
    $limit_low  = (($current * $rowCount) - ($rowCount));
    $limit_high = $rowCount;
}

if ($rowCount != -1) {
    $sql .= " LIMIT $limit_low,$limit_high";
}

$sql = "SELECT `P`.*, `ifDescr` AS `interface`, `F`.`mac_address`, `ipv4_mac`.`ipv4_address`, `V`.`vlan_vlan` as `vlan`, `D`.`hostname` AS `device` $sql";

foreach (dbFetchRows($sql, $param) as $entry) {
    $entry = cleanPort($entry);
    if (!$ignore) {
        if ($entry['ifInErrors'] > 0 || $entry['ifOutErrors'] > 0) {
            $error_img = generate_port_link($entry, "<i class='fa fa-flag fa-lg' style='color:red' aria-hidden='true'></i>", 'port_errors');
        } else {
            $error_img = '';
        }

        $response[] = array(
            'device'           => generate_device_link(device_by_id_cache($entry['device_id'])),
            'mac_address'      => formatMac($entry['mac_address']),
            'ipv4_address'     => $entry['ipv4_address'],
            'interface'        => generate_port_link($entry, makeshortif(fixifname(cleanPort($entry['label'])))).' '.$error_img,
            'vlan'             => $entry['vlan'],
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
