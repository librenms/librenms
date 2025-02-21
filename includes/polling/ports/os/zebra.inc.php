<?php

// Quick hack against fluctuating ifSpeed on every polling time
foreach ($port_stats as $index => $port) {
    $port_stats[$index]['ifSpeed'] = 100000000;
}
