<?php
$tmp_panasonic = preg_split('/[\s]+/', $device['sysDescr']);
$hardware = $tmp_panasonic[0];
$version  = $tmp_panasonic[1];
unset($tmp_panasonic);
