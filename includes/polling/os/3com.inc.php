<?php

if (strpos($device['sysDescr'], 'Software')) {
    $hardware = str_replace("3Com ", '', substr($device['sysDescr'], 0, strpos($device['sysDescr'], 'Software')));
    // Version is the last word in the sysDescr's first line
    list($version) = explode("\n", substr($device['sysDescr'], (strpos($device['sysDescr'], 'Version') + 8)));
} else {
    $hardware = str_replace("3Com ", '', $device['sysDescr']);
    $version='';
}
