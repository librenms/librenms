<?php

$asn    = clean($_POST['asn']);
$ixid   = clean($_POST['ixid']);
$status = clean($_POST['status']);

$sql    = " FROM `pdb_ix_peers` AS `P` LEFT JOIN `pdb_ix` ON `P`.`ix_id` = `pdb_ix`.`ix_id` LEFT JOIN `bgpPeers` ON `P`.`remote_asn` = `bgpPeers`.`bgpPeerRemoteAs` LEFT JOIN `devices` ON `bgpPeers`.`device_id` = `devices`.`device_id` WHERE `P`.`ix_id` = ?";
$params = array($ixid);

if ($status === 'connected') {
    $sql .= " AND `remote_asn` = `bgpPeerRemoteAs` ";
}

if ($status === 'unconnected') {
    $sql .= " AND `bgpPeerRemoteAs` IS NULL ";
}

if (isset($searchPhrase) && !empty($searchPhrase)) {
    $sql .= " AND (`remote_asn` LIKE '%$searchPhrase%' OR `P`.`name` LIKE '%$searchPhrase%')";
}

$count_sql = "SELECT COUNT(*) $sql";

$total     = dbFetchCell($count_sql, $params);
if (empty($total)) {
    $total = 0;
}

$sql .= ' GROUP BY `bgpPeerRemoteAs`, `P`.`name`, `P`.`remote_asn`, `P`.`peer_id` ';

if (!isset($sort) || empty($sort)) {
    $sort = 'remote_asn ASC';
}

$sql .= " ORDER BY $sort";

if (isset($current)) {
    $limit_low  = (($current * $rowCount) - ($rowCount));
    $limit_high = $rowCount;
}

if ($rowCount != -1) {
    $sql .= " LIMIT $limit_low,$limit_high";
}

$sql = "SELECT `P`.`name`, `P`.`remote_asn`, `P`.`peer_id`, `bgpPeers`.`bgpPeerRemoteAs` $sql";

foreach (dbFetchRows($sql, $params) as $peer) {
    if ($peer['remote_asn'] === $peer['bgpPeerRemoteAs']) {
        $connected = 'Connected';
    } else {
        $connected = 'Not Connected';
    }
    $peer_id = $peer['peer_id'];
    $response[] = array(
        'peer'      => $peer['name'],
        'connected' => "$connected",
        'links'     => "<a href='https://peeringdb.com/net/$peer_id'><i class='fa fa-database'></i></a>",
    );
}

$output = array(
    'current'  => $current,
    'rowCount' => $rowCount,
    'rows'     => $response,
    'total'    => $total,
);
echo _json_encode($output);
