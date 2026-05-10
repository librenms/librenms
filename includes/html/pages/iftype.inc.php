<table class="table table-condensed table-hover table-striped">
<?php

use App\Facades\DeviceCache;
use App\Facades\LibrenmsConfig;
use App\Facades\PortCache;
use App\Facades\Rrd;
use LibreNMS\Util\Url;
use App\Models\PortGroup;

$types_array = explode(',', strip_tags((string) $vars['type']));
$port_group = $vars['group'] ? PortGroup::find($vars['group'])
                                        ->ports()
                                        ->join('devices', 'ports.device_id', 'devices.device_id')
                                        ->select('devices.*', 'ports.*')
                                        ->get()
                                        ->toArray() : null;
$ports = $port_group ?? get_ports_from_type($types_array);

$if_list = implode(',', array_map(fn ($port) => $port['port_id'], $ports));

// show title from config file (but ucwords it)
$ctypes = collect(LibrenmsConfig::get('custom_descr', []))->keyBy(function ($descr) {
    if (is_array($descr)) {
        return strtolower((string) $descr[0]);
    }

    return strtolower((string) $descr);
});
array_walk($types_array, function (&$type) use ($ctypes): void {
    $name = $ctypes->get(strtolower((string) $type), $type);
    $type = ucwords(is_array($name) ? $name[0] : $name);
});

$types = implode(' + ', $types_array);

echo "<tr class='iftype'>
    <td colspan='5'><span class=list-large>Total Graph for ports of type : " . $types . '</span><br />';

if ($if_list) {
    $graph_type = 'multiport_bits_separate';
    $port['port_id'] = $if_list;

    include 'includes/html/print-interface-graphs.inc.php';

    echo '</td></tr>';

    foreach ($ports as $port) {
        //TODO: Convert to Eloquent with device
        $port = PortCache::get($port['port_id']);
        $done = 'yes';
        unset($class);
        $port->ifAlias = str_ireplace($port->type . ': ', '', $port->ifAlias);
        $port->ifAlias = str_ireplace('[PNI]', 'Private', $port->ifAlias);
        $ifclass = ifclass($port->ifOperStatus, $port->ifAdminStatus);

        echo "<tr class='iftype'>
            <td><span class=list-large>" . Url::portLink($port, $port->port_descr_descr) . "</span><br />
            <span class=interface-desc style='float: left;'>" . Url::deviceLink($port->device) . ' - ' . Url::portLink($port) . ' </span></td>
            <td>' . $port->port_descr_speed . '</td>
            <td>' . $port->port_descr_circuit . '</td>
            <td>' . $port->port_descr_notes . "</td>
            </tr>
            <tr class='iftype'>
            <td colspan='5'";

        if (dbFetchCell('SELECT count(*) FROM mac_accounting WHERE port_id = ?', [$port->port_id])) {
            echo "<span style='float: right;'><a href='" . Url::generate(['page' => 'device', 'device' => $port->device_id, 'tab' => 'port', 'port' => $port->port_id, 'view' => 'macaccounting']) . "'><i class='fa fa-pie-chart fa-lg icon-theme' aria-hidden='true'></i> MAC Accounting</a></span>";
        }

        echo '<br />';

        if (Rrd::checkRrdExists(get_port_rrdfile_path($port->device->hostname, $port->port_id))) {
            $graph_type = 'port_bits';

            include 'includes/html/print-interface-graphs.inc.php';
        }

        echo '</td></tr>';
    }
} else {
    echo 'None found.</td></tr>';
}

?>
</table>
