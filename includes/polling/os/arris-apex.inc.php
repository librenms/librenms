<?php
$arrisapex_data = snmp_get_multi_oid($device, ['.1.3.6.1.4.1.1166.7.1.7.0', '.1.3.6.1.4.1.1166.7.1.1.0', '.1.3.6.1.4.1.1166.7.1.15.0']);
$version      = $arrisapex_data['.1.3.6.1.4.1.1166.7.1.7.0'];
$serial       = $arrisapex_data['.1.3.6.1.4.1.1166.7.1.1.0'];
$hardware     = $arrisapex_data['.1.3.6.1.4.1.1166.7.1.15.0'];
