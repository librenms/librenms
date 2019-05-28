<?php
//Collect Software Version
$huaweismu_tmp = snmp_get_multi_oid($device, 'hwMonEquipSoftwareVersion.1', '-OQUs', 'HUAWEI-SITE-MONITOR-MIB');
$version  = $huaweismu_tmp['hwMonEquipSoftwareVersion.1'];
