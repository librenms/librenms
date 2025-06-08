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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2018 Ben Gibbons
 * @author     Ben Gibbons <axemann@gmail.com>
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 */

// Input Contact discovery
$oids = SnmpQuery::walk([
    'PowerNet-MIB::emsInputContactStatusEntry',
])->table(1);

foreach ($oids as $id => $contact) {
    $index = $contact['PowerNet-MIB::emsInputContactStatusInputContactIndex'];
    $oid = '.1.3.6.1.4.1.318.1.1.10.3.14.1.1.3.' . $index;
    $descr = $contact['PowerNet-MIB::emsInputContactStatusInputContactName'];
    $currentstate = $contact['PowerNet-MIB::emsInputContactStatusInputContactState'];
    $normalstate = $contact['PowerNet-MIB::emsInputContactStatusInputContactNormalState'];
    if (is_array($oids) && $normalstate == '1') {
        $state_name = 'emsInputContactNormalState_NC';
        $states = [
            ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'Closed'],
            ['value' => 2, 'generic' => 1, 'graph' => 0, 'descr' => 'Open'],
        ];
        create_state_index($state_name, $states);
    } elseif (is_array($oids) && $normalstate == '2') {
        $state_name = 'emsInputContactNormalState_NO';
        $states = [
            ['value' => 1, 'generic' => 1, 'graph' => 0, 'descr' => 'Closed'],
            ['value' => 2, 'generic' => 0, 'graph' => 0, 'descr' => 'Open'],
        ];
        create_state_index($state_name, $states);
    }

    app('sensor-discovery')->discover(new \App\Models\Sensor([
        'poller_type' => 'snmp',
        'sensor_class' => 'state',
        'sensor_oid' => $oid,
        'sensor_index' => $index,
        'sensor_type' => $state_name,
        'sensor_descr' => $descr,
        'sensor_divisor' => 1,
        'sensor_multiplier' => 1,
        'sensor_limit_low' => null,
        'sensor_limit_low_warn' => null,
        'sensor_limit_warn' => null,
        'sensor_limit' => null,
        'sensor_current' => $currentstate,
        'entPhysicalIndex' => $index,
        'entPhysicalIndex_measured' => null,
        'user_func' => null,
        'group' => null,
    ]));
}

// Output Relay discovery
$oids = SnmpQuery::walk([
    'PowerNet-MIB::emsOutputRelayStatusEntry',
])->table(1);

foreach ($oids as $id => $relay) {
    $index = $relay['PowerNet-MIB::emsOutputRelayStatusOutputRelayIndex'];
    $oid = '.1.3.6.1.4.1.318.1.1.10.3.15.1.1.3.' . $index;
    $descr = $relay['PowerNet-MIB::emsOutputRelayStatusOutputRelayName'];
    $currentstate = $relay['PowerNet-MIB::emsOutputRelayStatusOutputRelayState'];
    $normalstate = $relay['PowerNet-MIB::emsOutputRelayStatusOutputRelayNormalState'];
    if (is_array($oids) && $normalstate == '1') {
        $state_name = 'emsOutputRelayNormalState_NC';
        $states = [
            ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'Closed'],
            ['value' => 2, 'generic' => 1, 'graph' => 0, 'descr' => 'Open'],
        ];
        create_state_index($state_name, $states);
    } elseif (is_array($oids) && $normalstate == '2') {
        $state_name = 'emsOutputRelayNormalState_NO';
        $states = [
            ['value' => 1, 'generic' => 1, 'graph' => 0, 'descr' => 'Closed'],
            ['value' => 2, 'generic' => 0, 'graph' => 0, 'descr' => 'Open'],
        ];
        create_state_index($state_name, $states);
    }

    app('sensor-discovery')->discover(new \App\Models\Sensor([
        'poller_type' => 'snmp',
        'sensor_class' => 'state',
        'sensor_oid' => $oid,
        'sensor_index' => $index,
        'sensor_type' => $state_name,
        'sensor_descr' => $descr,
        'sensor_divisor' => 1,
        'sensor_multiplier' => 1,
        'sensor_limit_low' => null,
        'sensor_limit_low_warn' => null,
        'sensor_limit_warn' => null,
        'sensor_limit' => null,
        'sensor_current' => $currentstate,
        'entPhysicalIndex' => $index,
        'entPhysicalIndex_measured' => null,
        'user_func' => null,
        'group' => null,
    ]));
}

// Outlet discovery
$oids = SnmpQuery::walk([
    'PowerNet-MIB::emsOutletStatusEntry',
])->table(1);

foreach ($oids as $id => $outlet) {
    $index = $outlet['PowerNet-MIB::emsOutletStatusOutletIndex'];
    $oid = '.1.3.6.1.4.1.318.1.1.10.3.16.1.1.3.' . $index;
    $descr = $outlet['PowerNet-MIB::emsOutletStatusOutletName'];
    $currentstate = $outlet['PowerNet-MIB::emsOutletStatusOutletState'];
    $normalstate = $outlet['PowerNet-MIB::emsOutletStatusOutletNormalState'];
    if (is_array($oids) && $normalstate == '1') {
        $state_name = 'emsOutletNormalState_ON';
        $states = [
            ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'On'],
            ['value' => 2, 'generic' => 1, 'graph' => 0, 'descr' => 'Off'],
        ];
        create_state_index($state_name, $states);
    } elseif (is_array($oids) && $normalstate == '2') {
        $state_name = 'emsOutletNormalState_OFF';
        $states = [
            ['value' => 1, 'generic' => 1, 'graph' => 0, 'descr' => 'On'],
            ['value' => 2, 'generic' => 0, 'graph' => 0, 'descr' => 'Off'],
        ];
        create_state_index($state_name, $states);
    }

    app('sensor-discovery')->discover(new \App\Models\Sensor([
        'poller_type' => 'snmp',
        'sensor_class' => 'state',
        'sensor_oid' => $oid,
        'sensor_index' => $index,
        'sensor_type' => $state_name,
        'sensor_descr' => $descr,
        'sensor_divisor' => 1,
        'sensor_multiplier' => 1,
        'sensor_limit_low' => null,
        'sensor_limit_low_warn' => null,
        'sensor_limit_warn' => null,
        'sensor_limit' => null,
        'sensor_current' => $currentstate,
        'entPhysicalIndex' => $index,
        'entPhysicalIndex_measured' => null,
        'user_func' => null,
        'group' => null,
    ]));
}
