<?php

// Remove Vyatta prefix
$device['sysDescr'] = str_replace('Vyatta ', '', $device['sysDescr']);

// Version is second remaining word in sysDescr
list(,$version) = explode(' ', $device['sysDescr']);

$features = '';
