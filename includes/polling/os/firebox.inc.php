<?php

preg_match('/v(.*)/', $device['sysDescr'], $matches);

$version = (isset($matches[1]) ? $matches[1] : '');
// $hardware = "Still need to figger hardware out!";
// $serial = "Still need to figger serial out!";
// $features = "Still need to figger features out!";
