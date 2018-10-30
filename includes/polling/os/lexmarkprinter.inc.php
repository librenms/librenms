<?php

preg_match('/^Lexmark ([a-zA-Z0-9]+) version/', $device['sysDescr'], $result);
$hardware = $result[1];
