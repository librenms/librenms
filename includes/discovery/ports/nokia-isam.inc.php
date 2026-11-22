<?php

// Nokia ISAM has ports that can be accessed in other SNMP context.
// IHUB contains the ports of the NT cards ans backplane ports from NT to line cards
// SHUB contains the NT ports of SHUB based NTs (e.g. 7356 NFXR-A)
foreach (['ihub', 'shub'] as $context) {
    $response = SnmpQuery::context($context)->hideMib()->enumStrings()->abortOnFailure()->walk([
        'IF-MIB::ifDescr',
        'IF-MIB::ifName',
        'IF-MIB::ifAlias',
        'IF-MIB::ifType',
        'IF-MIB::ifOperStatus',
    ]);
    $response->table(1, $port_stats);
    if ($response->isValid()) {
        break;
    }
}

unset($response, $context);
