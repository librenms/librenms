<?php
/**
 * boss.inc.php
 *
 * LibreNMS Fan and Power Supply state Discovery module for Extreme/Avaya ERS
 */
if ($device['os'] === 'boss') {
    $oid = snmpwalk_cache_oid($device, 's5ChasComTable', [], 'S5-CHASSIS-MIB');
    $cur_oid = '.1.3.6.1.4.1.45.1.6.3.3.1.1.10.';

    if (is_array($oid)) {
        //get states
        $state_name = 's5ChasComOperState';
        $states = [
            ['value' => 1, 'generic' => 3, 'graph' => 0, 'descr' => 'other'],
            ['value' => 2, 'generic' => 3, 'graph' => 0, 'descr' => 'notAvail'],
            ['value' => 3, 'generic' => 3, 'graph' => 0, 'descr' => 'removed'],
            ['value' => 4, 'generic' => 3, 'graph' => 0, 'descr' => 'disabled'],
            ['value' => 5, 'generic' => 0, 'graph' => 0, 'descr' => 'normal'],
            ['value' => 6, 'generic' => 1, 'graph' => 0, 'descr' => 'resetInProg'],
            ['value' => 7, 'generic' => 1, 'graph' => 0, 'descr' => 'testing'],
            ['value' => 8, 'generic' => 1, 'graph' => 0, 'descr' => 'warning'],
            ['value' => 9, 'generic' => 1, 'graph' => 0, 'descr' => 'nonFatalErr'],
            ['value' => 10, 'generic' => 2, 'graph' => 0, 'descr' => 'fatalErr'],
            ['value' => 11, 'generic' => 3, 'graph' => 0, 'descr' => 'notConfig'],
            ['value' => 12, 'generic' => 3, 'graph' => 0, 'descr' => 'obsoleted'],
        ];
        create_state_index($state_name, $states);

        $ers_sensors = [];
        foreach ($oid as $key => $value) {
            if ($value['s5ChasComGrpIndx'] == 4 || $value['s5ChasComGrpIndx'] == 5 || $value['s5ChasComGrpIndx'] == 6) {
                $ers_sensors[$key] = $value;
            }
        }

        $ps_num = 0;
        foreach ($ers_sensors as $index => $entry) {
            //Get unit number
            $unit_array = explode('.', $index);
            $unit = floor($unit_array[1] / 10);
            //Set description with Power Supply number
            if ($unit_array[0] == 4) {
                if ($unit != $temp_unit) {
                    $ps_num = 1;
                } else {
                    $ps_num++;
                }
                $descr = "BOSS Unit $unit: Power Supply $ps_num";
            } else {
                $descr = "BOSS Unit $unit: $entry[s5ChasComDescr]";
            }
            //Discover Sensors
            discover_sensor($valid['sensor'], 'state', $device, $cur_oid . $index, "s5ChasComOperState.$index", $state_name, $descr, 1, 1, null, null, null, null, $entry['s5ChasComOperState']);
            //Create Sensor To State Index
            create_sensor_to_state_index($device, $state_name, "s5ChasComOperState.$index");
            $temp_unit = $unit;
        }
    }
}
