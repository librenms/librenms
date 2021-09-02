<?php
/**
 * devices_groups.inc.php
 *
 * List devices and groups in one
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */
[$devices, $d_more] = include 'devices.inc.php';
[$groups, $g_more] = include 'groups.inc.php';
[$locations, $l_more] = include 'locations.inc.php';

$locations = array_map(function ($location) {
    $location['id'] = 'l' . $location['id'];

    return $location;
}, $locations);

$groups = array_map(function ($group) {
    $group['id'] = 'g' . $group['id'];

    return $group;
}, $groups);

$data = [
    ['text' => 'Locations', 'children' => $locations],
    ['text' => 'Groups', 'children' => $groups],
    ['text' => 'Devices', 'children' => $devices],
];

return [$data, $d_more || $g_more || $l_more];
