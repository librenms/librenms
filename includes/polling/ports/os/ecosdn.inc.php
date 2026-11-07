<?php

/*
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

// RDP EcoNPB leaves IF-MIB::ifAlias empty. Port descriptions set in the CLI
// are only exposed in the vendor portInfoTable (description column),
// which is indexed by ifIndex.
$descriptions = SnmpQuery::walk('.1.3.6.1.4.1.45555.3.2.1.10')->values();

if (empty($descriptions)) {
    // The vendor table did not answer: keep the stored descriptions instead
    // of blanking every port and logging a change for each of them.
    foreach ($ports as $port) {
        if (isset($port_stats[$port['ifIndex']])) {
            $port_stats[$port['ifIndex']]['ifAlias'] = $port['ifAlias'];
        }
    }

    return;
}

foreach ($descriptions as $oid => $descr) {
    $index = \Illuminate\Support\Str::afterLast($oid, '.');
    // Skip ports that were not polled (selected port polling), otherwise an
    // entry with only ifAlias gets the port marked deleted.
    if ($descr && isset($port_stats[$index])) {
        $port_stats[$index]['ifAlias'] = $descr;
    }
}
