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
$descriptions = [];
foreach (SnmpQuery::walk('.1.3.6.1.4.1.45555.3.2.1.10')->values() as $oid => $descr) {
    $descriptions[\Illuminate\Support\Str::afterLast($oid, '.')] = $descr;
}
$stored = array_column($ports, 'ifAlias', 'ifIndex');

// Only touch ports that were polled: an entry with just ifAlias gets the port
// marked deleted (selected port polling). A port missing from the vendor table,
// for example after a partial walk, keeps its stored description.
foreach ($port_stats as $index => $stats) {
    if (array_key_exists($index, $descriptions)) {
        $port_stats[$index]['ifAlias'] = $descriptions[$index];
    } elseif (isset($stored[$index])) {
        $port_stats[$index]['ifAlias'] = $stored[$index];
    }
}
