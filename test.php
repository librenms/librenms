#!/usr/bin/php

<?php

include("config.php");
include("includes/functions.php");

$query = mysql_query("SELECT * FROM devices WHERE device_id = '8'");

$array = mysql_fetch_array($query);

echo($array[1] . "\n");

mysql_query("UPDATE `devices` SET `hostname` = 'sotsci-fw-office01.vostron.net' WHERE `device_id` = '8'");

echo(mysql_affected_rows() . " rows changed\n");

$query = mysql_query("SELECT * FROM devices WHERE device_id = '8'");

$array = mysql_fetch_array($query);

echo($array[1] . "\n");




?>
