<?php
/*
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @package    LibreNMS
 * @subpackage pi-hole
 * @link       https://www.librenms.org
 * @copyright  2017 LibreNMS
 * @author     crcro <crc@nuamchefazi.ro>
*/

use LibreNMS\RRD\RrdDefinition;

$name = 'pi-hole';
$app_id = $app['app_id'];
$options = '-Oqv';
$oid = '.1.3.6.1.4.1.8072.1.3.2.4.1.2.7.112.105.45.104.111.108.101';

$pihole = snmp_walk($device, $oid, $options);

if ($pihole) {
    [$domains_blocked, $dns_query, $ads_blocked, $ads_percentage, $unique_domains, $queries_forwarded, $queries_cached, $query_a, $query_aaaa, $query_ptr, $query_srv] = explode("\n", $pihole);

    $rrd_name = ['app', $name, $app_id];
    $rrd_def = RrdDefinition::make()
        ->addDataset('domains_blocked', 'GAUGE', 0)
        ->addDataset('dns_query', 'GAUGE', 0)
        ->addDataset('ads_blocked', 'GAUGE', 0)
        ->addDataset('ads_percentage', 'GAUGE', 0)
        ->addDataset('unique_domains', 'GAUGE', 0)
        ->addDataset('queries_forwarded', 'GAUGE', 0)
        ->addDataset('queries_cached', 'GAUGE', 0)
        ->addDataset('query_a', 'GAUGE', 0)
        ->addDataset('query_aaaa', 'GAUGE', 0)
        ->addDataset('query_ptr', 'GAUGE', 0)
        ->addDataset('query_srv', 'GAUGE', 0);

    $fields = [
        'domains_blocked' => $domains_blocked,
        'dns_query' => $dns_query,
        'ads_blocked' => $ads_blocked,
        'ads_percentage' => $ads_percentage,
        'unique_domains' => $unique_domains,
        'queries_forwarded' => $queries_forwarded,
        'queries_cached' => $queries_cached,
        'query_a' => $query_a,
        'query_aaaa' => $query_aaaa,
        'query_ptr' => $query_ptr,
        'query_srv' => $query_srv,
    ];

    $tags = ['name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
    data_update($device, 'app', $tags, $fields);
    update_application($app, $pihole, $fields);
}

unset($pihole);
