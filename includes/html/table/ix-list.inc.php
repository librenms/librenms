<?php
/**
 * LibreNMS PeeringDB Integration
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
 * @copyright  2018 Neil Lathwood
 * @author     Neil Lathwood <neil@lathwood.co.uk>
 */
$asn = strip_tags($vars['asn']);

$sql = ' FROM `pdb_ix` WHERE `asn` = ?';
$params = [$asn];

if (isset($searchPhrase) && ! empty($searchPhrase)) {
    $sql .= ' AND (`name` LIKE ?)';
    $params[] = "%$searchPhrase%";
}

$count_sql = "SELECT COUNT(*) $sql";

$total = dbFetchCell($count_sql, $params);
if (empty($total)) {
    $total = 0;
}

if (! isset($sort) || empty($sort)) {
    $sort = 'name ASC';
}

$sql .= " ORDER BY $sort";

if (isset($current)) {
    $limit_low = (($current * $rowCount) - ($rowCount));
    $limit_high = $rowCount;
}

if ($rowCount != -1) {
    $sql .= " LIMIT $limit_low,$limit_high";
}

$sql = "SELECT * $sql";

foreach (dbFetchRows($sql, $params) as $ix) {
    $ix_id = $ix['ix_id'];
    $response[] = [
        'exchange' => $ix['name'],
        'action'   => "<a class='btn btn-sm btn-primary' href='" . \LibreNMS\Util\Url::generate(['page' => 'peering', 'section' => 'ix-peers', 'asn' => $asn, 'ixid' => $ix['ix_id']]) . "' role='button'>Show Peers</a>",
        'links'    => "<a href='https://peeringdb.com/ix/$ix_id' target='_blank'><i class='fa fa-database'></i></a>",
    ];
}

$output = [
    'current'  => $current,
    'rowCount' => $rowCount,
    'rows'     => $response,
    'total'    => $total,
];
echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
