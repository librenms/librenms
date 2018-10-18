<?php
/**
 * netagent2.inc.php
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
 * @copyright  2016 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 * 3 Phase support extension
 * @copyright  2018 Mikael Sipilainen
 * @author     Mikael Sipilainen <mikael.sipilainen@gmail.com>
 */

$ups_state_oid = '.1.3.6.1.4.1.935.1.1.1.4.1.1.0';
$ups_state = snmp_get($device, $ups_state_oid, '-Oqv');

if (!empty($ups_state) || $ups_state == 0) {
    // UPS state OID (Value : 0-1 Unknown, 2 On Line, 3 On Battery, 4 On Boost, 5 Sleeping, 6 On Bypass, 7 Rebooting, 8 Standby, 9 On Buck )
    $state_name = 'netagent2upsstate';
    $state_index_id = create_state_index($state_name);

    if ($state_index_id !== null) {
        $states = array(
            array($state_index_id,'unknown',0,0,3) ,
            array($state_index_id,'unknown',0,1,3) ,
            array($state_index_id,'OnLine',0,2,0) ,
            array($state_index_id,'OnBattery',0,3,1) ,
            array($state_index_id,'OnBoost',0,4,0) ,
            array($state_index_id,'Sleeping',0,4,1) ,
            array($state_index_id,'OnBypass',0,6,0) ,
            array($state_index_id,'Rebooting',0,7,1) ,
            array($state_index_id,'Standby',0,8,0) ,
            array($state_index_id,'OnBuck',0,9,0)
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
    $state          = $ups_state / $divisor;
    $descr          = 'UPS state';

    discover_sensor(
        $valid['sensor'],
        'state',
        $device,
        $ups_state_oid,
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

// Detect type of UPS (Signle-Phase/3 Phase)
# Number of input lines
    $upsInputNumLines_oid = '.1.3.6.1.2.1.33.1.3.2.0';
    $in_phaseNum = snmp_get($device, $upsInputNumLines_oid, '-Oqv');

// 3 Phase system states
if ($in_phaseNum == '3') {
    // Inverter active
    $ups_state_oid = '.1.3.6.1.4.1.935.1.1.1.8.6.5.0';
    $ups_state = snmp_get($device, $ups_state_oid, '-Oqv');

    if (!empty($ups_state) || $ups_state == 0) {
        $state_name = 'upsThreePhaseUPSStatusInverterOperating';
        $state_index_id = create_state_index($state_name);

        if ($state_index_id !== null) {
            $states = array(
                array($state_index_id,'yes',0,14,0) ,
                array($state_index_id,'no',0,16,2)
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
        $state          = $ups_state / $divisor;
        $descr          = 'Inverter Operating';

        discover_sensor(
            $valid['sensor'],
            'state',
            $device,
            $ups_state_oid,
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
    // AC Status
    $ups_state_oid = '.1.3.6.1.4.1.935.1.1.1.8.6.3.0';
    $ups_state = snmp_get($device, $ups_state_oid, '-Oqv');

    if (!empty($ups_state) || $ups_state == 0) {
        $state_name = 'upsThreePhaseUPSStatusACStatus';
        $state_index_id = create_state_index($state_name);

        if ($state_index_id !== null) {
            $states = array(
                array($state_index_id,'normal',0,10,0) ,
                array($state_index_id,'abnormal',0,11,2)
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
        $state          = $ups_state / $divisor;
        $descr          = 'AC status';

        discover_sensor(
            $valid['sensor'],
            'state',
            $device,
            $ups_state_oid,
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
    // Bypass braker status
    $ups_state_oid = '.1.3.6.1.4.1.935.1.1.1.8.6.2.0';
    $ups_state = snmp_get($device, $ups_state_oid, '-Oqv');

    if (!empty($ups_state) || $ups_state == 0) {
        $state_name = 'upsThreePhaseUPSStatusManualBypassBreaker';
        $state_index_id = create_state_index($state_name);

        if ($state_index_id !== null) {
            $states = array(
                array($state_index_id,'close',0,8,1) ,
                array($state_index_id,'open',0,9,)
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
        $state          = $ups_state / $divisor;
        $descr          = 'Breaker Status';

        discover_sensor(
            $valid['sensor'],
            'state',
            $device,
            $ups_state_oid,
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
    // Rectifier Operating
    $ups_state_oid = '.1.3.6.1.4.1.935.1.1.1.8.5.7.0';
    $ups_state = snmp_get($device, $ups_state_oid, '-Oqv');

    if (!empty($ups_state) || $ups_state == 0) {
        $state_name = 'upsThreePhaseDCandRectifierStatusRecOperating';
        $state_index_id = create_state_index($state_name);

        if ($state_index_id !== null) {
            $states = array(
                array($state_index_id,'yes',0,14,0) ,
                array($state_index_id,'no',0,16,2)
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
        $state          = $ups_state / $divisor;
        $descr          = 'Rectifier Operating';

        discover_sensor(
            $valid['sensor'],
            'state',
            $device,
            $ups_state_oid,
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
    // Charge Status
    $ups_state_oid = '.1.3.6.1.4.1.935.1.1.1.8.5.6.0';
    $ups_state = snmp_get($device, $ups_state_oid, '-Oqv');

    if (!empty($ups_state) || $ups_state == 0) {
        $state_name = 'upsThreePhaseDCandRectifierStatusChargeStatus';
        $state_index_id = create_state_index($state_name);

        if ($state_index_id !== null) {
            $states = array(
                array($state_index_id,'boost',0,6,0) ,
                array($state_index_id,'float',0,7,0) ,
                array($state_index_id,'no',0,16,2)
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
        $state          = $ups_state / $divisor;
        $descr          = 'Charge Status';

        discover_sensor(
            $valid['sensor'],
            'state',
            $device,
            $ups_state_oid,
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
    // Back Status
    $ups_state_oid = '.1.3.6.1.4.1.935.1.1.1.8.5.5.0';
    $ups_state = snmp_get($device, $ups_state_oid, '-Oqv');

    if (!empty($ups_state) || $ups_state == 0) {
        $state_name = 'upsThreePhaseDCandRectifierStatusBatteryStatus';
        $state_index_id = create_state_index($state_name);

        if ($state_index_id !== null) {
            $states = array(
                array($state_index_id,'backup',0,4,1) ,
                array($state_index_id,'acnormal',0,5,0)
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
        $state          = $ups_state / $divisor;
        $descr          = 'Back Status';

        discover_sensor(
            $valid['sensor'],
            'state',
            $device,
            $ups_state_oid,
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
    // In And Out
    $ups_state_oid = '.1.3.6.1.4.1.935.1.1.1.8.5.4.0';
    $ups_state = snmp_get($device, $ups_state_oid, '-Oqv');

    if (!empty($ups_state) || $ups_state == 0) {
        $state_name = 'upsThreePhaseDCandRectifierStatusInAndOut';
        $state_index_id = create_state_index($state_name);

        if ($state_index_id !== null) {
            $states = array(
                array($state_index_id,'threeInOneOut',0,2,3) ,
                array($state_index_id,'threeInThreeOut',0,3,3)
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
        $state          = $ups_state / $divisor;
        $descr          = 'In And Out';

        discover_sensor(
            $valid['sensor'],
            'state',
            $device,
            $ups_state_oid,
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
    // Rectifier Rotation Error
    $ups_state_oid = '.1.3.6.1.4.1.935.1.1.1.8.5.1.0';
    $ups_state = snmp_get($device, $ups_state_oid, '-Oqv');

    if (!empty($ups_state) || $ups_state == 0) {
        $state_name = 'upsThreePhaseDCandRectifierStatusRecRotError';
        $state_index_id = create_state_index($state_name);

        if ($state_index_id !== null) {
            $states = array(
                array($state_index_id,'yes',0,14,2) ,
                array($state_index_id,'no',0,16,0)
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
        $state          = $ups_state / $divisor;
        $descr          = 'Rectifier Rotation Error';

        discover_sensor(
            $valid['sensor'],
            'state',
            $device,
            $ups_state_oid,
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
    // Short Circuit
    $ups_state_oid = '.1.3.6.1.4.1.935.1.1.1.8.7.7.0';
    $ups_state = snmp_get($device, $ups_state_oid, '-Oqv');

    if (!empty($ups_state) || $ups_state == 0) {
        $state_name = 'upsThreePhaseFaultStatusShortCircuit';
        $state_index_id = create_state_index($state_name);

        if ($state_index_id !== null) {
            $states = array(
                array($state_index_id,'yes',0,14,2) ,
                array($state_index_id,'no',0,16,0)
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
        $state          = $ups_state / $divisor;
        $descr          = 'Short Circuit';

        discover_sensor(
            $valid['sensor'],
            'state',
            $device,
            $ups_state_oid,
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
    // Switch Mode
    $ups_state_oid = '.1.3.6.1.4.1.935.1.1.1.8.6.4.0';
    $ups_state = snmp_get($device, $ups_state_oid, '-Oqv');

    if (!empty($ups_state) || $ups_state == 0) {
        $state_name = 'upsThreePhaseUPSStaticSwitchMode';
        $state_index_id = create_state_index($state_name);

        if ($state_index_id !== null) {
            $states = array(
                array($state_index_id,'invermode',0,12,0) ,
                array($state_index_id,'bypassmode',0,13,1)
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
        $state          = $ups_state / $divisor;
        $descr          = 'Switch Mode';

        discover_sensor(
            $valid['sensor'],
            'state',
            $device,
            $ups_state_oid,
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
    // Bypass Status
    $ups_state_oid = '.1.3.6.1.4.1.935.1.1.1.8.6.1.0';
    $ups_state = snmp_get($device, $ups_state_oid, '-Oqv');

    if (!empty($ups_state) || $ups_state == 0) {
        $state_name = 'upsThreePhaseUPSStatusBypassFreqFail';
        $state_index_id = create_state_index($state_name);

        if ($state_index_id !== null) {
            $states = array(
                array($state_index_id,'yes',0,14,2) ,
                array($state_index_id,'no',0,16,0)
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
        $state          = $ups_state / $divisor;
        $descr          = 'Bypass freq. fail';

        discover_sensor(
            $valid['sensor'],
            'state',
            $device,
            $ups_state_oid,
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
