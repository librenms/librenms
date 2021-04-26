<?php
/**
 * ctm.inc.php
 *
 * Last Mile Gear CTM State
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
 * @copyright  2018 Paul Heinrichs
 * @author     Paul Heinrichs <pdheinrichs@gmail.com>
 */
$states = [
    'power' => [
        ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'Power On'],
        ['value' => 0, 'generic' => 1, 'graph' => 0, 'descr' => 'Power Off'],
    ],
    'sync' => [
        ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'Sync Enabled'],
        ['value' => 0, 'generic' => 1, 'graph' => 0, 'descr' => 'Sync Off'],
    ],
];
$octetSetup = [
    [
        'oid'           => 'portOnM.0',
        'state_name'    => 'portOnM',
        'states'        => $states['power'],
        'name'          => 'Master Port Enabled',
        'num_oid'       => '.1.3.6.1.4.1.25868.1.12.0',
    ],
    [
        'oid'           => 'portSyncM.0',
        'state_name'    => 'portSyncM',
        'states'        => $states['sync'],
        'name'          => 'Master Port Sync Status',
        'num_oid'       => '.1.3.6.1.4.1.25868.1.13.0',
    ],
    [
        'oid'           => 'portOnS.0',
        'state_name'    => 'portOnS',
        'states'        => $states['power'],
        'name'          => 'Slave Port Enabled',
        'num_oid'       => '.1.3.6.1.4.1.25868.1.29.0',
    ],
    [
        'oid'           => 'portSyncS.0',
        'state_name'    => 'portSyncS',
        'states'        => $states['sync'],
        'name'          => 'Slave Port Sync Status',
        'num_oid'       => '.1.3.6.1.4.1.25868.1.30.0',
    ],
];

foreach ($octetSetup as $entry) {
    $octetString = snmp_get($device, $entry['oid'], '-Ovqe', 'CTMMIBCUSTOM');
    if ($octetString) {
        $onStates = explode(',', $octetString);

        create_state_index($entry['state_name'], $entry['states']);

        foreach ($onStates as $index => $value) {
            $port_number = $index + 1;
            discover_sensor(
                $valid['sensor'],
                'state',
                $device,
                $entry['num_oid'],
                $port_number,
                $entry['state_name'],
                $entry['name'] . " $port_number",
                1,
                1,
                null,
                null,
                null,
                null,
                $value,
                'snmp',
                $port_number
            );
            create_sensor_to_state_index($device, $entry['state_name'], $port_number);
        }
    }
    unset($octetString, $states, $octetSetup, $port_number);
}
