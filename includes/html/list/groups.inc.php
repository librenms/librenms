<?php
/**
 * groups.inc.php
 *
 * List groups
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
if (! Auth::user()->hasGlobalRead()) {
    return [];
}

$query = '';
$params = [];

if (! empty($_REQUEST['search'])) {
    $query .= ' WHERE `name` LIKE ?';
    $params[] = '%' . $_REQUEST['search'] . '%';
}

$total = dbFetchCell("SELECT COUNT(*) FROM `device_groups` $query", $params);
$more = false;

if (! empty($_REQUEST['limit'])) {
    $limit = (int) $_REQUEST['limit'];
    $page = isset($_REQUEST['page']) ? (int) $_REQUEST['page'] : 1;
    $offset = ($page - 1) * $limit;

    $query .= " LIMIT $offset, $limit";
} else {
    $offset = 0;
}

$sql = "SELECT `id`, `name` AS `text` FROM `device_groups` $query order by `name`";
$groups = dbFetchRows($sql, $params);

$more = ($offset + count($groups)) < $total;

return [$groups, $more];
