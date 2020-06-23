<?php

preg_match('/^(.*) Device, Compiled /', $device['sysDescr'], $matches);
$hardware = $matches[1];

preg_match('/^ SoftWare Version (?:' . $hardware . '_)?(.*)$/m', $device['sysDescr'], $matches);
$version = $matches[1];

preg_match('/^ (?: Serial No\.:|Device serial number )(.*)$/m', $device['sysDescr'], $matches);
$serial = $matches[1];
