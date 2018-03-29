<?php

echo "Cisco WLC";

$total = str_replace('"', "", snmp_get($device, "1.3.6.1.4.1.14179.1.1.5.2.0", '-OvQ'));
$avail = str_replace('"', "", snmp_get($device, "1.3.6.1.4.1.14179.1.1.5.3.0", '-OvQ'));

$mempool['total'] = ($total * 1024);
$mempool['free']  = ($avail * 1024);
$mempool['used']  = (($total - $avail) * 1024);
