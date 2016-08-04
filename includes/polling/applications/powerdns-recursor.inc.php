<?php
/**
 * powerdns-recursor.inc.php
 *
 * PowerDNS Recursor application polling module
 * Capable of collecting stats from the agent or via direct connection
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

global $config;
$data = '';
$name = 'powerdns-recursor';
$app_id = $app['app_id'];

echo ' ' . $name;

if ($agent_data['app'][$name]) {
    $data = $agent_data['app'][$name];
} elseif (isset($config['apps'][$name]['api-key'])) {
    if (isset($config['apps'][$name]['port']) && is_numeric($config['apps'][$name]['port'])) {
        $port = $config['apps'][$name]['port'];
    } else {
        $port = '8082';
    }

    $scheme = (isset($config['apps'][$name]['https']) && $config['apps'][$name]['https']) ? 'https://' : 'http://';

    d_echo("\nNo Agent Data. Attempting to connect directly to the powerdns-recursor server $scheme" . $device['hostname'] . ":$port\n");
    $context = stream_context_create(array('http' => array('header' => 'X-API-Key: ' . $config['apps'][$name]['api-key'])));
    $data = file_get_contents($scheme . $device['hostname'] . ':' . $port . '/servers/localhost/statistics', false, $context);
}

if (!empty($data)) {
    $rrd_def = array(
        'all-outqueries' => 'DS:all-outqueries:DERIVE:600:0:U',
        'answers-slow' => 'DS:answers-slow:DERIVE:600:0:U',
        'answers0-1' => 'DS:answers0-1:DERIVE:600:0:U',
        'answers1-10' => 'DS:answers1-10:DERIVE:600:0:U',
        'answers10-100' => 'DS:answers10-100:DERIVE:600:0:U',
        'answers100-1000' => 'DS:answers100-1000:DERIVE:600:0:U',
        'cache-entries' => 'DS:cache-entries:GAUGE:600:0:U',
        'cache-hits' => 'DS:cache-hits:DERIVE:600:0:U',
        'cache-misses' => 'DS:cache-misses:DERIVE:600:0:U',
        'case-mismatches' => 'DS:case-mismatches:DERIVE:600:0:U',
        'chain-resends' => 'DS:chain-resends:DERIVE:600:0:U',
        'client-parse-errors' => 'DS:client-parse-errors:DERIVE:600:0:U',
        'concurrent-queries' => 'DS:concurrent-queries:GAUGE:600:0:U',
        'dlg-only-drops' => 'DS:dlg-only-drops:DERIVE:600:0:U',
        'dont-outqueries' => 'DS:dont-outqueries:DERIVE:600:0:U',
        'edns-ping-matches' => 'DS:edns-ping-matches:DERIVE:600:0:U',
        'edns-ping-mismatches' => 'DS:edns-ping-mismatches:DERIVE:600:0:U',
        'failed-host-entries' => 'DS:failed-host-entries:GAUGE:600:0:U',
        'ipv6-outqueries' => 'DS:ipv6-outqueries:DERIVE:600:0:U',
        'ipv6-questions' => 'DS:ipv6-questions:DERIVE:600:0:U',
        'malloc-bytes' => 'DS:malloc-bytes:GAUGE:600:0:U',
        'max-mthread-stack' => 'DS:max-mthread-stack:GAUGE:600:0:U',
        'negcache-entries' => 'DS:negcache-entries:GAUGE:600:0:U',
        'no-packet-error' => 'DS:no-packet-error:DERIVE:600:0:U',
        'noedns-outqueries' => 'DS:noedns-outqueries:DERIVE:600:0:U',
        'noerror-answers' => 'DS:noerror-answers:DERIVE:600:0:U',
        'noping-outqueries' => 'DS:noping-outqueries:DERIVE:600:0:U',
        'nsset-invalidations' => 'DS:nsset-invalidations:DERIVE:600:0:U',
        'nsspeeds-entries' => 'DS:nsspeeds-entries:GAUGE:600:0:U',
        'nxdomain-answers' => 'DS:nxdomain-answers:DERIVE:600:0:U',
        'outgoing-timeouts' => 'DS:outgoing-timeouts:DERIVE:600:0:U',
        'over-capacity-drops' => 'DS:over-capacity-drops:DERIVE:600:0:U',
        'packetcache-entries' => 'DS:packetcache-entries:GAUGE:600:0:U',
        'packetcache-hits' => 'DS:packetcache-hits:DERIVE:600:0:U',
        'packetcache-misses' => 'DS:packetcache-misses:DERIVE:600:0:U',
        'policy-drops' => 'DS:policy-drops:DERIVE:600:0:U',
        'qa-latency' => 'DS:qa-latency:GAUGE:600:0:U',
        'questions' => 'DS:questions:DERIVE:600:0:U',
        'resource-limits' => 'DS:resource-limits:DERIVE:600:0:U',
        'security-status' => 'DS:security-status:GAUGE:600:0:U',
        'server-parse-errors' => 'DS:server-parse-errors:DERIVE:600:0:U',
        'servfail-answers' => 'DS:servfail-answers:DERIVE:600:0:U',
        'spoof-prevents' => 'DS:spoof-prevents:DERIVE:600:0:U',
        'sys-msec' => 'DS:sys-msec:DERIVE:600:0:U',
        'tcp-client-overflow' => 'DS:tcp-client-overflow:DERIVE:600:0:U',
        'tcp-clients' => 'DS:tcp-clients:GAUGE:600:0:U',
        'tcp-outqueries' => 'DS:tcp-outqueries:DERIVE:600:0:U',
        'tcp-questions' => 'DS:tcp-questions:DERIVE:600:0:U',
        'throttle-entries' => 'DS:throttle-entries:GAUGE:600:0:U',
        'throttled-out' => 'DS:throttled-out:DERIVE:600:0:U',
        'throttled-outqueries' => 'DS:throttled-outquerie:DERIVE:600:0:U',
        'too-old-drops' => 'DS:too-old-drops:DERIVE:600:0:U',
        'unauthorized-tcp' => 'DS:unauthorized-tcp:DERIVE:600:0:U',
        'unauthorized-udp' => 'DS:unauthorized-udp:DERIVE:600:0:U',
        'unexpected-packets' => 'DS:unexpected-packets:DERIVE:600:0:U',
        'unreachables' => 'DS:unreachables:DERIVE:600:0:U',
        'uptime' => 'DS:uptime:DERIVE:600:0:U',
        'user-msec' => 'DS:user-msec:DERIVE:600:0:U',
    );

    //decode and flatten the data
    $stats = array();
    foreach (json_decode($data, true) as $stat) {
        $stats[$stat['name']] = $stat['value'];
    }
    d_echo($stats);

    // only the stats we store in rrd
    $fields = array();
    foreach ($rrd_def as $key => $value) {
        if (isset($stats[$key])) {
            $fields[$key] = $stats[$key];
        } else {
            $fields[$key] = 'U';
        }
    }

    $rrd_name = array('app', 'powerdns', 'recursor', $app_id);
    $tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
    data_update($device, 'app', $tags, $fields);
}

unset($data, $stats, $rrd_def, $rrd_name, $rrd_keys, $tags, $fields);
