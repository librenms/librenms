<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2014 Neil Lathwood <https://github.com/laf/ http://www.lathwood.co.uk/fa>
 * Modyfied by Peter TKATCHENKO https://github.com/Peter2121/ 2016
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

    $oids     = snmp_walk($device, 'virtualDiskDeviceName', '-Oesqn', 'StorageManagement-MIB');
    $name_oid = '.1.3.6.1.4.1.674.10893.1.20.140.1.1.3';
    $main_oid = '.1.3.6.1.4.1.674.10893.1.20.140.1.1.4.';
    d_echo($oids."\n");

    $oids = trim($oids);
    if ($oids) {
        echo 'Dell ';

        $state_name = 'dellVirtualDiskState';
        $state_index_id = create_state_index($state_name);
/*
-- 1.3.6.1.4.1.674.10893.1.20.140.1.1.4
virtualDiskState
INTEGER
	{
	unknown(0),
	ready(1),
	failed(2),
	online(3),
	offline(4),
	degraded(6),
	verifying(7),
	resynching(15),
	regenerating(16),
	failedRedundancy(18),
	rebuilding(24),
	formatting(26),
	reconstructing(32),
	initializing(35),
	backgroundInit(36),
	permanentlyDegraded(52)
	}
*/
        if ($state_index_id) {
            $states = array(
                 array($state_index_id,'unknown',0,0,3) ,
                 array($state_index_id,'ready',1,1,0) ,
                 array($state_index_id,'failed',1,2,2) ,
                 array($state_index_id,'online',1,3,1) ,
                 array($state_index_id,'offline',1,4,2) ,
                 array($state_index_id,'degraded',1,6,2) ,
                 array($state_index_id,'verifying',1,7,1) ,
                 array($state_index_id,'resynching',1,15,1) ,
                 array($state_index_id,'regenerating',1,16,1) ,
                 array($state_index_id,'failedRedundancy',1,18,2) ,
                 array($state_index_id,'rebuilding',1,24,1) ,
                 array($state_index_id,'formatting',1,26,1) ,
                 array($state_index_id,'reconstructing',1,32,1) ,
                 array($state_index_id,'initializing',1,35,1) ,
                 array($state_index_id,'backgroundInit',1,36,1) ,
                 array($state_index_id,'permanentlyDegraded',1,52,2)
             );

            foreach($states as $value){ 
                $insert = array(
                    'state_index_id' => $value[0],
                    'state_descr' => $value[1],
                    'state_draw_graph' => $value[2],
                    'state_value' => $value[3],
                    'state_generic_value' => $value[4]
                );
                dbInsert($insert, 'state_translations');
            }
        }

        foreach (explode("\n", $oids) as $data) {
            list($oid,$name) = explode(' ', $data, 2);
            $name = trim($name,"\"");
            if($oid==$name_oid) continue; // Something goes wrong, we should have $name_oid.$num_index in $oid
            $split_oid        = explode('.', $oid);
            $num_index        = $split_oid[(count($split_oid) - 1)];
            $index            = (int)$num_index+0;
            $oid              = $main_oid.$num_index;
            $low_limit        = 0.5;
            $high_limit       = 1.5;

            $state_current = snmp_get($device, $oid, '-Oevq');
            discover_sensor($valid['sensor'], 'state', $device, $oid, $index, $state_name, $name, '1', '1', $low_limit, $low_limit, $high_limit, $high_limit, $state_current,'snmp',$index);
            create_sensor_to_state_index($device, $state_name, $index);
        }
    }
