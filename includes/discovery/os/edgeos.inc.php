<?php

$items = array(
    'EdgeOS',
    'EdgeRouter',
);

if (starts_with($sysDescr, $items)) {
    $os = 'edgeos';
}
