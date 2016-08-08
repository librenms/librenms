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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2016 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

echo ' rrdcached';

$data = "";

if ($agent_data['app']['rrdcached']) {
    $data = $agent_data['app']['rrdcached'];
} else {
    d_echo("\nNo Agent Data. Attempting to connect directly to the rrdcached server " . $device['hostname'] . ":42217\n");

    $sock = fsockopen($device['hostname'], 42217, $errno, $errstr, 5);

    if (!$sock && $device['hostname'] == 'localhost') {
        if (file_exists('/var/run/rrdcached.sock')) {
            $sock = fsockopen('unix:///var/run/rrdcached.sock');
        } elseif (file_exists('/run/rrdcached.sock')) {
            $sock = fsockopen('unix:///run/rrdcached.sock');
        } elseif (file_exists('/tmp/rrdcached.sock')) {
            $sock = fsockopen('unix:///tmp/rrdcached.sock');
        }
    }

    if ($sock) {
        fwrite($sock, "STATS\n");
        $max = -1;
        $count = 0;
        while ($max == -1 || $count < $max) {
            $data .= fgets($sock, 128);
            if ($max == -1) {
                $tmp_max = explode(' ', $data);
                $max     = $tmp_max[0]+1;
            }
            $count++;
        }
        fclose($sock);
    } else {
        d_echo("ERROR: $errno - $errstr\n");
    }
}

$rrd_filename = $config['rrd_dir'].'/'.$device['hostname'].'/app-rrdcached-'.$app['app_id'].'.rrd';


if (!is_file($rrd_filename)) {
    rrdtool_create(
        $rrd_filename,
        '--step 300
        DS:queue_length:GAUGE:600:0:U
        DS:updates_received:COUNTER:600:0:U
        DS:flushes_received:COUNTER:600:0:U
        DS:updates_written:COUNTER:600:0:U
        DS:data_sets_written:COUNTER:600:0:U
        DS:tree_nodes_number:GAUGE:600:0:U
        DS:tree_depth:GAUGE:600:0:U
        DS:journal_bytes:COUNTER:600:0:U
        DS:journal_rotate:COUNTER:600:0:U
        '.$config['rrd_rra']
    );
}
$fields = array();
foreach (explode("\n", $data) as $line) {
    $split = explode(': ', $line);
    if (count($split) == 2) {
        $name = strtolower(preg_replace('/[A-Z]/', '_$0', lcfirst($split[0])));
        $fields[$name] = $split[1];
    }
}

rrdtool_update($rrd_filename, $fields);

$tags = array('name' => 'rrdcached', 'app_id' => $app['app_id']);
influx_update($device,'app',$tags,$fields);

unset($data);
unset($rrd_filename);
unset($fields);
unset($tags);
