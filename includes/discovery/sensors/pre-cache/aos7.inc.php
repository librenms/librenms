<?php
echo 'ChasControlModuleEntry';
$pre_cache['aos7_sync_oids'] = snmpwalk_cache_multi_oid_nobulk($device, 'ChasControlModuleEntry',[], 'ALCATEL-IND1-CHASSIS-MIB', 'nokia/aos7');
