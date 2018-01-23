<?php

if (stristr($device['sysDescr'], "Linux")) {
    list(,,$version,$hardware) = explode(' ', $device['sysDescr']);
} else {
    list($hardware,,$version) = explode(',', $device['sysDescr']);
    $hardware = preg_replace("/Mellanox /", "", $hardware);
}
