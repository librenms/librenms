<?php

echo("Doing Allied Telesyn AlliedWare ");

$serial = "";
list(,$hardware,) = explode(" ", $hardware);
$hardware = $sysDescr;

$features = "";

echo("$hardware - $version - $features - $serial\n");

include("hr-mib.inc.php");

?>
