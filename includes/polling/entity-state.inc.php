<?php
/**
 * entity-state.inc.php
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
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

$entPhysical = dbFetchRows(
    'SELECT entPhysical_id, entPhysicalIndex FROM entPhysical WHERE device_id=?',
    array($device['device_id'])
);

if (!empty($entPhysical)) {
    echo "\nEntity States: ";

    $entPhysical = array_combine(
        array_column($entPhysical, 'entPhysicalIndex'),
        array_column($entPhysical, 'entPhysical_id')
    );
    $state_data = snmpwalk_group($device, 'entStateTable', 'ENTITY-STATE-MIB');
    $db_states = dbFetchRows('SELECT * FROM entityState WHERE device_id=?', array($device['device_id']));
    $db_states = array_combine(array_column($db_states, 'entPhysical_id'), $db_states);

    foreach ($state_data as $index => $state) {
        if (isset($entPhysical[$index])) {
            $id = $entPhysical[$index];
            if (isset($db_states[$id])) { // update the db
                // format date
                list($date, $time, $tz) = explode(',', $state['entStateLastChanged']);
                $lastChanged = new DateTime("$date $time", new DateTimeZone($tz));
                $state['entStateLastChanged'] = $lastChanged->format('Y-m-d H:i:s');

                // format the db state for comparison
                $db_state = $db_states[$id];
                unset($db_state['device_id']);
                unset($db_state['entPhysical_id']);
                unset($db_state['entity_state_id']);

                if ($db_state != $state) {
                    dbUpdate($state, 'entityState', 'entity_state_id=?', array($id));
                    d_echo("Updating entity state: ", 'U');
                    d_echo($state);
                } else {
                    echo '.';
                }

                unset($state_data[$index]); // remove so we don't  insert later
                unset($db_states[$id]); // remove so we don't delete later
            } else {
                // prep for insert later
                $state_data[$index]['device_id'] = $device['device_id'];
                $state_data[$index]['entPhysical_id'] = $id;
                d_echo("Inserting entity state:: ", '+');
                d_echo($state);
            }
        }
    }

    if (!empty($state_data)) {
        dbBulkInsert($state_data, 'entityState');
    }

    if (!empty($db_states)) {
        dbDelete(
            'entityState',
            'entity_state_id IN ' . dbGenPlaceholders(count($db_states)),
            array_column($db_states, 'entity_state_id')
        );
    }
}

echo PHP_EOL;

unset($entPhysical, $state_data, $db_states);
