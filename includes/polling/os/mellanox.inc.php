<?php

if (stristr($poll_device['sysDescr'], "Linux")) {
    list(,,$version,$hardware) = explode(' ', $poll_device['sysDescr']);
} else {
    list($hardware,,$version) = explode(',', $poll_device['sysDescr']);
    $hardware = preg_replace("/Mellanox /", "", $hardware);
}
