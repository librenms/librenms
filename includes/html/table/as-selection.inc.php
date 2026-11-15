<?php

$param = [];
// Exclude Private and reserved ASN ranges
// 64512 - 65534 (Private)
// 65535 (Well Known)
// 4200000000 - 4294967294 (Private)
// 4294967295 (Reserved)
$sql = ' FROM `devices` WHERE `disabled` = 0 AND `ignore` = 0 AND `bgpLocalAs` > 0 AND (`bgpLocalAs` < 64512 OR `bgpLocalAs` > 65535) AND `bgpLocalAs` < 4200000000 ';

if (Gate::denies('viewAll', App\Models\Device::class)) {
    $device_ids = Permissions::devicesForUser()->toArray() ?: [0];
    // @phpstan-ignore function.deprecated
    $sql .= ' AND `device_id` IN ' . dbGenPlaceholders(count($device_ids));
    $param = array_merge($param, $device_ids);
}

if (isset($searchPhrase) && ! empty($searchPhrase)) {
    $sql .= ' AND (`bgpLocalAs` LIKE ?)';
    $param[] = "%$searchPhrase%";
}

$count_sql = "SELECT COUNT(*) $sql";

$total = dbFetchCell($count_sql, $param);
if (empty($total)) {
    $total = 0;
}

if (! isset($sort) || empty($sort)) {
    $sort = 'bgpLocalAs ASC';
}

$sql .= " GROUP BY `bgpLocalAs` ORDER BY $sort";

if (isset($current)) {
    $limit_low = (($current * $rowCount) - $rowCount);
    $limit_high = $rowCount;
}

if ($rowCount != -1) {
    $sql .= " LIMIT $limit_low,$limit_high";
}

$sql = "SELECT `bgpLocalAs` $sql";

foreach (dbFetchRows($sql, $param) as $asn) {
    $astext = \LibreNMS\Util\AutonomousSystem::get($asn['bgpLocalAs'])->name();
    $response[] = [
        'bgpLocalAs' => $asn['bgpLocalAs'],
        'asname' => $astext,
        'action' => "<a class='btn btn-sm btn-primary' href='" . \LibreNMS\Util\Url::generate(['page' => 'peering', 'section' => 'ix-list', 'bgpLocalAs' => $asn['bgpLocalAs']]) . "' role='button'>Show connected IXes</a>",
    ];
}

$output = [
    'current' => $current,
    'rowCount' => $rowCount,
    'rows' => $response,
    'total' => $total,
];
echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
