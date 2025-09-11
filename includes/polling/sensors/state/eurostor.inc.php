<?php

$stateLookupTable = [
    'RaidSet Member' => 1,
    'Hot Spare' => 2,
    'Failed' => 3,
];

$sensor_value = $stateLookupTable[$sensor_value];
