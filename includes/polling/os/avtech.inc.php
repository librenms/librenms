<?php
$lastSpace = strrpos($poll_device['sysDescr'], ' ');

$hardware = trim(substr($poll_device['sysDescr'], 0, $lastSpace));
$version = trim(substr($poll_device['sysDescr'], $lastSpace));
