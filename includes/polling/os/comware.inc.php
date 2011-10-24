<?php

# SNMPv2-MIB::sysDescr.0 = STRING: Hangzhou H3C Comware Platform Software, Software Version 3.10, Release 2211P06
# H3C S3100-8TP-EI
# Copyright(c) 2004-2010 Hangzhou H3C Tech. Co.,Ltd. All rights reserved.
# SNMPv2-MIB::sysObjectID.0 = OID: HH3C-PRODUCT-ID-MIB::hh3c-S3100-8TP-EI

echo("Comware OS...");


$hardware = snmp_get($device, "sysObjectID.0", "-OQsv", "SNMPv2-MIB:HH3C-PRODUCT-ID-MIB", "+".$config['install_dir']."/mibs/h3c");

list(,$version,$features) = explode(",", $poll_device['sysDescr']);
list(,,,$version) = explode(" ", $version);
list(,,$features) = explode(" ", $features);

?>
