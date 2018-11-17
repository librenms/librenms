<?php
/**
 * eltek-webpower.inc.php
 *
 * -Description-
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2018 Mikael Sipilainen
 * @author     Mikael Sipilainen <mikael.sipilainen@gmail.com>
 */

// Battery banks symmetry status discovery
$symmetry_oid = array('.1.3.6.1.4.1.12148.9.3.19.3.1.3.0',
                      '.1.3.6.1.4.1.12148.9.3.19.3.1.6.0',
                      '.1.3.6.1.4.1.12148.9.3.19.3.1.9.0',
                      '.1.3.6.1.4.1.12148.9.3.19.3.1.12.0',
                      '.1.3.6.1.4.1.12148.9.3.19.3.1.15.0',
                      '.1.3.6.1.4.1.12148.9.3.19.3.1.18.0',
                      '.1.3.6.1.4.1.12148.9.3.19.3.1.21.0',
                      '.1.3.6.1.4.1.12148.9.3.19.3.1.24.0'
);
$oid = snmp_get_multi($device, 'batteryBanksSymmetry1enable.0 batteryBanksSymmetry2enable.0 batteryBanksSymmetry3enable.0 batteryBanksSymmetry4enable.0 batteryBanksSymmetry5enable.0 batteryBanksSymmetry6enable.0 batteryBanksSymmetry7enable.0 batteryBanksSymmetry8enable.0 batteryBanksSymmetry1status.0 batteryBanksSymmetry2status.0 batteryBanksSymmetry3status.0 batteryBanksSymmetry4status.0 batteryBanksSymmetry5status.0 batteryBanksSymmetry6status.0 batteryBanksSymmetry7status.0 batteryBanksSymmetry8status.0', '-OQUs', 'ELTEK-DISTRIBUTED-MIB');
$count = array(1, 2, 3, 4, 5, 6, 7, 8);
foreach ($count as &$countValue) {
    if ($oid[0]['batteryBanksSymmetry'.$countValue.'enable'] == 'enable') {
        if ($oid[0]['batteryBanksSymmetry'.$countValue.'status'] == 'ok') {
            $state_numeric = 0;
        }
        if ($oid[0]['batteryBanksSymmetry'.$countValue.'status'] == 'minorAlarm') {
            $state_numeric = 1;
        }
        if ($oid[0]['batteryBanksSymmetry'.$countValue.'status'] == 'majorAlarm') {
            $state_numeric = 2;
        }
        if ($oid[0]['batteryBanksSymmetry'.$countValue.'status'] == 'disabled') {
            $state_numeric = 3;
        }
        if ($oid[0]['batteryBanksSymmetry'.$countValue.'status'] == 'error') {
            $state_numeric = 4;
        }

        $state_name = 'batteryBanksSymmetry'.$countValue.'status.0';
        $state_index_id = create_state_index($state_name);
        if ($state_index_id !== null) {
            $states = array(
                array($state_index_id,'ok',0,0,0) ,
                array($state_index_id,'minorAlarm',0,1,1) ,
                array($state_index_id,'majorAlarm',0,2,2) ,
                array($state_index_id,'disabled',0,3,3) ,
                array($state_index_id,'error',0,4,2)
            );
            foreach ($states as $value) {
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
        $index          = 0;
        $limit          = 10;
        $warnlimit      = null;
        $lowlimit       = null;
        $lowwarnlimit   = null;
        $divisor        = 1;
        $num_oid        = $symmetry_oid[$countValue-1];
        $state          = $state_numeric / $divisor;
        $descr          = 'Battery banks symmetry '.$countValue;
        discover_sensor(
            $valid['sensor'],
            'state',
            $device,
            $num_oid,
            $index,
            $state_name,
            $descr,
            $divisor,
            '1',
            $lowlimit,
            $lowwarnlimit,
            $warnlimit,
            $limit,
            $state
        );
        create_sensor_to_state_index(
            $device,
            $state_name,
            $index
        );
    }
}
