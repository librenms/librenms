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
 * @link       http://librenms.org
 * @copyright  2017 LibreNMS
 * @author     crcro <crc@nuamchefazi.ro>
*/

use LibreNMS\RRD\RrdDefinition;

$name = 'pi-hole';
$app_id = $app['app_id'];

if (!empty($agent_data['app'][$name])) {
    $rawdata = $agent_data['app'][$name];
} else {
    $options = '-O qv';
    $oid = '.1.3.6.1.4.1.8072.1.3.2.4.1.2.7.112.105.45.104.111.108.101';
    $rawdata = snmp_walk($device, $oid, $options);
}

if ($rawdata) {
    #Format Data
    $lines = explode("\n", $rawdata);
    $pihole = array();
    $metrics = array();
    foreach ($lines as $line) {
        list($var,$value) = explode(':', $line);
        $pihole[$var] = $value;
    }

    $rrd_name = array('app', $name, 'stats', $app_id);
    $rrd_def = RrdDefinition::make()
        ->addDataset('domains_blocked', 'GAUGE', 0)
        ->addDataset('dns_query', 'GAUGE', 0)
        ->addDataset('ads_blocked', 'GAUGE', 0)
        ->addDataset('ads_percentage', 'GAUGE', 0)
        ->addDataset('unique_domains', 'GAUGE', 0)
        ->addDataset('queries_forwarded', 'GAUGE', 0)
        ->addDataset('queries_cached', 'GAUGE', 0)
        ->addDataset('clients_ever_seen', 'GAUGE', 0)
        ->addDataset('unique_clients', 'GAUGE', 0);

    $fields = array(
        'domains_blocked' => $pihole['domains_being_blocked'],
        'dns_query' => $pihole['dns_queries_today'],
        'ads_blocked' => $pihole['ads_blocked_today'],
        'ads_percentage' => $pihole['ads_percentage_today'],
        'unique_domains' => $pihole['unique_domains'],
        'queries_forwarded' => $pihole['queries_forwarded'],
        'queries_cached' => $pihole['queries_cached'],
        'clients_ever_seen' => $pihole['clients_ever_seen'],
        'unique_clients' => $pihole['unique_clients'],
    );
    $metrics['stats'] = $fields;
    $tags = array('name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name);
    data_update($device, 'app', $tags, $fields);

    $rrd_name = array('app', $name, 'query_types', $app_id);
    $rrd_def = RrdDefinition::make()
        ->addDataset('query_a', 'GAUGE', 0)
        ->addDataset('query_aaaa', 'GAUGE', 0)
        ->addDataset('query_ptr', 'GAUGE', 0)
        ->addDataset('query_srv', 'GAUGE', 0);

    $fields = array(
        'query_a' => $pihole['A(IPv4)'],
        'query_aaaa' => $pihole['AAAA(IPv6)'],
        'query_ptr' => $pihole['PTR'],
        'query_srv' => $pihole['SRV'],
    );
    $metrics['query_types'] = $fields;
    $tags = array('name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name);
    data_update($device, 'app', $tags, $fields);

    $rrd_name = array('app', $name, 'clients', $app_id);
    $rrd_def = RrdDefinition::make()
        ->addDataset('clients_ever_seen', 'GAUGE', 0)
        ->addDataset('unique_clients', 'GAUGE', 0);

    $fields = array(
        'clients_ever_seen' => $pihole['clients_ever_seen'],
        'unique_clients' => $pihole['unique_clients'],
    );
    $metrics['clients'] = $fields;
    $tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
    data_update($device, 'app', $tags, $fields);

    update_application($app, $rawdata, $metrics);
}

unset($pihole, $rawdata, $line, $lines, $var, $value,$rrd_name, $rrd_def, $fields, $tags);
