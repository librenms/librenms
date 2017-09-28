<?php

echo 'AVTECH: ';
if (ends_with($device['sysObjectID'], '.20916.1.9')) {
    //  RoomAlert 3E
    $device_oid = '.1.3.6.1.4.1.20916.1.9.';

    $switch = array(
        'id'        => 0,
        'type'      => 'switch',
        'oid'       => $device_oid.'1.2.1.0',
        'descr_oid' => $device_oid.'1.2.2.0',
    );
    avtech_add_sensor($device, $switch, $valid);
} elseif (ends_with($device['sysObjectID'], '.20916.1.6')) {
    //  RoomAlert 4E
    $device_oid = '.1.3.6.1.4.1.20916.1.6.';

    $switch = array(
        'id'        => 0,
        'type'      => 'switch',
        'oid'       => $device_oid.'1.3.1.0',
        'descr_oid' => $device_oid.'1.3.2.0',
    );
    avtech_add_sensor($device, $switch, $valid);
}
