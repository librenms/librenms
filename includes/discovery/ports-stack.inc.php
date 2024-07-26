<?php

$sql = "SELECT * FROM `ports_stack` WHERE `device_id` = '" . $device['device_id'] . "'";

$stack_db_array = [];
foreach (dbFetchRows($sql) as $entry) {
    $stack_db_array[$entry['port_id_high']][$entry['port_id_low']]['ifStackStatus'] = $entry['ifStackStatus'];
}
unset(
    $sql,
    $entry
);

$stack_poll_array = snmpwalk_cache_twopart_oid($device, 'ifStackStatus', [], 'IF-MIB');

foreach ($stack_poll_array as $port_id_high => $entry_high) {
    foreach ($entry_high as $port_id_low => $entry_low) {
        $ifStackStatus = $entry_low['ifStackStatus'];
        if (isset($stack_db_array[$port_id_high][$port_id_low])) {
            if ($stack_db_array[$port_id_high][$port_id_low]['ifStackStatus'] == $ifStackStatus) {
                echo '.';
            } else {
                dbUpdate(['ifStackStatus' => $ifStackStatus], 'ports_stack', 'device_id=? AND port_id_high=? AND `port_id_low`=?', [$device['device_id'], $port_id_high, $port_id_low]);
                echo 'U';
            }

            unset($stack_db_array[$port_id_high][$port_id_low]);
        } else {
            dbInsert(['device_id' => $device['device_id'], 'port_id_high' => $port_id_high, 'port_id_low' => $port_id_low, 'ifStackStatus' => $ifStackStatus], 'ports_stack');
            echo '+';
        }
    }//end foreach
    unset(
        $port_id_low,
        $entry_low
    );
}//end foreach
unset($stack_poll_array);

foreach ($stack_db_array as $port_id_high => $array) {
    foreach ($array as $port_id_low => $blah) {
        echo $device['device_id'] . ' ' . $port_id_low . ' ' . $port_id_high . "\n";
        dbDelete('ports_stack', '`device_id` =  ? AND port_id_high = ? AND port_id_low = ?', [$device['device_id'], $port_id_high, $port_id_low]);
        echo '-';
    }
}

echo "\n";
unset(
    $stack_db_array,
    $array,
    $port_id_high,
    $entry_high
);
