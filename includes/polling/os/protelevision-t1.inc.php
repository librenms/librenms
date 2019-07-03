<?php
$protelevision_tmp = snmp_get_multi_oid($device, 'pt3080SystemInstrumentType.0 pt3080SystemInstrumentSWRev.0 pt3080SystemInstrumentKU.0', '-OUQs', 'PT3080-MIB');
$hardware = $protelevision_tmp['pt3080SystemInstrumentType.0'];
$version  = $protelevision_tmp['pt3080SystemInstrumentSWRev.0'];
$serial   = $protelevision_tmp['pt3080SystemInstrumentKU.0'];
