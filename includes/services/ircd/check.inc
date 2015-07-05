<?php

$check = shell_exec($config['nagios_plugins'] . "/check_ircd -H ".$service['hostname']." ".$service['service_param']);

list($check, $time) = split("\|", $check);

if (strstr($check, "IRCD ok")) {
    $status = '1';
} else {
    $status = '0';
}

?>
