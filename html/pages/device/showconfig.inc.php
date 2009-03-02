<?php

if($_SESSION[userlevel] >= "5" && is_file($config['rancid_configs'] . $device['hostname'])) {
  $file = $config['rancid_configs'] . $device['hostname'];
  $fh = fopen($file, 'r') or die("Can't open file");
  $text = fread($fh, filesize($file));
  echo(highlight_string($text));
  fclose($fh);
} else {
  print_error("Error : Insufficient access.");
}


?>
