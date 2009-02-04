<?php

if($_SESSION[userlevel] >= "5" && is_file($config['rancid_configs'] . $device['hostname'])) {

  $file = $config['rancid_configs'] . $device['hostname'];
  $fh = fopen($file, 'r') or die("Can't open file");
  echo(highlight_string(fread($fh, filesize($file))));
  fclose($fh);

} else {

  print_error("Error : Insufficient access.");

}

?>
