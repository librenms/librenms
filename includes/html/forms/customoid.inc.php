<?php

header('Content-type: application/json');

if (! Auth::user()->hasGlobalAdmin()) {
    $response = [
        'status'  => 'error',
        'message' => 'Need to be admin',
    ];
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

$status = 'ok';
$message = '';

$device_id = $_POST['device_id'];
$id = $_POST['ccustomoid_id'];
$action = $_POST['action'];
$name = $_POST['name'];
$oid = $_POST['oid'];
$datatype = $_POST['datatype'];
if (empty(($_POST['unit']))) {
    $unit = ['NULL'];
} else {
    $unit = $_POST['unit'];
}
if (! empty(($_POST['limit'])) && is_numeric($_POST['limit'])) {
    $limit = $_POST['limit'];
} else {
    $limit = ['NULL'];
}
if (! empty(($_POST['limit_warn'])) && is_numeric($_POST['limit_warn'])) {
    $limit_warn = $_POST['limit_warn'];
} else {
    $limit_warn = ['NULL'];
}
if (! empty(($_POST['limit_low'])) && is_numeric($_POST['limit_low'])) {
    $limit_low = $_POST['limit_low'];
} else {
    $limit_low = ['NULL'];
}
if (! empty(($_POST['limit_low_warn'])) && is_numeric($_POST['limit_low_warn'])) {
    $limit_low_warn = $_POST['limit_low_warn'];
} else {
    $limit_low_warn = ['NULL'];
}
if ($_POST['alerts'] == 'on') {
    $alerts = 1;
} else {
    $alerts = 0;
}
if ($_POST['passed'] == 'on') {
    $passed = 1;
} else {
    $passed = 0;
}
if (! empty(($_POST['divisor'])) && is_numeric($_POST['divisor'])) {
    $divisor = $_POST['divisor'];
} else {
    $divisor = 1;
}
if (! empty(($_POST['multiplier'])) && is_numeric($_POST['multiplier'])) {
    $multiplier = $_POST['multiplier'];
} else {
    $multiplier = 1;
}
if (! empty(($_POST['user_func']))) {
    $user_func = $_POST['user_func'];
} else {
    $user_func = ['NULL'];
}

if ($action == 'test') {
    $query = 'SELECT * FROM `devices` WHERE `device_id` = ? LIMIT 1';
    $device = dbFetchRow($query, [$device_id]);

    $rawdata = snmp_get($device, $oid, '-Oqv');

    if (is_numeric($rawdata)) {
        if (dbUpdate(
            [
                'customoid_passed' => 1,
            ],
            'customoids',
            'customoid_id=?',
            [$id]
        ) >= 0) {
            $message = "Test successful for <i>$name</i>, value $rawdata received";
        } else {
            $status = 'error';
            $message = "Failed to set pass on OID <i>$name</i>";
        }
    } else {
        $status = 'error';
        $message = "Invalid data in SNMP reply, value $rawdata received";
    }
} else {
    if (is_numeric($id) && $id > 0) {
        if (dbUpdate(
            [
                'customoid_descr'          => $name,
                'customoid_oid'            => $oid,
                'customoid_datatype'       => $datatype,
                'customoid_unit'           => $unit,
                'customoid_divisor'        => $divisor,
                'customoid_multiplier'     => $multiplier,
                'customoid_limit'          => $limit,
                'customoid_limit_warn'     => $limit_warn,
                'customoid_limit_low'      => $limit_low,
                'customoid_limit_low_warn' => $limit_low_warn,
                'customoid_alert'          => $alerts,
                'customoid_passed'         => $passed,
                'user_func'                => $user_func,
            ],
            'customoids',
            '`customoid_id` = ?',
            [$id]
        ) >= 0) { //end if condition
            $message = "Edited OID: <i>$name</i>";
        } else {
            $status = 'error';
            $message = "Failed to edit OID <i>$name</i>";
        }
    } else {
        if (empty($name)) {
            $status = 'error';
            $message = 'No OID name provided';
        } else {
            if (dbFetchCell('SELECT 1 FROM `customoids` WHERE `customoid_descr` = ? AND `device_id`=?', [$name, $device_id])) {
                $status = 'error';
                $message = "OID named <i>$name</i> on this device already exists";
            } else {
                $id = dbInsert(
                    [
                        'device_id'                => $device_id,
                        'customoid_descr'          => $name,
                        'customoid_oid'            => $oid,
                        'customoid_datatype'       => $datatype,
                        'customoid_unit'           => $unit,
                        'customoid_divisor'        => $divisor,
                        'customoid_multiplier'     => $multiplier,
                        'customoid_limit'          => $limit,
                        'customoid_limit_warn'     => $limit_warn,
                        'customoid_limit_low'      => $limit_low,
                        'customoid_limit_low_warn' => $limit_low_warn,
                        'customoid_alert'          => $alerts,
                        'customoid_passed'         => $passed,
                        'user_func'                => $user_func,
                    ],
                    'customoids'
                );
                if ($id) {
                    $message = "Added OID: <i>$name</i>";
                } else {
                    $status = 'error';
                    $message = "Failed to add OID: <i>$name</i>";
                }
            }
        }
    }
}

exit(json_encode([
    'status'       => $status,
    'message'      => $message,
]));
