<?php

use App\Models\Ipv4Mac;
use LibreNMS\Util\Mac;
use LibreNMS\Util\Rewrite;
use LibreNMS\Util\Url;

$param = [];

if (! isset($sort) || empty($sort)) {
    $sort = 'hostname ASC';
}
$sort_arr = explode(' ', trim($sort));
if ($sort_arr[0] === 'interface') {
    $sort_arr[0] = 'port_if_descr';
} elseif ($sort_arr[0] === 'hostname') {
    $sort_arr[0] = 'device_hostname';
}

if (isset($current)) {
    $page = $current;
} else {
    $page = 1;
}

$query = Ipv4Mac::hasAccess(Auth::user())
    ->with('port', 'device', 'remote_ports_maybe', 'remote_ports_maybe.device')
    ->withAggregate('port', 'ifDescr')
    ->withAggregate('device', 'hostname')
    ->orderBy(...$sort_arr);

if (is_numeric($vars['device_id'])) {
    $query->where('device_id', $vars['device_id']);
}

if (isset($vars['port_id']) && is_numeric($vars['port_id'])) {
    $query->where('port_id', $vars['port_id']);
}

if (isset($vars['searchPhrase']) && ! empty($vars['searchPhrase'])) {
    $ip_search = '%' . trim($vars['searchPhrase']) . '%';
    $mac_search = '%' . str_replace([':', ' ', '-', '.', '0x'], '', trim($vars['searchPhrase'])) . '%';

    if (isset($vars['searchby']) && $vars['searchby'] == 'ip') {
        $query->where('ipv4_address', 'like', $ip_search);
    } elseif (isset($vars['searchby']) && $vars['searchby'] == 'mac') {
        $query->where('mac_address', 'like', $mac_search);
    } else {
        $query->where(function ($q) use ($ip_search, $mac_search) {
            $q->where('ipv4_address', 'like', $ip_search)
                ->orWhere('mac_address', 'like', $mac_search);
        });
    }
}

$pag = $query->paginate($rowCount, page: $page);

foreach ($pag->items() as $arp) {
    if ($arp->port->ifInErrors_delta > 0 || $arp->port->ifOutErrors_delta > 0) {
        $error_img = generate_port_link($arp->port, "<i class='fa fa-flag fa-lg' style='color:red' aria-hidden='true'></i>", 'port_errors');
    } else {
        $error_img = '';
    }

    if ($arp->remote_ports_maybe) {
        $remote_port = $arp->remote_ports_maybe->first();
        if ($remote_port->port_id != $arp->port_id) {
            $arp_name = Url::deviceLink($remote_port->device);
            $arp_if = Url::portLink($remote_port);
        } else {
            $arp_name = 'Localhost';
            $arp_if = 'Local port';
        }
    } elseif ($arp->mac_address == $arp->port->ifPhysAddress) {
        $arp_name = 'Localhost';
        $arp_if = 'Local port';
    } else {
        unset($arp_name);
        unset($arp_if);
    }

    $mac = Mac::parse($arp->mac_address);
    $response[] = [
        'mac_address' => $mac->readable(),
        'mac_oui' => $mac->vendor(),
        'ipv4_address' => $arp->ipv4_address,
        'hostname' => Url::deviceLink($arp->device),
        'interface' => Url::portLink($arp->port, Rewrite::shortenIfName($arp->label)) . ' ' . $error_img,
        'remote_device' => $arp_name,
        'remote_interface' => $arp_if,
    ];
}//end foreach

$output = [
    'current' => $current,
    'rowCount' => $rowCount,
    'rows' => $response,
    'total' => $pag->total(),
];
echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
