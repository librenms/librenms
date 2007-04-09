#!/usr/bin/php

<?php

include("config.php");

$data = `snmptable -Ov -v2c -c v05tr0n82 sotsci-sw01 ifTable`;

$data = trim(preg_replace("/(\ +)/", " ", $data));

echo("$data");


?>
