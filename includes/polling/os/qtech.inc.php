<?php

preg_match('/^ SoftWare Version (.*)$/m', $device['sysDescr'], $matches);
$version = $matches[1];

preg_match('/^ HardWare Version (.*)$/m', $device['sysDescr'], $matches);
$hardware = $matches[1];

preg_match('/^  Serial No\.:(.*)$/m', $device['sysDescr'], $matches);
$serial = $matches[1];
