<?php
/*
 * LibreNMS
 */
$pre_cache['timos_oids'] = snmpwalk_cache_multi_oid($device, 'tmnxDigitalDiagMonitorEntry', [], 'TIMETRA-PORT-MIB', 'timos');
