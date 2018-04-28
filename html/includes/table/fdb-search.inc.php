<?php

use LibreNMS\Authentication\Auth;

$param = array();

$select = "SELECT `F`.`port_id` AS `port_id`, `F`.`device_id`, `ifInErrors`, `ifOutErrors`, `ifOperStatus`,";
$select .= " `ifAdminStatus`, `ifAlias` AS `interface`, `ifDescr`, `mac_address`, `V`.`vlan_vlan` AS `vlan`,";
$select .= " `hostname`, `hostname` AS `device` , group_concat(`M`.`ipv4_address` SEPARATOR ', ') AS `ipv4_address`";

$sql  = " FROM `ports_fdb` AS `F`";
$sql .= " LEFT JOIN `devices` AS `D` USING(`device_id`)";
$sql .= " LEFT JOIN `ports` AS `P` USING(`port_id`, `device_id`)";
$sql .= " LEFT JOIN `vlans` AS `V` USING(`vlan_id`, `device_id`)";
// Add counter so we can ORDER BY the port_id with least amount of macs attached
$sql .= " LEFT JOIN ( SELECT `port_id`, COUNT(*) `portCount` FROM `ports_fdb` GROUP BY `port_id` ) AS `C` ON `C`.`port_id` = `F`.`port_id`";

$where = " WHERE 1";

if (!Auth::user()->hasGlobalRead()) {
    $sql    .= ' LEFT JOIN `devices_perms` AS `DP` USING (`device_id`)';
    $where  .= ' AND `DP`.`user_id`=?';
    $param[] = Auth::id();
}

if (is_numeric($vars['device_id'])) {
    $where    .= ' AND `F`.`device_id`=?';
    $param[] = $vars['device_id'];
}

if (is_numeric($vars['port_id'])) {
    $where    .= ' AND `F`.`port_id`=?';
    $param[] = $vars['port_id'];
}

if (isset($vars['searchPhrase']) && !empty($vars['searchPhrase'])) {
    $search = mres(trim($vars['searchPhrase']));
    $ip_search = '%'.mres(trim($vars['searchPhrase'])).'%';
    $mac_search = '%'.str_replace(array(':', ' ', '-', '.', '0x'), '', $search).'%';

    if (isset($vars['searchby']) && $vars['searchby'] == 'vlan') {
        $where  .= ' AND `V`.`vlan_vlan` = ?';
        $param[] = (int)$search;
    } elseif (isset($vars['searchby']) && $vars['searchby'] == 'ip') {
        $sql .= " LEFT JOIN `ipv4_mac` AS `M` USING (`mac_address`)";
        $where  .= ' AND `M`.`ipv4_address` LIKE ?';
        $param[] = $ip_search;
    } elseif ((isset($vars['searchby']) && $vars['searchby'] == 'mac') ||
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

// Don't use ipv4_mac in count it will inflate the rows unless we aggregate it
// Except for 'ip' search.
if ($vars['searchby'] != 'ip') {
    $sql .= " LEFT JOIN `ipv4_mac` AS `M` USING (`mac_address`)";
}
$sql .= $where;
$sql .= " GROUP BY `device_id`, `port_id`, `mac_address`, `vlan`, `hostname`, `ifAlias`,";
$sql .= " `ifAdminStatus`, `ifDescr`, `ifOperStatus`, `ifInErrors`, `ifOutErrors`";

// Get most likely endpoint port_id, used to add a visual marker for this element
// in the list 
if (isset($vars['searchby']) && !empty($vars['searchPhrase']) && $vars['searchby'] != 'vlan') {
    $countsql .= " ORDER BY `C`.`portCount` ASC LIMIT 1";
    foreach (dbFetchRows($select . $sql . $countsql, $param) as $entry) {
        $endpoint_portid = $entry['port_id'];
    }
}

if (!isset($sort) || empty($sort)) {
    $sort = '`C`.`portCount` ASC';
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
        if ($entry['port_id'] == $endpoint_portid) {
            $endpoint_img = "<i class='fa fa-star fa-lg' style='color:green' aria-hidden='true' title='This indicates the most likely endpoint switchport'></i>";
        } else {
            $endpoint_img = '';
        }

        $response[] = array(
            'device'       => generate_device_link(device_by_id_cache($entry['device_id'])),
            'mac_address'  => formatMac($entry['mac_address']),
            'ipv4_address' => $entry['ipv4_address'],
            'interface'    => generate_port_link($entry, makeshortif(fixifname($entry['label']))).' '.$error_img.' '.$endpoint_img,
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
