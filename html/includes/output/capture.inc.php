<?php
/**
 * output.php
 *
 * runs the requested command and outputs as a file or json
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
 * @copyright  2016 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

if (!is_admin()) {
    echo("Insufficient Privileges");
    exit();
}

$hostname = escapeshellcmd($_REQUEST['hostname']);
$type = $_REQUEST['type'];

switch ($type) {
    case 'poller':
        $cmd = "php ${config['install_dir']}/poller.php -h $hostname -r -f -d";
        $filename = "poller-$hostname.txt";
        break;
    case 'snmpwalk':
        $device = device_by_name(mres($hostname));

        $cmd = gen_snmpwalk_cmd($device, '.', ' -Onet');

        if ($debug) {
            $cmd .= ' 2>&1';
        }

        $filename = $device['os'] . '-' . $device['hostname'] . '.snmpwalk';
        break;
    case 'discovery':
        $cmd = "php ${config['install_dir']}/discovery.php -h $hostname -d";
        $filename = "discovery-$hostname.txt";
        break;
    default:
        echo 'You must specify a valid type';
        exit();
}

// ---- Output ----

if ($_GET['format'] == 'text') {
    header("Content-type: text/plain");
    header('X-Accel-Buffering: no');

    if (($fp = popen($cmd, "r"))) {
        while (!feof($fp)) {
            $line = stream_get_line($fp, 1024, PHP_EOL);
            echo preg_replace('/\033\[[\d;]+m/', '', $line) . PHP_EOL;
            ob_flush();
            flush(); // you have to flush buffer
        }
        fclose($fp);
    }
} elseif ($_GET['format'] == 'download') {
    ob_start();
    $output = shell_exec($cmd);
    ob_end_clean();

    $output = preg_replace('/\033\[[\d;]+m/', '', $output);

    file_download($filename, $output);
}
