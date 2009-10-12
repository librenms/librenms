#!/usr/bin/php
<?php

include("config.php");
include("includes/functions.php");

## Check all of our interface RRD files for errors

if($argv[1]) { $where = "AND `interface_id` = '$argv[1]'"; }

$i = '0';

$interface_query = mysql_query("SELECT * FROM `interfaces` AS I, `devices` AS D WHERE I.device_id = D.device_id $where");
while ($interface = mysql_fetch_array($interface_query)) {
  $rrdfile = $config['rrd_dir'] . "/" . $interface['hostname'] . "/" . $interface['ifIndex'] . ".rrd";
  $errors = interface_errors($rrdfile);
  mysql_query("UPDATE `interfaces` SET in_errors = '" . $errors['in'] . "', out_errors = '" . $errors['out'] . "' WHERE interface_id = '" . $interface['interface_id'] . "'");
  if($errors['in'] > '100' || $errors['out'] > '100') { 
    $errored[] = $interface['hostname'] . " - " . $interface['ifDescr'] . " - " . $interface['ifAlias'] . " - " . $errors['in'] . " - " . $errors['out']; 
  }
  $i++;
}

echo("Checked $i Interfaces\n");

if($errored) { ## If there are errored interfaces
  $i=0;
  $msg = "Interfaces with errors : \n\n";
  foreach ($errored as $int) {
    $msg .= "$int\n";  ## Add a line to the report email warning about them
    $i++;
  } 
  ## Send the alert email
  mail($config['email_default'], "Observer detected errors on $i interfaces", $msg, $config['email_headers']);
}

?>
