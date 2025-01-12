<?php

use App\Models\Port;
use LibreNMS\Util\Clean;
use LibreNMS\Util\Rewrite;
use LibreNMS\Util\Url;

if (is_numeric($vars['id']) && ($auth || port_permitted($vars['id']))) {
    $port = cleanPort(Port::with('device')->find($vars['id']));
    $title = Url::deviceLink($port->device) . ' :: Port  ' . Url::portLink($port);

    $graph_title = $port->device->shortDisplayName() . '::' . strtolower(Rewrite::shortenIfName($port->ifDescr));

    if (($port->ifAlias != '') && ($port->ifAlias != $port->ifDescr)) {
        $title .= ', ' . Clean::html($port->ifAlias, []);
        $graph_title .= '::' . Clean::html($port->ifAlias, []);
    }

    $auth = true;

    $rrd_filename = get_port_rrdfile_path($device->hostname, $port->port_id);
}
