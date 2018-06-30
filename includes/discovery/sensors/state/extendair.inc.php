<?php
/**
 * extendair.inc.php
 *
 * LibreNMS state discover module for Exalt ExtendAir
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
 * @copyright  2017 Neil Lathwood
 * @author     Neil Lathwood <neil@lathwood.co.uk>
 */

$temp = snmp_get($device, 'remLinkState.0', "-Ovqe", "ExaltComProducts");
$cur_oid = '.1.3.6.1.4.1.25651.1.2.4.2.4.1.1.0';

if (is_numeric($temp)) {
    $state_name = 'remLinkState';
    $index      = $state_name;
    $state_index_id = create_state_index($state_name);

    if ($state_index_id !== null) {
        $states = array(
            array($state_index_id,'almNORMAL',1,0,0) ,
            array($state_index_id,'almMINOR',1,1,1) ,
            array($state_index_id,'almMAJOR',1,2,2) ,
            array($state_index_id,'almDisable',1,3,1) ,
            array($state_index_id,'almNotAvailable',1,4,1),
            array($state_index_id,'almClearChanel',1,5,1),
            array($state_index_id,'almNonOccupant',1,6,1),
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

    $descr = 'Link status (far end radio)';
    discover_sensor($valid['sensor'], 'state', $device, $cur_oid, $index, $state_name, $descr, '1', '1', null, null, null, null, $temp);
    create_sensor_to_state_index($device, $state_name, $index);
}

$temp = snmp_get($device, 'locLinkState.0', "-Ovqe", "ExaltComProducts");
$cur_oid = '.1.3.6.1.4.1.25651.1.2.4.2.3.1.1.0';

if (is_numeric($temp)) {
    $state_name = 'locLinkState';
    $index      = $state_name;
    $state_index_id = create_state_index($state_name);

    if ($state_index_id !== null) {
        $states = array(
            array($state_index_id,'almNORMAL',1,0,0) ,
            array($state_index_id,'almMINOR',1,1,1) ,
            array($state_index_id,'almMAJOR',1,2,2) ,
            array($state_index_id,'almDisable',1,3,1) ,
            array($state_index_id,'almNotAvailable',1,4,1),
            array($state_index_id,'almClearChanel',1,5,1),
            array($state_index_id,'almNonOccupant',1,6,1),
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

    $descr = 'Link status (local radio)';
    discover_sensor($valid['sensor'], 'state', $device, $cur_oid, $index, $state_name, $descr, '1', '1', null, null, null, null, $temp);
    create_sensor_to_state_index($device, $state_name, $index);
}

$temp = snmp_get($device, 'locTempAlarm.0', "-Ovqe", "ExaltComProducts");
$cur_oid = '.1.3.6.1.4.1.25651.1.2.4.2.3.1.2.0';

if (is_numeric($temp)) {
    $state_name = 'locTempAlarm';
    $index = $state_name;
    $state_index_id = create_state_index($state_name);

    if ($state_index_id !== null) {
        $states = array(
            array($state_index_id,'almNORMAL',1,0,0) ,
            array($state_index_id,'almMINOR',1,1,1) ,
            array($state_index_id,'almMAJOR',1,2,2) ,
            array($state_index_id,'almDisable',1,3,1) ,
            array($state_index_id,'almNotAvailable',1,4,1),
            array($state_index_id,'almClearChanel',1,5,1),
            array($state_index_id,'almNonOccupant',1,6,1),
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

    $descr = 'Temperature status (local radio)';
    discover_sensor($valid['sensor'], 'state', $device, $cur_oid, $index, $state_name, $descr, '1', '1', null, null, null, null, $temp);
    create_sensor_to_state_index($device, $state_name, $index);
}


$temp = snmp_get($device, 'remTempAlarm.0', "-Ovqe", "ExaltComProducts");
$cur_oid = '.1.3.6.1.4.1.25651.1.2.4.2.4.1.2.0';

if (is_numeric($temp)) {
    $state_name = 'remTempAlarm';
    $index = $state_name;
    $state_index_id = create_state_index($state_name);

    if ($state_index_id !== null) {
        $states = array(
            array($state_index_id,'almNORMAL',1,0,0) ,
            array($state_index_id,'almMINOR',1,1,1) ,
            array($state_index_id,'almMAJOR',1,2,2) ,
            array($state_index_id,'almDisable',1,3,1) ,
            array($state_index_id,'almNotAvailable',1,4,1),
            array($state_index_id,'almClearChanel',1,5,1),
            array($state_index_id,'almNonOccupant',1,6,1),
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

    $descr = 'Temperature status (far end radio)';
    discover_sensor($valid['sensor'], 'state', $device, $cur_oid, $index, $state_name, $descr, '1', '1', null, null, null, null, $temp);
    create_sensor_to_state_index($device, $state_name, $index);
}

$temp = snmp_get($device, 'remLinkSecMismatch.0', "-Ovqe", "ExaltComProducts");
$cur_oid = '.1.3.6.1.4.1.25651.1.2.4.2.4.1.9.0';

if (is_numeric($temp)) {
    $state_name = 'remLinkSecMismatch';
    $index = $state_name;
    $state_index_id = create_state_index($state_name);

    if ($state_index_id !== null) {
        $states = array(
            array($state_index_id,'almNORMAL',1,0,0) ,
            array($state_index_id,'almMINOR',1,1,1) ,
            array($state_index_id,'almMAJOR',1,2,2) ,
            array($state_index_id,'almDisable',1,3,1) ,
            array($state_index_id,'almNotAvailable',1,4,1),
            array($state_index_id,'almClearChanel',1,5,1),
            array($state_index_id,'almNonOccupant',1,6,1),
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

    $descr = 'Link security status';
    discover_sensor($valid['sensor'], 'state', $device, $cur_oid, $index, $state_name, $descr, '1', '1', null, null, null, null, $temp);
    create_sensor_to_state_index($device, $state_name, $index);
}

$temp = snmp_get($device, 'locLinkStateV.0', "-Ovqe", "ExaltComProducts");
$cur_oid = '.1.3.6.1.4.1.25651.1.2.4.2.3.1.15.0';

if (is_numeric($temp)) {
    $state_name = 'locLinkStateV';
    $index = $state_name;
    $state_index_id = create_state_index($state_name);

    if ($state_index_id !== null) {
        $states = array(
            array($state_index_id,'almNORMAL',1,0,0) ,
            array($state_index_id,'almMINOR',1,1,1) ,
            array($state_index_id,'almMAJOR',1,2,2) ,
            array($state_index_id,'almDisable',1,3,1) ,
            array($state_index_id,'almNotAvailable',1,4,1),
            array($state_index_id,'almClearChanel',1,5,1),
            array($state_index_id,'almNonOccupant',1,6,1),
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

    $descr = 'Vertial link status (local radio)';
    discover_sensor($valid['sensor'], 'state', $device, $cur_oid, $index, $state_name, $descr, '1', '1', null, null, null, null, $temp);
    create_sensor_to_state_index($device, $state_name, $index);
}

$temp = snmp_get($device, 'locLinkStateH.0', "-Ovqe", "ExaltComProducts");
$cur_oid = '.1.3.6.1.4.1.25651.1.2.4.2.3.1.16.0';

if (is_numeric($temp)) {
    $state_name = 'locLinkStateH';
    $index = $state_name;
    $state_index_id = create_state_index($state_name);

    if ($state_index_id !== null) {
        $states = array(
            array($state_index_id,'almNORMAL',1,0,0) ,
            array($state_index_id,'almMINOR',1,1,1) ,
            array($state_index_id,'almMAJOR',1,2,2) ,
            array($state_index_id,'almDisable',1,3,1) ,
            array($state_index_id,'almNotAvailable',1,4,1),
            array($state_index_id,'almClearChanel',1,5,1),
            array($state_index_id,'almNonOccupant',1,6,1),
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

    $descr = 'Horizontal link status (local radio)';
    discover_sensor($valid['sensor'], 'state', $device, $cur_oid, $index, $state_name, $descr, '1', '1', null, null, null, null, $temp);
    create_sensor_to_state_index($device, $state_name, $index);
}

$temp = snmp_get($device, 'remLinkStateV.0', "-Ovqe", "ExaltComProducts");
$cur_oid = '.1.3.6.1.4.1.25651.1.2.4.2.4.1.15.0';

if (is_numeric($temp)) {
    $state_name = 'remLinkStateV';
    $index = $state_name;
    $state_index_id = create_state_index($state_name);

    if ($state_index_id !== null) {
        $states = array(
            array($state_index_id,'almNORMAL',1,0,0) ,
            array($state_index_id,'almMINOR',1,1,1) ,
            array($state_index_id,'almMAJOR',1,2,2) ,
            array($state_index_id,'almDisable',1,3,1) ,
            array($state_index_id,'almNotAvailable',1,4,1),
            array($state_index_id,'almClearChanel',1,5,1),
            array($state_index_id,'almNonOccupant',1,6,1),
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

    $descr = 'Vertial link status (far end radio)';
    discover_sensor($valid['sensor'], 'state', $device, $cur_oid, $index, $state_name, $descr, '1', '1', null, null, null, null, $temp);
    create_sensor_to_state_index($device, $state_name, $index);
}

$temp = snmp_get($device, 'remLinkStateH.0', "-Ovqe", "ExaltComProducts");
$cur_oid = '.1.3.6.1.4.1.25651.1.2.4.2.4.1.16.0';

if (is_numeric($temp)) {
    $state_name = 'remLinkStateH';
    $index = $state_name;
    $state_index_id = create_state_index($state_name);

    if ($state_index_id !== null) {
        $states = array(
            array($state_index_id,'almNORMAL',1,0,0) ,
            array($state_index_id,'almMINOR',1,1,1) ,
            array($state_index_id,'almMAJOR',1,2,2) ,
            array($state_index_id,'almDisable',1,3,1) ,
            array($state_index_id,'almNotAvailable',1,4,1),
            array($state_index_id,'almClearChanel',1,5,1),
            array($state_index_id,'almNonOccupant',1,6,1),
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

    $descr = 'Horizontal link status (far end radio)';
    discover_sensor($valid['sensor'], 'state', $device, $cur_oid, $index, $state_name, $descr, '1', '1', null, null, null, null, $temp);
    create_sensor_to_state_index($device, $state_name, $index);
}

unset(
    $temp,
    $cur_oid,
    $states
);
