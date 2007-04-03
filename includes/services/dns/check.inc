<?php

$check = `checkers/check_dns -H $service[param] -s $service[hostname]`;

list($check, $time) = split("\|", $check);

if(strstr($check, "DNS OK: ")) { 
  $status = '1'; 
} else { 
  $status = '0'; 
}

?>
