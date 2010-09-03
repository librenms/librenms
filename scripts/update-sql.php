#!/usr/bin/env php
<?php

include("config.php");
include("includes/functions.php");

$data = trim(shell_exec("cat ".$argv[1]));

foreach( explode("\n", $data) as $line) {
  $update = mysql_query($line);
  echo("$line \n");
}
?>
