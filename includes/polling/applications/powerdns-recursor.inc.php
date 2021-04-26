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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2016 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

use LibreNMS\Config;
use LibreNMS\RRD\RrdDefinition;

$data = '';
$name = 'powerdns-recursor';
$app_id = $app['app_id'];

echo ' ' . $name;

if ($agent_data['app'][$name]) {
    $data = $agent_data['app'][$name];
} elseif (Config::has('apps.powerdns-recursor.api-key')) {
    $port = Config::get('apps.powerdns-recursor.port', 8082);
    $scheme = Config::get('apps.powerdns-recursor.https') ? 'https://' : 'http://';

    d_echo("\nNo Agent Data. Attempting to connect directly to the powerdns-recursor server $scheme" . $device['hostname'] . ":$port\n");
    $context = stream_context_create(['http' => ['header' => 'X-API-Key: ' . Config::get('apps.powerdns-recursor.api-key')]]);
    $data = file_get_contents($scheme . $device['hostname'] . ':' . $port . '/api/v1/servers/localhost/statistics', false, $context);
    if ($data === false) {
        $data = file_get_contents($scheme . $device['hostname'] . ':' . $port . '/servers/localhost/statistics', false, $context);
    }
} else {
    // nsExtendOutputFull."powerdns-recursor"
    $oid = '.1.3.6.1.4.1.8072.1.3.2.3.1.2.17.112.111.119.101.114.100.110.115.45.114.101.99.117.114.115.111.114';
    $data = stripslashes(snmp_get($device, $oid, '-Oqv'));
}

if (! empty($data)) {
    $ds_list = [
        'all-outqueries' => 'DERIVE',
        'answers-slow' => 'DERIVE',
        'answers0-1' => 'DERIVE',
        'answers1-10' => 'DERIVE',
        'answers10-100' => 'DERIVE',
        'answers100-1000' => 'DERIVE',
        'cache-entries' => 'GAUGE',
        'cache-hits' => 'DERIVE',
        'cache-misses' => 'DERIVE',
        'case-mismatches' => 'DERIVE',
        'chain-resends' => 'DERIVE',
        'client-parse-errors' => 'DERIVE',
        'concurrent-queries' => 'GAUGE',
        'dlg-only-drops' => 'DERIVE',
        'dont-outqueries' => 'DERIVE',
        'edns-ping-matches' => 'DERIVE',
        'edns-ping-mismatches' => 'DERIVE',
        'failed-host-entries' => 'GAUGE',
        'ipv6-outqueries' => 'DERIVE',
        'ipv6-questions' => 'DERIVE',
        'malloc-bytes' => 'GAUGE',
        'max-mthread-stack' => 'GAUGE',
        'negcache-entries' => 'GAUGE',
        'no-packet-error' => 'DERIVE',
        'noedns-outqueries' => 'DERIVE',
        'noerror-answers' => 'DERIVE',
        'noping-outqueries' => 'DERIVE',
        'nsset-invalidations' => 'DERIVE',
        'nsspeeds-entries' => 'GAUGE',
        'nxdomain-answers' => 'DERIVE',
        'outgoing-timeouts' => 'DERIVE',
        'over-capacity-drops' => 'DERIVE',
        'packetcache-entries' => 'GAUGE',
        'packetcache-hits' => 'DERIVE',
        'packetcache-misses' => 'DERIVE',
        'policy-drops' => 'DERIVE',
        'qa-latency' => 'GAUGE',
        'questions' => 'DERIVE',
        'resource-limits' => 'DERIVE',
        'security-status' => 'GAUGE',
        'server-parse-errors' => 'DERIVE',
        'servfail-answers' => 'DERIVE',
        'spoof-prevents' => 'DERIVE',
        'sys-msec' => 'DERIVE',
        'tcp-client-overflow' => 'DERIVE',
        'tcp-clients' => 'GAUGE',
        'tcp-outqueries' => 'DERIVE',
        'tcp-questions' => 'DERIVE',
        'throttle-entries' => 'GAUGE',
        'throttled-out' => 'DERIVE',
        'throttled-outqueries' => 'DERIVE',
        'too-old-drops' => 'DERIVE',
        'unauthorized-tcp' => 'DERIVE',
        'unauthorized-udp' => 'DERIVE',
        'unexpected-packets' => 'DERIVE',
        'unreachables' => 'DERIVE',
        'uptime' => 'DERIVE',
        'user-msec' => 'DERIVE',
    ];

    //decode and flatten the data
    $stats = [];
    foreach (json_decode($data, true) as $stat) {
        $stats[$stat['name']] = $stat['value'];
    }
    d_echo($stats);

    // only the stats we store in rrd
    $rrd_def = new RrdDefinition();
    $fields = [];
    foreach ($ds_list as $key => $type) {
        $rrd_def->addDataset($key, $type, 0);

        if (isset($stats[$key])) {
            $fields[$key] = $stats[$key];
        } else {
            $fields[$key] = 'U';
        }
    }

    $rrd_name = ['app', 'powerdns', 'recursor', $app_id];
    $tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
    data_update($device, 'app', $tags, $fields);
    update_application($app, $data, $fields);
}

unset($data, $stats, $rrd_def, $rrd_name, $rrd_keys, $tags, $fields);
