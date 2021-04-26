<?php

use App\Models\Ipv4Address;
use App\Models\OspfArea;
use App\Models\OspfInstance;
use App\Models\OspfNbr;
use App\Models\OspfPort;
use LibreNMS\RRD\RrdDefinition;

$device_model = DeviceCache::getPrimary();
$vrfs_lite_cisco = empty($device['vrf_lite_cisco'])
    ? [['context_name' => null]]
    : $device['vrf_lite_cisco'];

foreach ($vrfs_lite_cisco as $vrf_lite) {
    $device['context_name'] = $vrf_lite['context_name'];

    echo ' Processes: ';

    // Pull data from device
    $ospf_instances_poll = snmpwalk_cache_oid($device, 'OSPF-MIB::ospfGeneralGroup', [], 'OSPF-MIB');
    d_echo($ospf_instances_poll);

    $ospf_instances = collect();
    foreach ($ospf_instances_poll as $ospf_instance_id => $ospf_entry) {
        // TODO add model listener from wireless polling PR #8607 for improved output
        $instance = OspfInstance::updateOrCreate([
            'device_id' => $device['device_id'],
            'ospf_instance_id' => $ospf_instance_id,
            'context_name' => $device['context_name'],
        ], $ospf_entry);

        $ospf_instances->push($instance);
    }

    // cleanup
    OspfInstance::query()
        ->where(['device_id' => $device['device_id'], 'context_name' => $device['context_name']])
        ->whereNotIn('id', $ospf_instances->pluck('id'))->delete();

    $instance_count = $ospf_instances->count();
    echo $instance_count;
    if ($instance_count == 0) {
        // if there are no instances, don't check for areas, neighbors, and ports
        return;
    }

    echo ' Areas: ';

    // Pull data from device
    $ospf_areas_poll = snmpwalk_cache_oid($device, 'OSPF-MIB::ospfAreaEntry', [], 'OSPF-MIB');
    d_echo($ospf_areas_poll);

    $ospf_areas = collect();
    foreach ($ospf_areas_poll as $ospf_area_id => $ospf_area) {
        $area = OspfArea::updateOrCreate([
            'device_id' => $device['device_id'],
            'ospfAreaId' => $ospf_area_id,
            'context_name' => $device['context_name'],
        ], $ospf_area);

        $ospf_areas->push($area);
    }

    // cleanup
    OspfArea::query()
        ->where(['device_id' => $device['device_id'], 'context_name' => $device['context_name']])
        ->whereNotIn('id', $ospf_areas->pluck('id'))->delete();

    echo $ospf_areas->count();

    echo ' Ports: ';

    // Pull data from device
    $ospf_ports_poll = snmpwalk_cache_oid($device, 'OSPF-MIB::ospfIfEntry', [], 'OSPF-MIB');
    d_echo($ospf_ports_poll);

    $ospf_ports = collect();
    foreach ($ospf_ports_poll as $ospf_port_id => $ospf_port) {
        // find port_id
        if ($ospf_port['ospfAddressLessIf']) {
            $ospf_port['port_id'] = (int) $device_model->ports()->where('ifIndex', $ospf_port['ospfAddressLessIf'])->value('port_id');
        } else {
            // FIXME force same device ?
            $ospf_port['port_id'] = (int) Ipv4Address::query()
                ->where('ipv4_address', $ospf_port['ospfIfIpAddress'])
                ->where('context_name', $device['context_name'])
                ->value('port_id');
        }

        $port = OspfPort::updateOrCreate([
            'device_id' => $device['device_id'],
            'ospf_port_id' => $ospf_port_id,
            'context_name' => $device['context_name'],
        ], $ospf_port);

        $ospf_ports->push($port);
    }

    // cleanup
    OspfPort::query()
        ->where(['device_id' => $device['device_id'], 'context_name' => $device['context_name']])
        ->whereNotIn('id', $ospf_ports->pluck('id'))->delete();

    echo $ospf_ports->count();

    echo ' Neighbours: ';

    // Pull data from device
    $ospf_nbrs_poll = snmpwalk_cache_oid($device, 'OSPF-MIB::ospfNbrEntry', [], 'OSPF-MIB');
    d_echo($ospf_nbrs_poll);

    $ospf_neighbours = collect();
    foreach ($ospf_nbrs_poll as $ospf_nbr_id => $ospf_nbr) {
        // get neighbor port_id
        $ospf_nbr['port_id'] = Ipv4Address::query()
            ->where('ipv4_address', $ospf_nbr['ospfNbrIpAddr'])
            ->where('context_name', $device['context_name'])
            ->value('port_id');
        $ospf_nbr['ospf_nbr_id'] = $ospf_nbr_id;

        $neighbour = OspfNbr::updateOrCreate([
            'device_id' => $device['device_id'],
            'ospf_nbr_id' => $ospf_nbr_id,
            'context_name' => $device['context_name'],
        ], $ospf_nbr);

        $ospf_neighbours->push($neighbour);
    }

    // cleanup
    OspfNbr::query()
        ->where(['device_id' => $device['device_id'], 'context_name' => $device['context_name']])
        ->whereNotIn('id', $ospf_neighbours->pluck('id'))->delete();

    echo $ospf_neighbours->count();

    echo ' TOS Metrics: ';

    // Pull data from device
    $ospf_tos_poll = snmpwalk_cache_oid($device, 'OSPF-MIB::ospfIfMetricEntry', [], 'OSPF-MIB');
    d_echo($ospf_tos_poll);

    $ospf_tos_metrics = collect();
    foreach ($ospf_tos_poll as $ospf_tos_id => $ospf_tos) {
        // get ospf_port_id
        $ospf_tos['ospf_port_id'] = OspfPort::query()
            ->where('ospfIfIpAddress', $ospf_tos['ospfIfMetricIpAddress'])
            ->where('context_name', $device['context_name'])
            ->value('ospf_port_id');
        $tos = OspfPort::updateOrCreate([
            'device_id' => $device['device_id'],
            'ospf_port_id' => $ospf_tos['ospf_port_id'],
            'context_name' => $device['context_name'],
        ], $ospf_tos);

        $ospf_tos_metrics->push($tos);
    }

    echo $ospf_tos_metrics->count();
}

unset($device['context_name'], $vrfs_lite_cisco, $vrf_lite);

if ($instance_count) {
    // Create device-wide statistics RRD
    $rrd_def = RrdDefinition::make()
        ->addDataset('instances', 'GAUGE', 0, 1000000)
        ->addDataset('areas', 'GAUGE', 0, 1000000)
        ->addDataset('ports', 'GAUGE', 0, 1000000)
        ->addDataset('neighbours', 'GAUGE', 0, 1000000);

    $fields = [
        'instances'   => $instance_count,
        'areas'       => $ospf_areas->count(),
        'ports'       => $ospf_ports->count(),
        'neighbours'  => $ospf_neighbours->count(),
    ];

    $tags = compact('rrd_def');
    data_update($device, 'ospf-statistics', $tags, $fields);
}

echo PHP_EOL;

unset(
    $ospf_instances,
    $instance_count,
    $ospf_areas,
    $ospf_ports,
    $ospf_neighbours,
    $ospf_instances_poll,
    $ospf_areas_poll,
    $ospf_ports_poll,
    $ospf_nbrs_poll,
    $ospf_entry,
    $instance,
    $ospf_instance_id,
    $ospf_area,
    $area,
    $ospf_area_id,
    $ospf_port,
    $port,
    $ospf_port_id,
    $ospf_nbr,
    $neighbour,
    $ospf_nbr_id,
    $ospf_tos,
    $tos,
    $ospf_tos_id,
    $rrd_def,
    $fields,
    $tags
);
