<?php
$orvaldi_data = snmp_get_multi_oid($device, ['upsIdSerialNumber.0', 'upsIdFWVersion.0', 'upsIdModelName.0'], '-OUQs', 'companyMIB');
$version      = $orvaldi_data['upsIdFWVersion.0'];
$serial       = $orvaldi_data['upsIdSerialNumber.0'];
$hardware     = $orvaldi_data['upsIdModelName.0'];
