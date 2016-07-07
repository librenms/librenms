<?php

 $hardware = snmp_get($device, '.1.3.6.1.4.1.244.1.1.6.28.0', '-Ovqs', ''); // LANTRONIX-SLC-MIB::slcSystemModelString.0
 $hardware = str_replace('"', '', $hardware);
 $version = snmp_get($device, '.1.3.6.1.4.1.244.1.1.6.3.0', '-Ovqs', ''); // LANTRONIX-SLC-MIB::slcSystemFWRev.0
 $version = str_replace('"', '', $version);
 $serial = snmp_get($device, '.1.3.6.1.4.1.244.1.1.6.2.0', '-Ovqs', ''); // LANTRONIX-SLC-MIB::slcSystemSerialNo.0
 $serial = str_replace('"', '', $serial);
 $features       = '';
