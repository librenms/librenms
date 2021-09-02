<?php

if ($device['sysObjectID'] == '.1.3.6.1.4.1.23128.1000.1.1') {
    $features = 'Enterprise Security Manager';
} elseif ($device['sysObjectID'] == '.1.3.6.1.4.1.23128.1000.3.1') {
    $features = 'Event Receiver';
} elseif ($device['sysObjectID'] == '.1.3.6.1.4.1.23128.1000.7.1') {
    $features = 'Enterprise Log Manager';
} elseif ($device['sysObjectID'] == '.1.3.6.1.4.1.23128.1000.11.1') {
    $features = 'Advanced Correlation Engine';
} else {
    $features = 'Unknown';
}

// McAfee ACE 9.5.0
if (preg_match('/^McAfee [A-Z]{3} ([^,]+)$/', $device['sysDescr'], $regexp_result)) {
    $version = $regexp_result[1];
}
