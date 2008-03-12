#!/usr/bin/php
<?php

include("config.php");
include("includes/functions.php");

if($argv[1]) { $where = "AND `interface_id` = '$argv[1]'"; }

$interface_query = mysql_query("SELECT * FROM `interfaces` AS I, `devices` AS D WHERE I.device_id = D.device_id $where");
while ($interface = mysql_fetch_array($interface_query)) {
  $errors = interface_errors($interface);
  mysql_query("UPDATE `interfaces` SET in_errors = '" . $errors['in'] . "', out_errors = '" . $errors['out'] . "' WHERE interface_id = '" . $interface['interface_id'] . "'");

  if($errors['in'] > '100' || $errors['out'] > '100') { $errored[] = $interface['hostname'] . " - " . $interface['ifAlias'] . " - " . $errors['in'] . " - " . $errors['out']; }

}

if($errored) {
  $i=0;
  $msg = "Interfaces with errors : \n\n";
  foreach ($errored as $int) {
    $msg .= "$int\n";
    $i++;
  }

  mail($config['email_default'], "Observer detected errors on $i interfaces", $msg, $config['email_headers']);

}

?>
