<?php

/**
 * draytek.php
 *
 * DrayTek OS
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
 * @copyright  2025 CTNET BV
 * @author     Rudy Broersma <r.broersma@ctnet.nl>
 */
$lteinfo_objects = SnmpQuery::hideMIB()->walk('DRAYTEK-MIB::lteinfoObjects')->table(1);

foreach ($lteinfo_objects as $index => $object) {
    if (isset($object['ltemaxchannelratetx'])) {
        $current = preg_replace('/\D/', '', $object['ltemaxchannelratetx']);
        if ($current == '') {
            $current = 0; 
        }

        $num_oid = '.1.3.6.1.4.1.7367.4.9.';

        app('sensor-discovery')->discover(new \App\Models\Sensor([
                'poller_type' => 'snmp',
                'sensor_class' => 'count',
                'device_id' => $device['device_id'],
                'sensor_oid' => $num_oid . $index,
                'sensor_index' => 'ltemaxchannelratetx.' . $index,
                'sensor_type' => 'ltemaxchannelratetx',
                'sensor_descr' => 'LTE Tx Mbps modem ' . $index,
                'sensor_divisor' => 1,
                'sensor_multiplier' => 1,
                'sensor_limit' => null,
                'sensor_limit_warn' => null,
                'sensor_limit_low' => null,
                'sensor_limit_low_warn' => null,
                'sensor_current' => $current,
                'group' => 'Mobile',
        ]));
    }

    if (isset($object['ltemaxchannelraterx'])) {
        $current = preg_replace('/\D/', '', $object['ltemaxchannelraterx']);
        if ($current == '') {
            $current = 0; 
        }

        $num_oid = '.1.3.6.1.4.1.7367.4.10.';

        app('sensor-discovery')->discover(new \App\Models\Sensor([
                'poller_type' => 'snmp',
                'sensor_class' => 'count',
                'device_id' => $device['device_id'],
                'sensor_oid' => $num_oid . $index,
                'sensor_index' => 'ltemaxchannelraterx.' . $index,
                'sensor_type' => 'ltemaxchannelraterx',
                'sensor_descr' => 'LTE Rx Mbps modem ' . $index,
                'sensor_divisor' => 1,
                'sensor_multiplier' => 1,
                'sensor_limit' => null,
                'sensor_limit_warn' => null,
                'sensor_limit_low' => null,
                'sensor_limit_low_warn' => null,
                'sensor_current' => $current,
                'group' => 'Mobile',
        ]));
    }    
}
                

