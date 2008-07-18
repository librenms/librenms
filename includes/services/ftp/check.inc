<?php

$check = `scripts/services/check_ftp -H $service[hostname]`;

list($check, $time) = split("\|", $check);

if(strstr($check, "FTP OK - ")) { 
  $status = '1'; 
} else { 
  $status = '0'; 
}

?>
