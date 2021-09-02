<?php

[$version] = explode(',', $device['sysDescr']);

preg_match('/Version V(\S+) (.+) Software,/', $device['sysDescr'], $matches);

$hardware = $matches[2];
