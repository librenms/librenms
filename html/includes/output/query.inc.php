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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2016 Neil Lathwood
 * @author     Neil Lathwood <neil@lathwood.co.uk>
 */

if (!is_admin()) {
    echo("Insufficient Privileges");
    exit();
}

$hostname = escapeshellcmd($_REQUEST['hostname']);
$type = $_REQUEST['type'];

switch ($type) {
    case 'alerts':
        $filename = "alerts-$hostname.txt";
        $device_id = getidbyname($hostname);
        $device = device_by_id_cache($device_id);
        $rules = GetRules($device_id);
        $output = '';
        foreach ($rules as $rule) {
            $sql = GenSQL($rule['rule']);
            $qry = dbFetchRow($sql, array($device_id));
            if (is_array($qry)) {
                $response = 'matches';
            } else {
                $response = 'no match';
            }
            $output .= 'Rule name: ' . $rule['name'] . PHP_EOL;
            $output .= 'Alert rule: ' . $rule['rule'] . PHP_EOL;
            $output .= 'Rule match: ' . $response . PHP_EOL . PHP_EOL;
        }
        break;
    default:
        echo 'You must specify a valid type';
        exit();
}

// ---- Output ----

if ($_GET['format'] == 'text') {
    header("Content-type: text/plain");
    header('X-Accel-Buffering: no');

    echo $output;
} elseif ($_GET['format'] == 'download') {
    file_download($filename, $output);
}
