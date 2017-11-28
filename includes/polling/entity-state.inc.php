<?php
/**
 * entity-state.inc.php
 *
 * ENTITY-STATE-MIB polling module
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
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

$entityStates = dbFetchRows('SELECT entityState.*, entPhysical.entPhysicalIndex FROM entityState LEFT JOIN entPhysical USING (entPhysical_id) WHERE entityState.device_id=?',array($device['device_id']));

if (!empty($entityStates)) {
    echo "\nEntity States: ";

    // index by entPhysicalIndex
    $entityStates = array_combine(array_column($entityStates, 'entPhysicalIndex'), $entityStates);

    $entLC = snmpwalk_group($device, 'entStateLastChanged', 'ENTITY-STATE-MIB', 0);

    foreach (current($entLC) as $index => $changed) {
        if ($changed) { // skip empty entries
            try {
                list($date, $time, $tz) = explode(',', $changed);
                $lastChanged = new DateTime("$date $time", new DateTimeZone($tz));
                $dbLastChanged = new DateTime($entityStates[$index]['entStateLastChanged']);
                if ($lastChanged != $dbLastChanged) {
                    // data has changed, fetch it
                    $state_data = snmp_get_multi(
                        $device,
                        array(
                            "entStateAdmin.$index",
                            "entStateOper.$index",
                            "entStateUsage.$index",
                            "entStateAlarm.$index",
                            "entStateStandby.$index"
                        ),
                        '-OQUse',
                        'ENTITY-STATE-MIB'
                    );
                    $state_data = $state_data[$index]; // just get values

                    // add entStateLastChanged and update
                    $state_data['entStateLastChanged'] = $lastChanged
                        ->setTimezone(new DateTimeZone(date_default_timezone_get()))
                        ->format('Y-m-d H:i:s');

                    // check if anything has changed
                    $update = array_diff($state_data, $entityStates[$index]);
                    if (!empty($update)) {
                        dbUpdate($update, 'entityState', 'entity_state_id=?',
                            array($entityStates[$index]['entity_state_id']));
                        d_echo("Updating $index: ", 'U');
                        d_echo($state_data[$index]);
                        continue;
                    }
                }
            } catch (Exception $e) {
                // no update
                d_echo("Error: " . $e->getMessage() . PHP_EOL);
            }
        }
        echo '.';
    }

    echo PHP_EOL;
}

unset($entityStates, $entLC, $lastChanged, $dbLastChanged, $state_data, $update);
