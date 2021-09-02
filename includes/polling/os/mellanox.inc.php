<?php

if (stristr($device['sysDescr'], 'Linux')) {
    [,,$version,$hardware] = explode(' ', $device['sysDescr']);
} else {
    [$hardware,,$version] = explode(',', $device['sysDescr']);
    $hardware = preg_replace('/Mellanox /', '', $hardware);
}
