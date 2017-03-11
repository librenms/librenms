<?php

$asn  = clean($_POST['asn']);
$ixid = clean($_POST['ixid']);

$data = dbFetchRows("SELECT * FROM `pdb_ix_peers` WHERE `ix_id` = ? AND `local_asn` = ? AND UNIX_TIMESTAMP() < `timestamp` LIMIT 1", array($ixid, $asn));

if (count($data) === 0) {
    $get = Requests::get("https://peeringdb.com/api/ixlan/$ixid?depth=2");
    $json_data = $get->body;
    $data = json_decode($json_data);
    $peers = $data->{'data'}{0}->{'net_set'};
    $timestamp = time();
    dbDelete('pdb_ix_peers', '`ix_id` = ? AND `local_asn` = ?', array($ixid, $asn));
    foreach ($peers as $peer) {
        dbInsert(array('ix_id' => $ixid, 'local_asn' => $asn, 'peer_id' => $peer->{'id'}, 'remote_asn' => $peer->{'asn'}, 'name' => $peer->{'name'}, 'timestamp' => $timestamp), 'pdb_ix_peers');
    }
}

$where = 1;
$param = array();

$sql = ' FROM `pdb_ix_peers`';
exit;

if (isset($current)) {
    $limit_low = (($current * $rowCount) - ($rowCount));
    $limit_high = $rowCount;
}

foreach ($data as $peer) {
    $response[] = array(
        'peer' => $peer->{'name'},
        'connected'   => "<a class='btn btn-sm btn-primary' href='" . generate_url(array('page' => 'peering', 'section' => 'ix-peers', 'asn' => $asn['bgpLocalAs'], 'ixid' => $ix->{'ix_id'})) . "' role='button'>Show Peers</a>"
    );
}



$output = array(
    'current'  => $current,
    'rowCount' => $rowCount,
    'rows'     => $response,
    'total'    => $total,
);
echo _json_encode($output);
