<?php
/*
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
* @package    LibreNMS
* @link       https://www.librenms.org
* @copyright  2017 LibreNMS
* @author     Cercel Valentin <crc@nuamchefazi.ro>
*/

use LibreNMS\RRD\RrdDefinition;

$name = 'powerdns-dnsdist';
$app_id = $app['app_id'];
$options = '-Oqv';
//NET-SNMP-EXTEND-MIB::nsExtendOutputFull."powerdns-dnsdist"
$oid = '.1.3.6.1.4.1.8072.1.3.2.3.1.2.16.112.111.119.101.114.100.110.115.45.100.110.115.100.105.115.116';

d_echo($name);

$powerdns_dnsdist = snmp_walk($device, $oid, $options);

if (is_string($powerdns_dnsdist)) {
    [$cache_hits, $cache_miss, $downstream_err, $downstream_timeout, $dynamic_block_size, $dynamic_blocked, $queries_count, $queries_recursive, $queries_empty, $queries_drop_no_policy, $queries_drop_nc, $queries_drop_nc_answer, $queries_self_answer, $queries_serv_fail, $queries_failure, $queries_acl_drop, $rule_drop, $rule_nxdomain, $rule_refused, $latency_100, $latency_1000, $latency_10000, $latency_1000000, $latency_slow, $latency_0_1, $latency_1_10, $latency_10_50, $latency_50_100, $latency_100_1000] = explode("\n", $powerdns_dnsdist);

    $rrd_name = ['app', $name, $app_id];

    $rrd_def = RrdDefinition::make()
        ->addDataset('cache_hits', 'COUNTER', 0)
        ->addDataset('cache_miss', 'COUNTER', 0)
        ->addDataset('downstream_err', 'COUNTER', 0)
        ->addDataset('downstream_timeout', 'COUNTER', 0)
        ->addDataset('dynamic_block_size', 'COUNTER', 0)
        ->addDataset('dynamic_blocked', 'COUNTER', 0)
        ->addDataset('queries_count', 'COUNTER', 0)
        ->addDataset('queries_recursive', 'COUNTER', 0)
        ->addDataset('queries_empty', 'COUNTER', 0)
        ->addDataset('queries_drop_no_policy', 'COUNTER', 0)
        ->addDataset('queries_drop_nc', 'COUNTER', 0)
        ->addDataset('queries_drop_nc_answer', 'COUNTER', 0)
        ->addDataset('queries_self_answer', 'COUNTER', 0)
        ->addDataset('queries_serv_fail', 'COUNTER', 0)
        ->addDataset('queries_failure', 'COUNTER', 0)
        ->addDataset('queries_acl_drop', 'COUNTER', 0)
        ->addDataset('rule_drop', 'COUNTER', 0)
        ->addDataset('rule_nxdomain', 'COUNTER', 0)
        ->addDataset('rule_refused', 'COUNTER', 0)
        ->addDataset('latency_100', 'GAUGE', 0)
        ->addDataset('latency_1000', 'GAUGE', 0)
        ->addDataset('latency_10000', 'GAUGE', 0)
        ->addDataset('latency_1000000', 'GAUGE', 0)
        ->addDataset('latency_slow', 'COUNTER', 0)
        ->addDataset('latency_0_1', 'COUNTER', 0)
        ->addDataset('latency_1_10', 'COUNTER', 0)
        ->addDataset('latency_10_50', 'COUNTER', 0)
        ->addDataset('latency_50_100', 'COUNTER', 0)
        ->addDataset('latency_100_1000', 'COUNTER', 0);

    $fields = [
        'cache_hits' => $cache_hits,
        'cache_miss' => $cache_miss,
        'downstream_err' => $downstream_err,
        'downstream_timeout' => $downstream_timeout,
        'dynamic_block_size' => $dynamic_block_size,
        'dynamic_blocked' => $dynamic_blocked,
        'queries_count' => $queries_count,
        'queries_recursive' => $queries_recursive,
        'queries_empty' => $queries_empty,
        'queries_self_answer' => $queries_self_answer,
        'queries_drop_no_policy' => $queries_drop_no_policy,
        'queries_drop_nc' => $queries_drop_nc,
        'queries_drop_nc_answer' => $queries_drop_nc_answer,
        'queries_failure' => $queries_failure,
        'queries_acl_drop' => $queries_acl_drop,
        'queries_serv_fail' => $queries_serv_fail,
        'rule_drop' => $rule_drop,
        'rule_nxdomain' => $rule_nxdomain,
        'rule_refused' => $rule_refused,
        'latency_100' => $latency_100,
        'latency_1000' => $latency_1000,
        'latency_10000' => $latency_10000,
        'latency_1000000' => $latency_1000000,
        'latency_slow' => $latency_slow,
        'latency_0_1' => $latency_0_1,
        'latency_1_10' => $latency_1_10,
        'latency_10_50' => $latency_10_50,
        'latency_50_100' => $latency_50_100,
        'latency_100_1000' => $latency_100_1000,
    ];

    $tags = ['name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
    data_update($device, 'app', $tags, $fields);
    update_application($app, $powerdns_dnsdist, $fields);
}
unset($powerdns_dnsdist);
