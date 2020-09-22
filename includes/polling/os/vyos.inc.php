<?php

// Remove Vyatta prefix
$device['sysDescr'] = str_replace('Vyatta ', '', $device['sysDescr']);

// Version is second remaining word in sysDescr
[,$version] = explode(' ', $device['sysDescr']);

$features = '';
