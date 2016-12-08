<?php

preg_match('/^Lexmark ([a-zA-Z0-9]+) version/', $poll_device['sysDescr'], $result);
$hardware = $result[1];
