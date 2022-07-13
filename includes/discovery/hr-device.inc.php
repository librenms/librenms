<?php

$hrDevice_oids = [
    'hrDeviceEntry',
    'hrProcessorEntry',
];
d_echo($hrDevices);

$hrDevices = [];
foreach ($hrDevice_oids as $oid) {
    $hrDevices = snmpwalk_cache_oid($device, $oid, $hrDevices, 'HOST-RESOURCES-MIB:HOST-RESOURCES-TYPES');
}

d_echo($hrDevices);

if (is_array($hrDevices)) {
    foreach ($hrDevices as $hrDevice) {
        if (is_array($hrDevice) && is_numeric($hrDevice['hrDeviceIndex'])) {
            if (dbFetchCell('SELECT COUNT(*) FROM `hrDevice` WHERE device_id = ? AND hrDeviceIndex = ?', [$device['device_id'], $hrDevice['hrDeviceIndex']])) {
                $update_array = [
                    'hrDeviceType'   => $hrDevice['hrDeviceType'],
                    'hrDeviceDescr'  => $hrDevice['hrDeviceDescr'],
                    'hrDeviceStatus' => $hrDevice['hrDeviceStatus'],
                    'hrDeviceErrors' => $hrDevice['hrDeviceErrors'],
                ];
                if ($hrDevice['hrDeviceType'] == 'hrDeviceProcessor') {
                    $update_array['hrProcessorLoad'] = $hrDevice['hrProcessorLoad'];
                }

                dbUpdate($update_array, 'hrDevice', 'device_id=? AND hrDeviceIndex=?', [$device['device_id'], $hrDevice['hrDeviceIndex']]);
                echo '.';
            } else {
                $inserted_rows = dbInsert(['hrDeviceIndex' => $hrDevice['hrDeviceIndex'], 'device_id' => $device['device_id'], 'hrDeviceType' => $hrDevice['hrDeviceType'], 'hrDeviceDescr' => $hrDevice['hrDeviceDescr'], 'hrDeviceStatus' => $hrDevice['hrDeviceStatus'], 'hrDeviceErrors' => (int) $hrDevice['hrDeviceErrors']], 'hrDevice');
                echo '+';
                d_echo($hrDevice);
                d_echo("$inserted_rows row inserted");
            }//end if

            $valid_hrDevice[$hrDevice['hrDeviceIndex']] = 1;
        }//end if
    }//end foreach
}//end if

$sql = "SELECT * FROM `hrDevice` WHERE `device_id`  = '" . $device['device_id'] . "'";

foreach (dbFetchRows($sql) as $test_hrDevice) {
    if (! $valid_hrDevice[$test_hrDevice['hrDeviceIndex']]) {
        echo '-';
        dbDelete('hrDevice', '`hrDevice_id` = ?', [$test_hrDevice['hrDevice_id']]);
        d_echo($test_hrDevice);
    }
}

unset($valid_hrDevice);
echo "\n";
