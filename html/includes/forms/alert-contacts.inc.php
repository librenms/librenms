<?php
header('Content-type: application/json');

if (is_admin() === false) {
    die(json_encode([
        'status' => 'error',
        'message' => 'ERROR: You need to be admin'
    ]));
}
$status = 'ok';
$message = '';

$name           = mres($_POST['contact-name']);
$transport_type = mres($_POST['transport-type']);
$member         = mres($_POST['member']);


// check for contact name and contact detail
if (empty($name)) {
    $status = 'error';
    $message = 'No contact name provided';
}

if (empty($member)) {
    $status = 'error';
    $message = 'No contact detail provided';
}

if ($transport_type = 'email' && $status == 'ok') {
    //validate email
    if (filter_var($member, FILTER_VALIDATE_EMAIL)) {
        dbInsert(array(
            'email' => $member,
            'contact_name' => $name
        ), 'transport_email');
 
        $message = 'Added contact name'. $name;
    } else {
        $status = 'error';
        $message = 'Invalid email provided';
    }
}

die(json_encode([
    'status' => $status,
    'message' => $message
]));
