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
$ixid = strip_tags($vars['ixid']);
$status = strip_tags($vars['status']);

$sql = ' FROM `pdb_ix_peers` AS `P` LEFT JOIN `pdb_ix` ON `P`.`ix_id` = `pdb_ix`.`ix_id` LEFT JOIN `bgpPeers` ON `P`.`remote_ipaddr4` = `bgpPeers`.`bgpPeerIdentifier` WHERE `P`.`ix_id` = ? AND `remote_ipaddr4` IS NOT NULL';
$params = [$ixid];

if ($status === 'connected') {
    $sql .= ' AND `remote_ipaddr4` = `bgpPeerIdentifier` ';
}

if ($status === 'unconnected') {
    $sql .= ' AND `bgpPeerRemoteAs` IS NULL ';
}

if (isset($searchPhrase) && ! empty($searchPhrase)) {
    $sql .= ' AND (`remote_ipaddr4` LIKE ? OR `remote_asn` LIKE ? OR `P`.`name` LIKE ?)';
    $params[] = "%$searchPhrase%";
    $params[] = "%$searchPhrase%";
    $params[] = "%$searchPhrase%";
}

$sql .= ' GROUP BY `bgpPeerIdentifier`, `P`.`name`, `P`.`remote_ipaddr4`, `P`.`peer_id`, `P`.`remote_asn` ';
$count_sql = "SELECT COUNT(*) $sql";

$total = count(dbFetchRows($count_sql, $params));
if (empty($total)) {
    $total = 0;
}

if (! isset($sort) || empty($sort)) {
    $sort = 'remote_asn ASC';
}

$sql .= " ORDER BY $sort";

if (isset($current)) {
    $limit_low = (($current * $rowCount) - ($rowCount));
    $limit_high = $rowCount;
}

if ($rowCount != -1) {
    $sql .= " LIMIT $limit_low,$limit_high";
}

$sql = "SELECT `P`.`remote_asn`, `P`.`name`, `P`.`remote_ipaddr4`, `P`.`peer_id`, `bgpPeers`.`bgpPeerIdentifier` $sql";

foreach (dbFetchRows($sql, $params) as $peer) {
    if ($peer['remote_ipaddr4'] === $peer['bgpPeerIdentifier']) {
        $connected = '<i class="fa fa-check fa-2x text text-success"></i>';
    } else {
        $connected = '<i class="fa fa-times fa-2x text text-default"></i>';
    }
    $peer_id = $peer['peer_id'];
    $response[] = [
        'remote_asn'     => $peer['remote_asn'],
        'remote_ipaddr4' => $peer['remote_ipaddr4'],
        'peer'           => $peer['name'],
        'connected'      => "$connected",
        'links'          => "<a href='https://peeringdb.com/asn/{$peer['remote_asn']}' target='_blank'><i class='fa fa-database'></i></a>",
    ];
}

$output = [
    'current'  => $current,
    'rowCount' => $rowCount,
    'rows'     => $response,
    'total'    => $total,
];
echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
