<?php

header('Content-type: application/json');

if (! Auth::user()->hasGlobalAdmin()) {
    exit(json_encode([
        'status' => 'error',
        'message' => 'Need to be admin',
    ]));
}

$status = 'error';
$message = '';

$device_id = $_POST['device_id'];
$id = $_POST['ccustomoid_id'];
$action = $_POST['action'];
$name = strip_tags($_POST['name']);
$oid = strip_tags($_POST['oid']);
$datatype = strip_tags($_POST['datatype']);
$unit = $_POST['unit'];
$limit = $_POST['limit'];
$limit_warn = $_POST['limit_warn'];
$limit_low = $_POST['limit_low'];
$limit_low_warn = $_POST['limit_low_warn'];
$alerts = ($_POST['alerts'] == 'on' ? 1 : 0);
$passed = ($_POST['passed'] == 'on' ? 1 : 0);
$divisor = set_numeric($_POST['divisor'], 1);
$multiplier = set_numeric($_POST['multiplier'], 1);
$user_func = $_POST['user_func'];

if ($action == 'test') {
    $query = 'SELECT * FROM `devices` WHERE `device_id` = ? LIMIT 1';
    $device = dbFetchRow($query, [$device_id]);

    $rawdata = snmp_get($device, $oid, '-Oqv');

    if (is_numeric($rawdata)) {
        $oid_value = $rawdata;
    } elseif (
        ! empty($_POST['unit']) &&
        str_i_contains($rawdata, $unit) &&
        is_numeric(trim(str_replace($unit, '', $rawdata)))
    ) {
        $oid_value = trim(str_replace($unit, '', $rawdata));
    } elseif (is_numeric(string_to_float($rawdata))) {
        $oid_value = string_to_float($rawdata);
    }

    if (is_numeric($oid_value)) {
        if (dbUpdate(
            [
                'customoid_passed' => 1,
            ],
            'customoids',
            'customoid_id=?',
            [$id]
        ) >= 0) {
            $message = "Test successful for <i>$name</i>, value $rawdata received";
            $status = 'ok';
        } else {
            $message = "Failed to set pass on OID <i>$name</i>";
        }
    } else {
        $message = "Invalid data in SNMP reply, value $rawdata received";
    }
} else {
    if (is_numeric($id) && $id > 0) {
        if (dbUpdate(
            [
                'customoid_descr' => $name,
                'customoid_oid' => $oid,
                'customoid_datatype' => $datatype,
                'customoid_unit' => $unit,
                'customoid_divisor' => $divisor,
                'customoid_multiplier' => $multiplier,
                'customoid_limit' => $limit,
                'customoid_limit_warn' => $limit_warn,
                'customoid_limit_low' => $limit_low,
                'customoid_limit_low_warn' => $limit_low_warn,
                'customoid_alert' => $alerts,
                'customoid_passed' => $passed,
                'user_func' => $user_func,
            ],
            'customoids',
            '`customoid_id` = ?',
            [$id]
        ) >= 0) { //end if condition
            $message = "Edited OID: <i>$name</i>";
            $status = 'ok';
        } else {
            $message = "Failed to edit OID <i>$name</i>";
        }
    } elseif (empty($name)) {
        $message = 'No OID name provided';
    } elseif (dbFetchCell('SELECT 1 FROM `customoids` WHERE `customoid_descr` = ? AND `device_id`=?', [$name, $device_id])) {
        $message = "OID named <i>$name</i> on this device already exists";
    } else {
        $id = dbInsert(
            [
                'device_id' => $device_id,
                'customoid_descr' => $name,
                'customoid_oid' => $oid,
                'customoid_datatype' => $datatype,
                'customoid_unit' => $unit,
                'customoid_divisor' => $divisor,
                'customoid_multiplier' => $multiplier,
                'customoid_limit' => $limit,
                'customoid_limit_warn' => $limit_warn,
                'customoid_limit_low' => $limit_low,
                'customoid_limit_low_warn' => $limit_low_warn,
                'customoid_alert' => $alerts,
                'customoid_passed' => $passed,
                'user_func' => $user_func,
            ],
            'customoids'
        );
        if ($id) {
            $message = "Added OID: <i>$name</i>";
            $status = 'ok';
        } else {
            $message = "Failed to add OID: <i>$name</i>";
        }
    }
}

exit(json_encode([
    'status' => $status,
    'message' => $message,
]));
