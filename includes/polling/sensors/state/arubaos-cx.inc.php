<?php

$stateLookupTable = [
    // arubaWiredVsfv2OperStatus
    'no_split' => 0,
    'fragment_active' => 1,
    'fragment_inactive' => 2,

    //arubaWiredVsfv2MemberTable
    'not_present' => 10,
    'booting' => 11,
    'ready' => 12,
    'version_mismatch' => 13,
    'communication_failure' => 14,
    'in_other_fragment' => 15,
];

if ($sensor['sensor_type'] === 'arubaWiredVsfv2OperStatus' || $sensor['sensor_type'] === 'arubaWiredVsfv2MemberTable') {
    $sensor_value = $stateLookupTable[$sensor_value];
}
