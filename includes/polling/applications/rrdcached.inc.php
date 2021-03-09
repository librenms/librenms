<?php
/**
 * rrdcached.inc.php
 *
 * rrdcached application polling module
 * Capable of collecting stats from the agent or via direct connection
 * Only the default tcp port is supported, and unix sockets only work on localhost
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

use LibreNMS\RRD\RrdDefinition;

echo ' rrdcached';

$data = '';
$name = 'rrdcached';
$app_id = $app['app_id'];

if ($agent_data['app'][$name]) {
    $data = $agent_data['app'][$name];
} else {
    d_echo("\nNo Agent Data. Attempting to connect directly to the rrdcached server " . $device['hostname'] . ":42217\n");

    $sock = fsockopen($device['hostname'], 42217, $errno, $errstr, 5);

    if (! $sock) {
        d_echo("\nNo Socket to rrdcached server " . $device['hostname'] . ":42217 try to get rrdcached from SNMP\n");
        $oid = '.1.3.6.1.4.1.8072.1.3.2.3.1.2.9.114.114.100.99.97.99.104.101.100';
        $result = snmp_get($device, $oid, '-Oqv');
        $data = trim($result, '"');
        $data = str_replace("<<<rrdcached>>>\n", '', $data);
    }
    if (strlen($data) < 100) {
        $socket = \LibreNMS\Config::get('rrdcached');
        if (substr($socket, 0, 6) == 'unix:/') {
            $socket_file = substr($socket, 5);
            if (file_exists($socket_file)) {
                $sock = fsockopen('unix://' . $socket_file);
            }
        }
        d_echo("\nNo SnmpData " . $device['hostname'] . ' fallback to local rrdcached unix://' . $socket_file . "\n");
    }
    if ($sock) {
        fwrite($sock, "STATS\n");
        $max = -1;
        $count = 0;
        while ($max == -1 || $count < $max) {
            $data .= fgets($sock, 128);
            if ($max == -1) {
                $tmp_max = explode(' ', $data);
                $max = $tmp_max[0] + 1;
            }
            $count++;
        }
        fclose($sock);
    } elseif (strlen($data) < 100) {
        d_echo("ERROR: $errno - $errstr\n");
    }
}

$rrd_name = ['app', $name, $app_id];
$rrd_def = RrdDefinition::make()
    ->addDataset('queue_length', 'GAUGE', 0)
    ->addDataset('updates_received', 'COUNTER', 0)
    ->addDataset('flushes_received', 'COUNTER', 0)
    ->addDataset('updates_written', 'COUNTER', 0)
    ->addDataset('data_sets_written', 'COUNTER', 0)
    ->addDataset('tree_nodes_number', 'GAUGE', 0)
    ->addDataset('tree_depth', 'GAUGE', 0)
    ->addDataset('journal_bytes', 'COUNTER', 0)
    ->addDataset('journal_rotate', 'COUNTER', 0);

$fields = [];
foreach (explode("\n", $data) as $line) {
    $split = explode(': ', $line);
    if (count($split) == 2) {
        $ds = strtolower(preg_replace('/[A-Z]/', '_$0', lcfirst($split[0])));
        $fields[$ds] = $split[1];
    }
}

$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);
update_application($app, $data, $fields);

unset($data, $rrd_name, $rrd_def, $fields, $tags);
