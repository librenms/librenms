<?php

use App\Models\Port;
use App\Models\PortAdsl;
use App\Models\PortsNac;
use App\Models\PortVdsl;
use App\Plugins\Hooks\PortTabHook;
use LibreNMS\Util\Rewrite;
use LibreNMS\Util\Url;

$vars['view'] = basename($vars['view'] ?? 'graphs');

$port = \App\Models\Port::find($vars['port']);

$port_details = 1;

$hostname = $device['hostname'];
$ifname = $port->ifDescr;
$ifIndex = $port->ifIndex;
$speed = \LibreNMS\Util\Number::formatSi($port->ifSpeed, 2, 0, 'bps');

$ifalias = $port->getLabel();

if ($port->ifPhysAddress) {
    $mac = $port->ifPhysAddress;
}

$color = 'black';
if ($port->ifAdminStatus == 'down') {
    $status = "<span class='grey'>Disabled</span>";
}

if ($port->ifAdminStatus == 'up' && $port->ifOperStatus != 'up') {
    $status = "<span class='red'>Enabled / Disconnected</span>";
}

if ($port->ifAdminStatus == 'up' && $port->ifOperStatus == 'up') {
    $status = "<span class='green'>Enabled / Connected</span>";
}

$i = 1;
$inf = Rewrite::normalizeIfName($ifname);

$bg = '#ffffff';

$show_all = 1;

echo "<div style='margin: 0px; width: 100%'><table class='iftable'>";

echo view('device.tabs.ports.includes.port_row', [
    'port' => $port,
    'data' => [
        'neighbors' => [$port->port_id => (new \App\Http\Controllers\Device\Tabs\PortsController())->findPortNeighbors($port)],
        'graphs' => [
            'bits' => [['type' => 'port_bits', 'title' => trans('Traffic'), 'vars' => [['from' => '-1d'], ['from' => '-7d'], ['from' => '-30d'], ['from' => '-1y']]]],
            'upkts' => [['type' => 'port_upkts', 'title' => trans('Packets (Unicast)'), 'vars' => [['from' => '-1d'], ['from' => '-7d'], ['from' => '-30d'], ['from' => '-1y']]]],
            'errors' => [['type' => 'port_errors', 'title' => trans('Errors'), 'vars' => [['from' => '-1d'], ['from' => '-7d'], ['from' => '-30d'], ['from' => '-1y']]]],
        ],
    ],
    'collapsing' => false,
]);

echo '</table></div>';

$pos = strpos(strtolower($ifname), 'vlan');
if ($pos !== false) {
    $broke = 'yes';
}

$pos = strpos(strtolower($ifname), 'loopback');

if ($pos !== false) {
    $broke = 'yes';
}

echo "<div style='clear: both;'>";

print_optionbar_start();

$link_array = [
    'page' => 'device',
    'device' => $device['device_id'],
    'tab' => 'port',
    'port' => $port->port_id,
];

$menu_options['graphs'] = 'Graphs';
$menu_options['realtime'] = 'Real time';

if ($port->macs()->exists()) {
    $menu_options['arp'] = 'ARP Table';
}

if ($port->fdbEntries()->exists()) {
    $menu_options['fdb'] = 'FDB Table';
}
$menu_options['events'] = 'Eventlog';
$menu_options['notes'] = (get_dev_attrib($device, 'port_id_notes:' . $port->port_id) ?? '') == '' ? 'Notes' : 'Notes*';

if ($port->transceivers()->exists()) {
    $menu_options['transceiver'] = __('port.transceiver');
}

if (dbFetchCell("SELECT COUNT(*) FROM `sensors` WHERE `device_id` = ? AND `entPhysicalIndex` = ?  AND entPhysicalIndex_measured = 'ports'", [$device['device_id'], $port->ifIndex])) {
    $menu_options['sensors'] = 'Health';
}

if (PortAdsl::where('port_id', $port->port_id)->exists()) {
    $menu_options['xdsl'] = 'xDSL';
} elseif (PortVdsl::where('port_id', $port->port_id)->exists()) {
    $menu_options['xdsl'] = 'xDSL';
}

if (PortsNac::where('port_id', $port->port_id)->exists()) {
    $menu_options['nac'] = 'NAC';
}

if (DeviceCache::getPrimary()->ports()->where('pagpGroupIfIndex', $port->ifIndex)->exists()) {
    $menu_options['pagp'] = 'PAgP';
}

if (dbFetchCell("SELECT COUNT(*) FROM `ports_vlans` WHERE `port_id` = '" . $port->port_id . "' and `device_id` = '" . $device['device_id'] . "'")) {
    $menu_options['vlans'] = 'VLANs';
}

// Are there any CBQoS components for this device?
$component = new LibreNMS\Component();
$options = [];         // Re-init array in case it has been declared previously.
$options['filter']['type'] = ['=', 'Cisco-CBQOS'];
$components = $component->getComponents($device['device_id'], $options);
$components = $components[$device['device_id']] ?? [];        // We only care about our device id.
if (count($components) > 0) {
    $menu_options['cbqos'] = 'CBQoS';
}

$portModel = Port::find($port->port_id);

if (LibreNMS\Plugins::countHooks('port_container') || \PluginManager::hasHooks(PortTabHook::class, ['port' => $portModel])) {
    // Checking if any plugin implements the port_container. If yes, allow to display the menu_option
    $menu_options['plugins'] = 'Plugins';
}

$sep = '';
foreach ($menu_options as $option => $text) {
    echo $sep;
    if ($vars['view'] == $option) {
        echo "<span class='pagemenu-selected'>";
    }

    echo generate_link($text, $link_array, ['view' => $option]);
    if ($vars['view'] == $option) {
        echo '</span>';
    }

    $sep = ' | ';
}

unset($sep);

if (dbFetchCell("SELECT count(*) FROM mac_accounting WHERE port_id = '" . $port->port_id . "'") > '0') {
    echo generate_link($descr, $link_array, ['view' => 'macaccounting', 'graph' => $type]);

    echo ' | Mac Accounting : ';
    if ($vars['view'] == 'macaccounting' && $vars['graph'] == 'bits' && $vars['subview'] == 'graphs') {
        echo "<span class='pagemenu-selected'>";
    }

    echo generate_link('Bits', $link_array, ['view' => 'macaccounting', 'subview' => 'graphs', 'graph' => 'bits']);
    if ($vars['view'] == 'macaccounting' && $vars['graph'] == 'bits' && $vars['subview'] == 'graphs') {
        echo '</span>';
    }

    echo '(';
    if ($vars['view'] == 'macaccounting' && $vars['graph'] == 'bits' && $vars['subview'] == 'minigraphs') {
        echo "<span class='pagemenu-selected'>";
    }

    echo generate_link('Mini', $link_array, ['view' => 'macaccounting', 'subview' => 'minigraphs', 'graph' => 'bits']);
    if ($vars['view'] == 'macaccounting' && $vars['graph'] == 'bits' && $vars['subview'] == 'minigraphs') {
        echo '</span>';
    }

    echo '|';

    if ($vars['view'] == 'macaccounting' && $vars['graph'] == 'bits' && $vars['subview'] == 'top10') {
        echo "<span class='pagemenu-selected'>";
    }

    echo generate_link('Top10', $link_array, ['view' => 'macaccounting', 'subview' => 'top10', 'graph' => 'bits']);
    if ($vars['view'] == 'macaccounting' && $vars['graph'] == 'bits' && $vars['subview'] == 'top10') {
        echo '</span>';
    }

    echo ') | ';

    if ($vars['view'] == 'macaccounting' && $vars['graph'] == 'pkts' && $vars['subview'] == 'graphs') {
        echo "<span class='pagemenu-selected'>";
    }

    echo generate_link('Packets', $link_array, ['view' => 'macaccounting', 'subview' => 'graphs', 'graph' => 'pkts']);
    if ($vars['view'] == 'macaccounting' && $vars['graph'] == 'pkts' && $vars['subview'] == 'graphs') {
        echo '</span>';
    }

    echo '(';
    if ($vars['view'] == 'macaccounting' && $vars['graph'] == 'pkts' && $vars['subview'] == 'minigraphs') {
        echo "<span class='pagemenu-selected'>";
    }

    echo generate_link('Mini', $link_array, ['view' => 'macaccounting', 'subview' => 'minigraphs', 'graph' => 'pkts']);
    if ($vars['view'] == 'macaccounting' && $vars['graph'] == 'pkts' && $vars['subview'] == 'minigraphs') {
        echo '</span>';
    }

    echo '|';
    if ($vars['view'] == 'macaccounting' && $vars['graph'] == 'pkts' && $vars['subview'] == 'top10') {
        echo "<span class='pagemenu-selected'>";
    }

    echo generate_link('Top10', $link_array, ['view' => 'macaccounting', 'subview' => 'top10', 'graph' => 'pkts']);
    if ($vars['view'] == 'macaccounting' && $vars['graph'] == 'pkts' && $vars['subview'] == 'top10') {
        echo '</span>';
    }

    echo ')';
}//end if

if (dbFetchCell("SELECT COUNT(*) FROM juniAtmVp WHERE port_id = '" . $port->port_id . "'") > '0') {
    // FIXME ATM VPs
    // FIXME URLs BROKEN
    echo ' | ATM VPs : ';
    if ($vars['view'] == 'junose-atm-vp' && $vars['graph'] == 'bits') {
        echo "<span class='pagemenu-selected'>";
    }

    echo "<a href='" . Url::generate(['page' => 'device', 'device' => $device['device_id'], 'tab' => 'port', 'port' => $port->port_id]) . "/junose-atm-vp/bits/'>Bits</a>";
    if ($vars['view'] == 'junose-atm-vp' && $vars['graph'] == 'bits') {
        echo '</span>';
    }

    echo ' | ';
    if ($vars['view'] == 'junose-atm-vp' && $vars['graph'] == 'packets') {
        echo "<span class='pagemenu-selected'>";
    }

    echo "<a href='" . Url::generate(['page' => 'device', 'device' => $device['device_id'], 'tab' => 'port', 'port' => $port->port_id]) . "/junose-atm-vp/packets/'>Packets</a>";
    if ($vars['view'] == 'junose-atm-vp' && $vars['graph'] == 'bits') {
        echo '</span>';
    }

    echo ' | ';
    if ($vars['view'] == 'junose-atm-vp' && $vars['graph'] == 'cells') {
        echo "<span class='pagemenu-selected'>";
    }

    echo "<a href='" . Url::generate(['page' => 'device', 'device' => $device['device_id'], 'tab' => 'port', 'port' => $port->port_id]) . "/junose-atm-vp/cells/'>Cells</a>";
    if ($vars['view'] == 'junose-atm-vp' && $vars['graph'] == 'bits') {
        echo '</span>';
    }

    echo ' | ';
    if ($vars['view'] == 'junose-atm-vp' && $vars['graph'] == 'errors') {
        echo "<span class='pagemenu-selected'>";
    }

    echo "<a href='" . Url::generate(['page' => 'device', 'device' => $device['device_id'], 'tab' => 'port', 'port' => $port->port_id]) . "/junose-atm-vp/errors/'>Errors</a>";
    if ($vars['view'] == 'junose-atm-vp' && $vars['graph'] == 'bits') {
        echo '</span>';
    }
}//end if

if (Auth::user()->hasGlobalAdmin() && \LibreNMS\Config::get('enable_billing') == 1) {
    $bills = dbFetchRows('SELECT `bill_id` FROM `bill_ports` WHERE `port_id`=?', [$port->port_id]);
    if (count($bills) === 1) {
        echo "<span style='float: right;'><a href='" . Url::generate(['page' => 'bill', 'bill_id' => $bills[0]['bill_id']]) . "'><i class='fa fa-money fa-lg icon-theme' aria-hidden='true'></i> View Bill</a></span>";
    } elseif (count($bills) > 1) {
        echo "<span style='float: right;'><a href='" . Url::generate(['page' => 'bills']) . "'><i class='fa fa-money fa-lg icon-theme' aria-hidden='true'></i> View Bills</a></span>";
    } else {
        echo "<span style='float: right;'><a href='" . Url::generate(['page' => 'bills', 'view' => 'add', 'port' => $port->port_id]) . "'><i class='fa fa-money fa-lg icon-theme' aria-hidden='true'></i> Create Bill</a></span>";
    }
}

print_optionbar_end();

echo "<div style='margin: 5px;'>";

require 'includes/html/pages/device/port/' . $vars['view'] . '.inc.php';

echo '</div>';
