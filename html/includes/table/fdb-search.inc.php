<?php
$param = array();

$select = "SELECT `F`.`port_id` AS `port_id`, `device_id`, `ifInErrors`, `ifOutErrors`, `ifOperStatus`,";
$select .= " `ifAdminStatus`, `ifAlias` AS `interface`, `ifDescr`, `mac_address`, `V`.`vlan_vlan` AS `vlan`,";
$select .= " `hostname`, `hostname` AS `device` , group_concat(`M`.`ipv4_address` SEPARATOR ', ') AS `ipv4_address`";

$sql  = " FROM `ports_fdb` AS `F`";
$sql .= " LEFT JOIN `devices` AS `D` USING(`device_id`)";
$sql .= " LEFT JOIN `ports` AS `P` USING(`port_id`, `device_id`)";
$sql .= " LEFT JOIN `vlans` AS `V` USING(`vlan_id`, `device_id`)";

$where = " WHERE 1";

if (is_admin() === false && is_read() === false) {
    $sql    .= ' LEFT JOIN `devices_perms` AS `DP` USING (`device_id`)';
    $where  .= ' AND `DP`.`user_id`=?';
    $param[] = $_SESSION['user_id'];
}

if (is_numeric($_POST['device_id'])) {
    $where    .= ' AND `F`.`device_id`=?';
    $param[] = $_POST['device_id'];
}

if (is_numeric($_POST['port_id'])) {
    $where    .= ' AND `F`.`port_id`=?';
    $param[] = $_POST['port_id'];
}

if (isset($_POST['searchPhrase']) && !empty($_POST['searchPhrase'])) {
    $search = mres(trim($_POST['searchPhrase']));
    $mac_search = '%'.str_replace(array(':', ' ', '-', '.', '0x'), '', $search).'%';

    if (isset($_POST['searchby']) && $_POST['searchby'] == 'vlan') {
        $where  .= ' AND `V`.`vlan_vlan` = ?';
        $param[] = (int)$search;
    } elseif ((isset($_POST['searchby']) && $_POST['searchby'] == 'mac') ||
        (!is_numeric($search) || $search > 4096)
    ) {
        $where  .= ' AND `F`.`mac_address` LIKE ?';
        $param[] = $mac_search;
    } else {
        $where  .= ' AND (`V`.`vlan_vlan` = ? OR `F`.`mac_address` LIKE ?)';
        $param[] = (int)$search;
        $param[] = $mac_search;
    }
}

$total = (int)dbFetchCell("SELECT COUNT(*) $sql $where", $param);

// Don't use ipv4_mac in count it will inflate the rows unless we aggregate it and it isn't used for search
$sql .= " LEFT JOIN `ipv4_mac` AS `M` USING (`mac_address`, `device_id`)";
$sql .= $where;
$sql .= " GROUP BY `device_id`, `port_id`, `mac_address`, `vlan`, `hostname`, `ifAlias`,";
$sql .= " `ifAdminStatus`, `ifDescr`, `ifOperStatus`, `ifInErrors`, `ifOutErrors`";

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

$response = array();
foreach (dbFetchRows($select . $sql, $param) as $entry) {
    $entry = cleanPort($entry);
    if (!$ignore) {
        if ($entry['ifInErrors'] > 0 || $entry['ifOutErrors'] > 0) {
            $error_img = generate_port_link(
                $entry,
                "<i class='fa fa-flag fa-lg' style='color:red' aria-hidden='true'></i>",
                'port_errors'
            );
        } else {
            $error_img = '';
        }

        $response[] = array(
            'device'       => generate_device_link(device_by_id_cache($entry['device_id'])),
            'mac_address'  => formatMac($entry['mac_address']),
            'ipv4_address' => $entry['ipv4_address'],
            'interface'    => generate_port_link($entry, makeshortif(fixifname($entry['label']))).' '.$error_img,
            'vlan'         => $entry['vlan'],
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
