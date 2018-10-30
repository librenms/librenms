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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

list($devices, $d_more) = include 'devices.inc.php';
list($groups, $g_more) = include 'groups.inc.php';

$groups = array_map(function ($group) {
    $group['id'] = 'g' . $group['id'];
    return $group;
}, $groups);

$data = [
    ['text' => 'Devices', 'children' => $devices],
    ['text' => 'Groups', 'children' => $groups]
];

return [$data, $d_more || $g_more];
