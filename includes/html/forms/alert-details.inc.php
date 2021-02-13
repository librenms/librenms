<?php

header('Content-type: application/json');

$alert_log_id = $vars['alert_log_id'];
$sub_type = $vars['sub_type'];
$status = 'error';
$details = 'No Details found';
$message = 'No Details found';

if (! Auth::user()->hasGlobalAdmin()) {
    $message = 'Wrong permissions';
    $details = 'You need to have admin permissions.';
    exit(json_encode([
        'status'  => $status,
        'message' => $message,
        'details' => $details,
    ]));
}

if (is_numeric($alert_log_id)) {
    foreach (dbFetchRows('SELECT device_id, id, time_logged, details as detail FROM alert_log WHERE state != 2 && state != 0  && id =  ?', [$alert_log_id])  as $alertlog) {
        $details = json_decode(gzuncompress($alertlog['detail']), true)['rule'];
        if (! empty($details)) {
            $message = 'Found alert details';
            $status = 'ok';
        } else {
            $details = 'No Details found';
        }
    }
} else {
    $message = 'Invalid alert id';
    $details = 'Invalid alert id';
}

exit(json_encode([
    'status'  => $status,
    'message' => $message,
    'details' => $details,
]));
