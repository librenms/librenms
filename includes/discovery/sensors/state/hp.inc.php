<?php
/**
 * hp.inc.php
 *
 * LibreNMS state sensor discovery module for HP Hardware devices
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
 * @copyright  2017 Neil Lathwood
 * @author     Neil Lathwood <neil@lathwood.co.uk>
 */

// One could add more entries from deviceGroup, but this will do as a start
$tables = [
    ['cpqDaPhyDrvStatus', '.1.3.6.1.4.1.232.3.2.5.1.1.6.', 'Status', 'CPQIDA-MIB', [
        ['value' => 1, 'generic' => 3, 'graph' => 0, 'descr' => 'other'],
        ['value' => 2, 'generic' => 0, 'graph' => 0, 'descr' => 'ok'],
        ['value' => 3, 'generic' => 2, 'graph' => 0, 'descr' => 'failed'],
        ['value' => 4, 'generic' => 2, 'graph' => 0, 'descr' => 'predictiveFailure'],
        ['value' => 5, 'generic' => 1, 'graph' => 0, 'descr' => 'erasing'],
        ['value' => 6, 'generic' => 1, 'graph' => 0, 'descr' => 'eraseDone'],
        ['value' => 7, 'generic' => 1, 'graph' => 0, 'descr' => 'eraseQueued'],
        ['value' => 8, 'generic' => 2, 'graph' => 0, 'descr' => 'ssdWearOut'],
        ['value' => 9, 'generic' => 3, 'graph' => 0, 'descr' => 'notAuthenticated'],
    ]],
    ['cpqDaPhyDrvSmartStatus', '.1.3.6.1.4.1.232.3.2.5.1.1.57.', 'S.M.A.R.T.', 'CPQIDA-MIB', [
        ['value' => 1, 'generic' => 3, 'graph' => 0, 'descr' => 'other'],
        ['value' => 2, 'generic' => 0, 'graph' => 0, 'descr' => 'ok'],
        ['value' => 3, 'generic' => 1, 'graph' => 0, 'descr' => 'replaceDrive'],
        ['value' => 4, 'generic' => 1, 'graph' => 0, 'descr' => 'replaceDriveSSDWearOut'],
    ]],
];

foreach ($tables as $tablevalue) {
    [$oid, $num_oid, $descr, $mib, $states] = $tablevalue;
    $temp = snmpwalk_cache_multi_oid($device, $oid, [], $mib, 'hp', '-OQUse');

    if (! empty($temp)) {
        //Create State Index
        $state_name = $oid;
        $state_index_id = create_state_index($state_name, $states);

        foreach ($temp as $index => $entry) {
            $drive_bay = snmp_get($device, "cpqDaPhyDrvBay.$index", '-Ovqn', 'CPQIDA-MIB', 'hp');

            //Discover Sensors
            discover_sensor(
                $valid['sensor'],
                'state',
                $device,
                $num_oid . $index,
                $index,
                $state_name,
                "Drive  $drive_bay $descr",
                1,
                1,
                null,
                null,
                null,
                null,
                $entry[$oid],
                'snmp',
                $index
            );

            //Create Sensor To State Index
            create_sensor_to_state_index($device, $state_name, $index);
        }
    }
}
