<?php

use App\Models\Port;
use App\Models\PortVlan;
use App\Models\Vlan;
use LibreNMS\Util\Rewrite;
use LibreNMS\Util\Url;

$port_vlans = PortVlan::query()
    ->where('port_id', $port['port_id'])
    ->where('device_id', $device['device_id'])
    ->get();
$vlan_details = Vlan::query()
    ->where('device_id', $device['device_id'])
    ->whereIn('vlan_vlan', $port_vlans->pluck('vlan'))
    ->get()
    ->keyBy('vlan_vlan');
$otherports_by_vlan = PortVlan::query()
    ->where('device_id', $device['device_id'])
    ->whereIn('vlan', $port_vlans->pluck('vlan'))
    ->whereHas('port')
    ->with('port.device')
    ->get()
    ->groupBy('vlan');
$ifvlan_ports_by_vlan = Port::query()
    ->with('device')
    ->where('device_id', $device['device_id'])
    ->whereIn('ifVlan', $port_vlans->pluck('vlan'))
    ->get()
    ->groupBy('ifVlan');

echo '<table class="table table-hover table-striped table-condensed">';

echo '<tr><th>VLAN</th><th>Description</th><th>Cost</th><th>Priority</th><th>State</th><th>Other Ports</th></tr>';

foreach ($port_vlans as $vlan) {
    $details = $vlan_details->get($vlan->vlan);
    if (! $details) {
        continue;
    }

    echo '<tr>';

    echo '<td width=100 class=list-large> Vlan ' . e($vlan->vlan) . '</td>';
    echo '<td width=200 class=box-desc>' . e($details->vlan_name) . '</td>';

    if ($vlan->state == 'blocking') {
        $class = 'red';
    } elseif ($vlan->state == 'forwarding') {
        $class = 'green';
    } else {
        $class = 'none';
    }

    echo '<td>' . e($vlan->cost) . '</td><td>' . e($vlan->priority) . "</td><td class=$class>" . e($vlan->state) . '</td>';

    $traverse_ifvlan = true;
    $vlan_ports = [];
    foreach ($otherports_by_vlan->get($vlan->vlan, collect()) as $port_vlan) {
        $untagged = $port_vlan->getRawOriginal('untagged');
        if ($untagged) {
            $traverse_ifvlan = false;
        }
        $vlan_ports[$port_vlan->port->ifIndex] = ['port' => $port_vlan->port, 'untagged' => $untagged];
    }

    if ($traverse_ifvlan) {
        foreach ($ifvlan_ports_by_vlan->get($vlan->vlan, collect()) as $otherport) {
            $vlan_ports[$otherport->ifIndex] = ['port' => $otherport, 'untagged' => '1'];
        }
    }

    ksort($vlan_ports);

    echo '<td>';
    $vsep = '';
    foreach ($vlan_ports as $otherport) {
        echo $vsep . Url::portLink($otherport['port'], Rewrite::shortenIfName($otherport['port']->ifDescr));
        if ($otherport['untagged']) {
            echo '(U)';
        }

        $vsep = ', ';
    }

    echo '</td>';
    echo '</tr>';
}//end foreach

echo '</table>';
