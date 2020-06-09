<?php

// Exclude Private and reserved ASN ranges
// 64512 - 65535
// 4200000000 - 4294967295
$sql = " FROM `devices` WHERE `disabled` = 0 AND `ignore` = 0 AND `bgpLocalAs` > 0 AND (`bgpLocalAs` < 64512 OR `bgpLocalAs` > 65535) AND `bgpLocalAs` < 4200000000 ";

if (isset($searchPhrase) && !empty($searchPhrase)) {
    $sql .= " AND (`bgpLocalAs` LIKE '%$searchPhrase%')";
}

$count_sql = "SELECT COUNT(*) $sql";

$total     = dbFetchCell($count_sql);
if (empty($total)) {
    $total = 0;
}

if (!isset($sort) || empty($sort)) {
    $sort = 'bgpLocalAs ASC';
}

$sql .= " GROUP BY `bgpLocalAs` ORDER BY $sort";

if (isset($current)) {
    $limit_low  = (($current * $rowCount) - ($rowCount));
    $limit_high = $rowCount;
}

if ($rowCount != -1) {
    $sql .= " LIMIT $limit_low,$limit_high";
}

$sql = "SELECT `bgpLocalAs` $sql";

foreach (dbFetchRows($sql) as $asn) {
    $astext = get_astext($asn['bgpLocalAs']);
    $response[] = array(
        'bgpLocalAs'    => $asn['bgpLocalAs'],
        'asname' => $astext,
        'action' => "<a class='btn btn-sm btn-primary' href='" . generate_url(array('page' => 'peering', 'section' => 'ix-list', 'bgpLocalAs' => $asn['bgpLocalAs'])) . "' role='button'>Show connected IXes</a>",
    );
}

$output = array(
    'current'  => $current,
    'rowCount' => $rowCount,
    'rows'     => $response,
    'total'    => $total,
);
echo _json_encode($output);
