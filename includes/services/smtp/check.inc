<?php

$check = `scripts/services/check_smtp -H $service[hostname]`;

list($check, $time) = split("\|", $check);

if(strstr($check, "SMTP OK")) { 
  $status = '1'; 
} else { 
  $status = '0'; 
}

?>
