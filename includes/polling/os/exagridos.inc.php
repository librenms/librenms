<?php

preg_match('/^ExaGrid (?<hardware>[^,]*), (?<version>.*)$/', $device['sysDescr'], $data);
$version = $data[2];
$hardware = $data[1];
