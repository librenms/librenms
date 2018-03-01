<?php
/**
 * aos-emu2.inc.php
 *
 * LibreNMS sensors state discovery module for APC EMU2
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
 * @copyright  2018 Ben Gibbons
 * @author     Ben Gibbons <axemann@gmail.com>
 */

 // Input Contact discovery

$contacts['emu2_contacts'] = snmpwalk_cache_oid($device, 'emsInputContactStatusEntry', array(), 'PowerNet-MIB');

foreach ($contacts['emu2_contacts'] as $id => $contact) {
    $index          = $contact['emsInputContactStatusInputContactIndex'];
    $oid            = '.1.3.6.1.4.1.318.1.1.10.3.14.1.1.3.' . $index;
    $descr          = $contact['emsInputContactStatusInputContactName'];
    $currentstate   = $contact['emsInputContactStatusInputContactState'];
    $normalstate    = $contact['emsInputContactStatusInputContactNormalState'];
    if (is_array($contacts['emu2_contacts']) && $normalstate == 'normallyClosedEMS') {
        $state_name_nc  = 'emsInputContactNormalState_NC';
        $state_index_id = create_state_index($state_name_nc);
        if ($state_index_id !== null) {
            $states = array(
                array($state_index_id,'Normal',0,1,0) ,
                array($state_index_id,'Alert',0,2,1)
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
    }
    elseif (is_array($contacts['emu2_contacts']) && $normalstate == 'normallyOpenEMS') {
        $state_name_no  = 'emsInputContactNormalState_NO';
        $state_index_id = create_state_index($state_name_no);
        if ($state_index_id !== null) {
            $states = array(
                array($state_index_id,'Normal',0,2,0) ,
                array($state_index_id,'Alert',0,1,1)
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
    }
    if ($normalstate == 'normallyClosedEMS') {
        discover_sensor($valid['sensor'], 'state', $device, $oid, $index, $state_name_nc, $descr, '1', '1', null, null, null, null, $currentstate, 'snmp', $index);
        create_sensor_to_state_index($device, $state_name_nc, $index);
    }
    elseif ($normalstate == 'normallyOpenEMS') {
        discover_sensor($valid['sensor'], 'state', $device, $oid, $index, $state_name_no, $descr, '1', '1', null, null, null, null, $currentstate, 'snmp', $index);
        create_sensor_to_state_index($device, $state_name_no, $index);
    }
}

// Output Relay discovery

$relays['emu2_relays'] = snmpwalk_cache_oid($device, 'emsOutputRelayStatusEntry', array(), 'PowerNet-MIB');

foreach ($relays['emu2_relays'] as $id => $relay) {
    $index          = $relay['emsOutputRelayStatusOutputRelayIndex'];
    $oid            = '.1.3.6.1.4.1.318.1.1.10.3.15.1.1.3.' . $index;
    $descr          = $relay['emsOutputRelayStatusOutputRelayName'];
    $currentstate   = $relay['emsOutputRelayStatusOutputRelayState'];
    $normalstate    = $relay['emsOutputRelayStatusOutputRelayNormalState'];
    if (is_array($relays['emu2_relays']) && $normalstate == 'normallyClosedEMS') {
        $state_name_nc  = 'emsOutputRelayNormalState_NC';
        $state_index_id = create_state_index($state_name_nc);
        if ($state_index_id !== null) {
            $states = array(
                array($state_index_id,'Normal',0,1,0) ,
                array($state_index_id,'Alert',0,2,1)
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
    }
    elseif (is_array($relays['emu2_relays']) && $normalstate == 'normallyOpenEMS') {
        $state_name_no  = 'emsOutputRelayNormalState_NO';
        $state_index_id = create_state_index($state_name_no);
        if ($state_index_id !== null) {
            $states = array(
                array($state_index_id,'Normal',0,2,0) ,
                array($state_index_id,'Alert',0,1,1)
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
    }
    if ($normalstate == 'normallyClosedEMS') {
        discover_sensor($valid['sensor'], 'state', $device, $oid, $index, $state_name_nc, $descr, '1', '1', null, null, null, null, $currentstate, 'snmp', $index);
        create_sensor_to_state_index($device, $state_name_nc, $index);
    }
    elseif ($normalstate == 'normallyOpenEMS') {
        discover_sensor($valid['sensor'], 'state', $device, $oid, $index, $state_name_no, $descr, '1', '1', null, null, null, null, $currentstate, 'snmp', $index);
        create_sensor_to_state_index($device, $state_name_no, $index);
    }
}

// Outlet discovery

$outlets['emu2_outlets'] = snmpwalk_cache_oid($device, 'emsOutletStatusEntry', array(), 'PowerNet-MIB');

foreach ($outlets['emu2_outlets'] as $id => $outlet) {
    $index          = $outlet['emsOutletStatusOutletIndex'];
    $oid            = '.1.3.6.1.4.1.318.1.1.10.3.16.1.1.3.' . $index;
    $descr          = $outlet['emsOutletStatusOutletName'];
    $currentstate   = $outlet['emsOutletStatusOutletState'];
    $normalstate    = $outlet['emsOutletStatusOutletNormalState'];
    if (is_array($outlets['emu2_outlets']) && $normalstate == 'normallyOnEMS') {
        $state_name_on  = 'emsOutletNormalState_ON';
        $state_index_id = create_state_index($state_name_on);
        if ($state_index_id !== null) {
            $states = array(
                array($state_index_id,'On',0,1,0) ,
                array($state_index_id,'Off',0,2,1)
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
    }
    elseif (is_array($outlets['emu2_outlets']) && $normalstate == 'normallyOffEMS') {
        $state_name_off  = 'emsOutletNormalState_OFF';
        $state_index_id = create_state_index($state_name_off);
        if ($state_index_id !== null) {
            $states = array(
                array($state_index_id,'Off',0,2,0) ,
                array($state_index_id,'On',0,1,1)
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
    }
    if ($normalstate == 'normallyOnEMS') {
        discover_sensor($valid['sensor'], 'state', $device, $oid, $index, $state_name_on, $descr, '1', '1', null, null, null, null, $currentstate, 'snmp', $index);
        create_sensor_to_state_index($device, $state_name_on, $index);
    }
    elseif ($normalstate == 'normallyOffEMS') {
        discover_sensor($valid['sensor'], 'state', $device, $oid, $index, $state_name_off, $descr, '1', '1', null, null, null, null, $currentstate, 'snmp', $index);
        create_sensor_to_state_index($device, $state_name_off, $index);
    }
}
