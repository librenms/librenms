<?php

$check = `checkers/check_http -H $service[hostname]`;

list($check, $time) = split("\|", $check);

if(strstr($check, "HTTP OK")) { 
  $status = '1'; 
} else { 
  $status = '0'; 
}

?>
