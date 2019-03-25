<?php
//$orvaldi_data = snmp_get_multi_oid($device, ['.1.3.6.1.4.1.21111.1.1.1.4.0', '.1.3.6.1.4.1.21111.1.1.1.6.0', '.1.3.6.1.4.1.21111.1.1.1.3.0']);
//$version      = $orvaldi_data['.1.3.6.1.4.1.21111.1.1.1.6.0'];
//$serial       = $orvaldi_data['.1.3.6.1.4.1.21111.1.1.1.4.0'];
//$hardware     = $orvaldi_data['.1.3.6.1.4.1.21111.1.1.1.3.0'];

$orvaldi_data = snmp_get_multi_oid($device, ['upsIdSerialNumber.0', 'upsIdFWVersion.0', 'upsIdModelName.0'], '-OUQs', 'companyMIB');
$version      = $orvaldi_data['upsIdFWVersion.0'];
$serial       = $orvaldi_data['upsIdSerialNumber.0'];
$hardware     = $orvaldi_data['upsIdModelName.0'];
