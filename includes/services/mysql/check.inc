<?php

$check = `checkers/check_mysql -H $service[hostname] $service[service_param]`;

echo($check);

list($check, $time) = split("\|", $check);

if(strstr($check, "Uptime:")) { 
  $status = '1'; 
} else { 
  $status = '0'; 
}

?>
