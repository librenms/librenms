<?php

if (! Auth::user()->hasGlobalAdmin()) {
    $response = [
        'status'  => 'error',
        'message' => 'Need to be admin',
    ];
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}
$customoid_id = $_POST['customoid_id'];

if (is_numeric($customoid_id) && $customoid_id > 0) {
    $oid = dbFetchRow('SELECT * FROM `customoids` WHERE `customoid_id` = ? LIMIT 1', [$customoid_id]);

    if ($oid['customoid_alert'] == 1) {
        $alerts = true;
    } else {
        $alerts = false;
    }
    if ($oid['customoid_passed'] == 1) {
        $cpassed = true;
        $passed = 'on';
    } else {
        $cpassed = false;
        $passed = '';
    }

    header('Content-type: application/json');
    echo json_encode([
        'name'           => $oid['customoid_descr'],
        'oid'            => $oid['customoid_oid'],
        'datatype'       => $oid['customoid_datatype'],
        'unit'           => $oid['customoid_unit'],
        'divisor'        => $oid['customoid_divisor'],
        'multiplier'     => $oid['customoid_multiplier'],
        'limit'          => $oid['customoid_limit'],
        'limit_warn'     => $oid['customoid_limit_warn'],
        'limit_low'      => $oid['customoid_limit_low'],
        'limit_low_warn' => $oid['customoid_limit_low_warn'],
        'alerts'         => $alerts,
        'cpassed'        => $cpassed,
        'passed'         => $passed,
        'user_func'      => $oid['user_func'],
    ]);
}
