<?php

// Nokia ISAM has ports that can be accessed in other SNMP context.
// IHUB contains the ports of the NT cards ans backplane ports from NT to line cards
SnmpQuery::context('ihub')->hideMib()->walk([
    'IF-MIB::ifDescr',
    'IF-MIB::ifName',
    'IF-MIB::ifAlias',
    'IF-MIB::ifType',
    'IF-MIB::ifOperStatus',
])->table(1, $port_stats);
