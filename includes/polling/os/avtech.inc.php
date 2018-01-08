<?php
$lastSpace = strrpos($device['sysDescr'], ' ');

$hardware = trim(substr($device['sysDescr'], 0, $lastSpace));
$version = trim(substr($device['sysDescr'], $lastSpace));
