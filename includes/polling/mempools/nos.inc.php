<?php

// Somewhat of an uggly hack since NOS doesn't support fetching total memory of the device over SNMP
// Given OID returns usage in percent so we set total to 100 in order to get a proper graph
$mempool['total']   = "100";
$mempool['used']    = snmp_get($device, "1.3.6.1.4.1.1588.2.1.1.1.26.6.0", "-Ovq");
$mempool['free']    = ($mempool['total'] - $mempool['used']);
