<?php

$updated = '1';

$service_id = add_service(mres($_POST['device']), mres($_POST['type']), mres($_POST['descr']), mres($_POST['ip']), mres($_POST['params']));

if ($service_id) {
    $message .= $message_break . "Service added (".$service_id.")!";
    $message_break .= "<br />";
}