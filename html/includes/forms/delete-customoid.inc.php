<?php

use LibreNMS\Authentication\LegacyAuth;

header('Content-type: text/plain');

if (!LegacyAuth::user()->hasGlobalAdmin()) {
    die('ERROR: You need to be admin');
}

if (!is_numeric($_POST['customoid_id'])) {
    echo 'ERROR: No alert selected';
    exit;
} else {
    if (dbDelete('customoids', '`customoid_id` =  ?', array($_POST['customoid_id']))) {
        echo 'Custom OID has been deleted.';
        exit;
    } else {
        echo 'ERROR: Custom OID has not been deleted.';
        exit;
    }
}
