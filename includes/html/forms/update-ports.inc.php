<?php

header('Content-type: application/json');

if (!Auth::user()->hasGlobalAdmin()) {
    $response = array(
        'status'  => 'error',
        'message' => 'Need to be admin',
    );
    echo _json_encode($response);
    exit;
}

$status           = 'error';
$message          = 'Error with config';

// enable/disable ports/interfaces on devices.
$device_id    = intval($_POST['device']);
$rows_updated = 0;

foreach ($_POST as $key => $val) {
    if (strncmp($key, 'oldign_', 7) == 0) {
        // Interface identifier passed as part of the field name
        $port_id = intval(substr($key, 7));

        $oldign = intval($val) ? 1 : 0;
        $newign = $_POST['ignore_'.$port_id] ? 1 : 0;

        // As checkboxes are not posted when unset - we effectively need to do a diff to work
        // out a set->unset case.
        if ($oldign == $newign) {
            continue;
        }

        $n = dbUpdate(array('ignore' => $newign), 'ports', '`device_id` = ? AND `port_id` = ?', array($device_id, $port_id));

        if ($n < 0) {
            $rows_updated = -1;
            break;
        }

        $rows_updated += $n;
    } elseif (strncmp($key, 'olddis_', 7) == 0) {
        // Interface identifier passed as part of the field name
        $port_id = intval(substr($key, 7));

        $olddis = intval($val) ? 1 : 0;
        $newdis = $_POST['disabled_'.$port_id] ? 1 : 0;

        // As checkboxes are not posted when unset - we effectively need to do a diff to work
        // out a set->unset case.
        if ($olddis == $newdis) {
            continue;
        }

        $n = dbUpdate(array('disabled' => $newdis), 'ports', '`device_id` = ? AND `port_id` = ?', array($device_id, $port_id));

        if ($n < 0) {
            $rows_updated = -1;
            break;
        }

        $rows_updated += $n;
    }//end if
}//end foreach

if ($rows_updated > 0) {
    $message = $rows_updated.' Device record updated.';
    $status         = 'ok';
} elseif ($rows_updated = '-1') {
    $message = 'Device record unchanged. No update necessary.';
    $status         = 'ok';
} else {
    $message = 'Device record update error.';
}

$response = array(
    'status'        => $status,
    'message'       => $message,
);
echo _json_encode($response);
