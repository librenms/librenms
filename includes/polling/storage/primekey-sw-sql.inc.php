<?php
$sql_oids = snmp_get_multi_oid($device, ['pk-SAV2-internal-databaseAvailableStorage.0', 'pk-SAV2-internal-databaseTotalStorage.0'], '-OUQn', 'PK-SOFTWARE-APPLIANCE-V2');
$storage['free'] = $sql_oids['.1.3.6.1.4.1.22408.1.4.1.3.1.2.0'];
$storage['size'] = $sql_oids['.1.3.6.1.4.1.22408.1.4.1.3.1.3.0'];
$storage['used'] = $storage['size'] - $storage['free'];
$storage['units'] = 1024;
unset($sql_oids);