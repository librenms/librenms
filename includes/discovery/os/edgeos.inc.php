<?php

$items = array(
    'EdgeOS',
    'EdgeRouter Lite',
);

if (starts_with($sysDescr, $items)) {
    $os = 'edgeos';
}
