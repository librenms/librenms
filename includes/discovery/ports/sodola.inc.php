<?php

/**
 * Sodola / Letscom: many units put the user description in ifDescr and the canonical name in
 * ifName. Prefer ifName for display (os ifname: true) and copy the description to ifAlias
 * when the device left ifAlias empty, so the ports list shows both.
 */
foreach ($port_stats as $ifIndex => &$row) {
    if (! is_array($row)) {
        continue;
    }

    $ifName = trim((string) ($row['ifName'] ?? ''));
    $ifDescr = trim((string) ($row['ifDescr'] ?? ''));
    $ifAlias = trim((string) ($row['ifAlias'] ?? ''));
    if ($ifAlias !== '' || $ifDescr === '' || $ifName === '' || $ifName === $ifDescr) {
        continue;
    }

    if (preg_match('/^(?:vlan|lo)\\d*$/i', $ifName)) {
        continue;
    }

    if (preg_match('/^(?:[gxt]e|fg|eth)\\d+\\/\\d+(?:\\/\\d+)?$/i', $ifName)) {
        $row['ifAlias'] = \LibreNMS\Util\StringHelpers::inferEncoding($ifDescr);
    }
}
unset($row);
