<?php

preg_match('/^ExaGrid ([^,]*), (.*)$/', $device['sysDescr'], $data);
$version = $data[2];
$hardware = $data[1];
