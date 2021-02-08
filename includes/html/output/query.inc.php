<?php
/**
 * output.php
 *
 * runs the requested query and outputs as a file or text
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
 * @copyright  2016 Neil Lathwood
 * @author     Neil Lathwood <neil@lathwood.co.uk>
 */

use LibreNMS\Alert\AlertDB;
use LibreNMS\Alert\AlertUtil;
use LibreNMS\Alerting\QueryBuilderParser;

if (! Auth::user()->hasGlobalAdmin()) {
    echo 'Insufficient Privileges';
    exit();
}

$hostname = escapeshellcmd($_REQUEST['hostname']);
$type = $_REQUEST['type'];

switch ($type) {
    case 'alerts':
        $filename = "alerts-$hostname.txt";
        $device_id = getidbyname($hostname);
        $device = device_by_id_cache($device_id);
        $rules = AlertUtil::getRules($device_id);
        $output = '';
        $results = [];
        foreach ($rules as $rule) {
            if (empty($rule['query'])) {
                $rule['query'] = AlertDB::genSQL($rule['rule'], $rule['builder']);
            }
            $sql = $rule['query'];
            $qry = dbFetchRow($sql, [$device_id]);
            if (is_array($qry)) {
                $results[] = $qry;
                $response = 'matches';
            } else {
                $response = 'no match';
            }

            $extra = json_decode($rule['extra'], true);
            if ($extra['options']['override_query'] === 'on') {
                $qb = $extra['options']['override_query'];
            } elseif ($rule['builder']) {
                $qb = QueryBuilderParser::fromJson($rule['builder']);
            } else {
                $qb = QueryBuilderParser::fromOld($rule['rule']);
            }

            $output .= 'Rule name: ' . $rule['name'] . PHP_EOL;
            if ($qb instanceof QueryBuilderParser) {
                $output .= 'Alert rule: ' . $qb->toSql(false) . PHP_EOL;
            } else {
                $output .= 'Alert rule: Custom SQL Query' . PHP_EOL;
            }
            $output .= 'Alert query: ' . $rule['query'] . PHP_EOL;
            $output .= 'Rule match: ' . $response . PHP_EOL . PHP_EOL;
        }
        if (\LibreNMS\Config::get('alert.transports.mail') === true) {
            $contacts = AlertUtil::getContacts($results);
            if (count($contacts) > 0) {
                $output .= 'Found ' . count($contacts) . ' contacts to send alerts to.' . PHP_EOL;
            }
            foreach ($contacts as $email => $name) {
                $output .= $name . '<' . $email . '>' . PHP_EOL;
            }
            $output .= PHP_EOL;
        }
        $transports = '';
        $x = 0;
        foreach (\LibreNMS\Config::get('alert.transports') as $name => $v) {
            if (\LibreNMS\Config::get("alert.transports.$name") === true) {
                $transports .= 'Transport: ' . $name . PHP_EOL;
                $x++;
            }
        }
        if (! empty($transports)) {
            $output .= 'Found ' . $x . ' transports to send alerts to.' . PHP_EOL;
            $output .= $transports;
        }
        break;
    default:
        echo 'You must specify a valid type';
        exit();
}

// ---- Output ----

if ($_GET['format'] == 'text') {
    header('Content-type: text/plain');
    header('X-Accel-Buffering: no');

    echo $output;
} elseif ($_GET['format'] == 'download') {
    file_download($filename, $output);
}
