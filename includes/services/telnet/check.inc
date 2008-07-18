<?php

if($service[service_port]) { $port = $service[service_port]; } else { $port = '23'; }


$check = `scripts/services/check_tcp -H $service[hostname] -p $port`;

list($check, $time) = split("\|", $check);

if(strstr($check, "TCP OK - ")) { 
  $status = '1'; 
} else { 
  $status = '0'; 
}

?>
