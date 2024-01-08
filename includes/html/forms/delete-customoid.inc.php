<?php

header('Content-type: text/plain');

if (! Auth::user()->hasGlobalAdmin()) {
    $response = [
        'status'  => 'error',
        'message' => 'Need to be admin',
    ];
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

if (! is_numeric($_POST['customoid_id'])) {
    echo 'ERROR: No alert selected';
    exit;
} else {
    if (dbDelete('customoids', '`customoid_id` =  ?', [$_POST['customoid_id']])) {
        echo 'Custom OID has been deleted.';
        exit;
    } else {
        echo 'ERROR: Custom OID has not been deleted.';
        exit;
    }
}
