<?php

$hrDevice_oids = array(
    'hrDeviceEntry',
    'hrProcessorEntry',
);
d_echo($hrDevices);

$hrDevices = array();
foreach ($hrDevice_oids as $oid) {
    $hrDevices = snmpwalk_cache_oid($device, $oid, $hrDevices, 'HOST-RESOURCES-MIB:HOST-RESOURCES-TYPES');
}

d_echo($hrDevices);

if (is_array($hrDevices)) {
    foreach ($hrDevices as $hrDevice) {
        if (is_array($hrDevice) && is_numeric($hrDevice['hrDeviceIndex'])) {
            if (dbFetchCell('SELECT COUNT(*) FROM `hrDevice` WHERE device_id = ? AND hrDeviceIndex = ?', array($device['device_id'], $hrDevice['hrDeviceIndex']))) {
                $update_array = array(
                    'hrDeviceType'   => mres($hrDevice['hrDeviceType']),
                    'hrDeviceDescr'  => mres($hrDevice['hrDeviceDescr']),
                    'hrDeviceStatus' => mres($hrDevice['hrDeviceStatus']),
                    'hrDeviceErrors' => mres($hrDevice['hrDeviceErrors']),
                );
                if ($hrDevice['hrDeviceType'] == 'hrDeviceProcessor') {
                    $update_array['hrProcessorLoad'] = mres($hrDevice['hrProcessorLoad']);
                }

                dbUpdate($update_array, 'hrDevice', 'device_id=? AND hrDeviceIndex=?', array($device['device_id'], $hrDevice['hrDeviceIndex']));
                echo '.';
            } else {
                $inserted_rows = dbInsert(array('hrDeviceIndex' => mres($hrDevice['hrDeviceIndex']), 'device_id' => mres($device['device_id']), 'hrDeviceType' => mres($hrDevice['hrDeviceType']), 'hrDeviceDescr' => mres($hrDevice['hrDeviceDescr']), 'hrDeviceStatus' => mres($hrDevice['hrDeviceStatus']), 'hrDeviceErrors' => (int) mres($hrDevice['hrDeviceErrors'])), 'hrDevice');
                echo '+';
                d_echo($hrDevice);
                d_echo("$inserted_rows row inserted");
            }//end if

            $valid_hrDevice[$hrDevice['hrDeviceIndex']] = 1;
        }//end if
    }//end foreach
}//end if

$sql = "SELECT * FROM `hrDevice` WHERE `device_id`  = '".$device['device_id']."'";

foreach (dbFetchRows($sql) as $test_hrDevice) {
    if (!$valid_hrDevice[$test_hrDevice['hrDeviceIndex']]) {
        echo '-';
        dbDelete('hrDevice', '`hrDevice_id` = ?', array($test_hrDevice['hrDevice_id']));
        d_echo($test_hrDevice);
    }
}

unset($valid_hrDevice);
echo "\n";
