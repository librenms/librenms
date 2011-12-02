<?php

list($version) = explode(',', $poll_device['sysDescr']);

preg_match('/Version V(\S+) (.+) Software,/', $poll_device['sysDescr'], $matches);

$hardware = $matches[2];

?>
