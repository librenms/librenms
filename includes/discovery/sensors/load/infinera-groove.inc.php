<?php
/**
 * infinera-groove.inc.php
 *
 * LibreNMS fanspeed discovery module for Infinera Groove
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
 * @copyright  2019 Nick Hilliard
 * @author     Nick Hilliard <nick@foobar.org>
 */
foreach ($pre_cache['infineragroove_slotTable'] as $index => $data) {
    if (is_numeric($data['cardFanSpeedRate']) && $data['cardFanSpeedRate'] != -99) {
        $infinera_slot = 'slot-' . str_replace('.', '/', $index);
        $descr = 'Chassis fan ' . $infinera_slot;
        $oid = '.1.3.6.1.4.1.42229.1.2.3.3.1.1.7.' . $index;
        $value = $data['cardFanSpeedRate'];
        discover_sensor($valid['sensor'], 'load', $device, $oid, 'cardFanSpeedRate.' . $index, 'infinera-groove', $descr, null, '1', 0, 20, 80, 100, $value);
    }
}

unset($infinera_slot);
