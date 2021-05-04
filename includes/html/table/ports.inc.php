<?php
/**
 * ports.inc.php
 *
 * Exports the ports table to json
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
 * @copyright  2016 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */
$where = "`D`.`hostname` != '' ";
$param = [];
$sql = 'FROM `ports`';

if (! Auth::user()->hasGlobalRead()) {
    $port_ids = Permissions::portsForUser()->toArray() ?: [0];
    $device_ids = Permissions::devicesForUser()->toArray() ?: [0];
    $where .= ' AND (`ports`.`port_id` IN ' . dbGenPlaceholders(count($port_ids));
    $where .= ' OR `D`.`device_id` IN ' . dbGenPlaceholders(count($device_ids));
    $where .= ')';
    $param = array_merge($param, $port_ids, $device_ids);
}

$sql .= ' LEFT JOIN `devices` AS `D` ON `ports`.`device_id` = `D`.`device_id`';

if (! empty($vars['hostname'])) {
    $where .= ' AND (D.hostname LIKE ? OR D.sysName LIKE ?)';
    $param += array_fill(count($param), 2, '%' . $vars['hostname'] . '%');
}

if (! empty($vars['location'])) {
    $where .= ' AND `D`.`location_id` = ?';
    $param[] = $vars['location'];
}

$sql .= " WHERE $where ";

if (! empty($vars['errors'])) {
    $sql .= ' AND (`ports`.`ifInErrors_delta` > 0 OR `ports`.`ifOutErrors_delta` > 0)';
}

if (! empty($vars['device_id'])) {
    $sql .= ' AND `ports`.`device_id`=?';
    $param[] = $vars['device_id'];
}

if (! empty($vars['state'])) {
    switch ($vars['state']) {
        case 'down':
            $sql .= ' AND `ports`.`ifAdminStatus` = ? AND `ports`.`ifOperStatus` = ?';
            $param[] = 'up';
            $param[] = 'down';
            break;
        case 'up':
            $sql .= ' AND `ports`.`ifAdminStatus` = ? AND `ports`.`ifOperStatus` = ?';
            $param[] = 'up';
            $param[] = 'up';
            break;
        case 'admindown':
            $sql .= ' AND `ports`.`ifAdminStatus` = ? AND `D`.`ignore` = 0';
            $param[] = 'down';
            break;
    }
}

if (! empty($vars['ifSpeed'])) {
    $sql .= ' AND `ports`.`ifSpeed`=?';
    $param[] = $vars['ifSpeed'];
}

if (! empty($vars['ifType'])) {
    $sql .= ' AND `ports`.`ifType`=?';
    $param[] = $vars['ifType'];
}

if (! empty($vars['port_descr_type'])) {
    $sql .= ' AND `ports`.`port_descr_type`=?';
    $param[] = $vars['port_descr_type'];
}

if (! empty($vars['ifAlias'])) {
    $sql .= ' AND `ports`.`ifAlias` LIKE ?';
    $param[] = '%' . $vars['ifAlias'] . '%';
}

$sql .= ' AND `ports`.`disabled`=?';
$param[] = (int) (isset($vars['disabled']) && $vars['disabled']);

$sql .= ' AND `ports`.`ignore`=?';
$param[] = (int) (isset($vars['ignore']) && $vars['ignore']);

$sql .= ' AND `ports`.`deleted`=?';
$param[] = (int) (isset($vars['deleted']) && $vars['deleted']);

$count_sql = "SELECT COUNT(`ports`.`port_id`) $sql";
$total = (int) dbFetchCell($count_sql, $param);

if (isset($sort) && ! empty($sort)) {
    [$sort_column, $sort_order] = explode(' ', trim($sort));
    if ($sort_column == 'device') {
        $sql .= " ORDER BY `D`.`hostname` $sort_order";
    } elseif ($sort_column == 'port') {
        $sql .= " ORDER BY `ifDescr` $sort_order";
    } elseif ($sort_column == 'ifLastChange') {
        $sql .= " ORDER BY `secondsIfLastChange` $sort_order";
    } else {
        $sql .= " ORDER BY `$sort_column` $sort_order";
    }
}

if (isset($current)) {
    $limit_low = (($current * $rowCount) - ($rowCount));
    $limit_high = $rowCount;
}

if ($rowCount != -1) {
    $sql .= " LIMIT $limit_low,$limit_high";
}

$query = 'SELECT DISTINCT(`ports`.`port_id`),`ports`.*';
// calculate ifLastChange as seconds ago
$query .= ',`D`.`uptime` - `ports`.`ifLastChange` / 100 as secondsIfLastChange ';
$query .= $sql;

foreach (dbFetchRows($query, $param) as $port) {
    $device = device_by_id_cache($port['device_id']);
    $port = cleanPort($port, $device);

    switch ($port['ifOperStatus']) {
        case 'up':
            $status = 'label-success';
            break;
        case 'down':
            switch ($port['ifAdminStatus']) {
                case 'up':
                    $status = 'label-danger';
                    break;
                case 'down':
                    $status = 'label-warning';
                    break;
            }
            break;
    }

    // FIXME what actions should we have?
    $actions = '<div class="container-fluid"><div class="row">';

    if ($vars['deleted'] !== 'yes') {
        $actions .= '<div class="col-xs-1"><a href="';
        $actions .= \LibreNMS\Util\Url::deviceUrl((int) $device['device_id'], ['tab' => 'alerts']);
        $actions .= '" title="View alerts"><i class="fa fa-exclamation-circle fa-lg icon-theme" aria-hidden="true"></i></a></div>';

        if (Auth::user()->hasGlobalAdmin()) {
            $actions .= '<div class="col-xs-1"><a href="';
            $actions .= \LibreNMS\Util\Url::deviceUrl((int) $device['device_id'], ['tab' => 'edit', 'section' => 'ports']);
            $actions .= '" title="Edit ports"><i class="fa fa-pencil fa-lg icon-theme" aria-hidden="true"></i></a></div>';
        }
    }

    if ($vars['deleted'] === 'yes') {
        if (port_permitted($port['port_id'], $device['device_id'])) {
            $actions .= '<div class="col-xs-1"><a href="ports/deleted=yes/purge=' . $port['port_id'] . '" title="Delete port"><i class="fa fa-times fa-lg icon-theme"></i></a></div>';
        }
    }

    $actions .= '</div></div>';

    $response[] = [
        'status' => $status,
        'device' => generate_device_link($device),
        'port' => generate_port_link($port),
        'ifLastChange' => ceil($port['secondsIfLastChange']),
        'ifConnectorPresent' => ($port['ifConnectorPresent'] == 'true') ? 'yes' : 'no',
        'ifSpeed' => $port['ifSpeed'],
        'ifMtu' => $port['ifMtu'],
        'ifInOctets_rate' => $port['ifInOctets_rate'] * 8,
        'ifOutOctets_rate' => $port['ifOutOctets_rate'] * 8,
        'ifInUcastPkts_rate' => $port['ifInUcastPkts_rate'],
        'ifOutUcastPkts_rate' => $port['ifOutUcastPkts_rate'],
        'ifInErrors' => $port['poll_period'] ? \LibreNMS\Util\Number::formatSi($port['ifInErrors_delta'] / $port['poll_period'], 2, 3, 'EPS') : '',
        'ifOutErrors' => $port['poll_period'] ? \LibreNMS\Util\Number::formatSi($port['ifOutErrors_delta'] / $port['poll_period'], 2, 3, 'EPS') : '',
        'ifType' => \LibreNMS\Util\Rewrite::normalizeIfType($port['ifType']),
        'ifAlias' => $port['ifAlias'],
        'actions' => $actions,
    ];
}

$output = [
    'current' => $current,
    'rowCount' => $rowCount,
    'rows' => $response,
    'total' => $total,
];

echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
