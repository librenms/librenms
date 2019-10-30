<?php
/**
 * devices.inc.php
 *
 * List devices
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

$query = '';
$where = [];
$params = [];

if (!Auth::user()->hasGlobalRead()) {
    $query .= ' LEFT JOIN `devices_perms` USING (`device_id`)';
    $where = '`devices_perms`.`user_id`=?';
    $params[] = Auth::id();
}

if (!empty($_REQUEST['search'])) {
    $where[] = '(`hostname` LIKE ? OR `sysName` LIKE ?)';
    $search = '%' . mres($_REQUEST['search']) . '%';
    $params[] = $search;
    $params[] = $search;
}

if (!empty($where)) {
    $query .= ' WHERE ';
    $query .= implode(' AND ', $where);
}

$total = dbFetchCell("SELECT COUNT(*) FROM `devices` $query", $params);
$more = false;

if (!empty($_REQUEST['limit'])) {
    $limit = (int) $_REQUEST['limit'];
    $page = isset($_REQUEST['page']) ? (int) $_REQUEST['page'] : 1;
    $offset = ($page - 1) * $limit;

    $query .= " LIMIT $offset, $limit";
} else {
    $offset = 0;
}

$sql = "SELECT `device_id`, `hostname`, `sysName` FROM `devices` $query";
$devices = array_map(function ($device) {
    return [
        'id' => $device['device_id'],
        'text' => format_hostname($device),
    ];
}, dbFetchRows($sql, $params));

$more = ($offset + count($devices)) < $total;

array_multisort(array_column($devices, 'text'), SORT_ASC, $devices);

return [$devices, $more];
