<?php

/*
BDCOM(tm) S2524C Software, Version 2.1.0A Build 5721
Compiled: 2011-11-1 15:57:26 by SYS
ROM: System Bootstrap,Version 0.3.2,Serial num:27072980
*/

preg_match('/BDCOM\(tm\) ([A-Z0-9]+) Software, Version (.*)\nCompiled: (.*)\n(.*),Serial num:([0-9]+)/', $device['sysDescr'], $matches);

$hardware = $matches['1'];
$version = $matches['2'];
$serial = $matches['5'];
