<?php

$check = shell_exec($config['nagios_plugins'] . "/check_spop -H ".$service['hostname']);

list($check, $time) = split("\|", $check);

if(strstr($check, "SPOP OK")) {
  $status = '1';
} else {
  $status = '0';
}

?>
