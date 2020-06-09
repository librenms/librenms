<?php
 $imco_tmp = snmp_get_multi_oid($device, ['imco3IdentModel.0','imPM1SystemIDserNumb.0', 'imco3IdentSwVersion.0'], '-OUQs', 'IMCO-BIG-MIB');
 $hardware = $imco_tmp['imco3IdentModel.0'];
 $serial   = $imco_tmp['imPM1SystemIDserNumb.0'];
 $version  = $imco_tmp['imco3IdentSwVersion.0'];
