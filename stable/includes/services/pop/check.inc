<?php

$check = shell_exec($config['nagios_plugins'] . "/check_pop -H ".$service['hostname']);

list($check, $time) = split("\|", $check);

if(strstr($check, "POP OK - ")) {
  $status = '1';
} else {
  $status = '0';
}

?>
