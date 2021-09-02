<?php

$vars['view'] = basename($vars['view'] ?? 'graphs');

$port = dbFetchRow('SELECT * FROM `ports` WHERE `port_id` = ?', [$vars['port']]);

$port_details = 1;

$hostname = $device['hostname'];
$hostid = $device['port_id'];
$ifname = $port['ifDescr'];
$ifIndex = $port['ifIndex'];
$speed = \LibreNMS\Util\Number::formatSi($port['ifSpeed'], 2, 3, 'bps');

$ifalias = $port['name'];

if ($port['ifPhysAddress']) {
    $mac = "$port[ifPhysAddress]";
}

$color = 'black';
if ($port['ifAdminStatus'] == 'down') {
    $status = "<span class='grey'>Disabled</span>";
}

if ($port['ifAdminStatus'] == 'up' && $port['ifOperStatus'] != 'up') {
    $status = "<span class='red'>Enabled / Disconnected</span>";
}

if ($port['ifAdminStatus'] == 'up' && $port['ifOperStatus'] == 'up') {
    $status = "<span class='green'>Enabled / Connected</span>";
}

$i = 1;
$inf = \LibreNMS\Util\Rewrite::normalizeIfName($ifname);

$bg = '#ffffff';

$show_all = 1;

echo "<div class=ifcell style='margin: 0px;'><table width=100% cellpadding=10 cellspacing=0>";

require 'includes/html/print-interface.inc.php';

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
    'page'   => 'device',
    'device' => $device['device_id'],
    'tab'    => 'port',
    'port'   => $port['port_id'],
];

$menu_options['graphs'] = 'Graphs';
$menu_options['realtime'] = 'Real time';
// FIXME CONDITIONAL
$menu_options['arp'] = 'ARP Table';
$menu_options['fdb'] = 'FDB Table';
$menu_options['events'] = 'Eventlog';
$menu_options['notes'] = 'Notes';

if (dbFetchCell("SELECT COUNT(*) FROM `sensors` WHERE `device_id` = ? AND `entPhysicalIndex` = ?  AND entPhysicalIndex_measured = 'ports'", [$device['device_id'], $port['ifIndex']])) {
    $menu_options['sensors'] = 'Health';
}

if (dbFetchCell("SELECT COUNT(*) FROM `ports_adsl` WHERE `port_id` = '" . $port['port_id'] . "'")) {
    $menu_options['adsl'] = 'ADSL';
}

if (dbFetchCell("SELECT COUNT(*) FROM `ports` WHERE `pagpGroupIfIndex` = '" . $port['ifIndex'] . "' and `device_id` = '" . $device['device_id'] . "'")) {
    $menu_options['pagp'] = 'PAgP';
}

if (dbFetchCell("SELECT COUNT(*) FROM `ports_vlans` WHERE `port_id` = '" . $port['port_id'] . "' and `device_id` = '" . $device['device_id'] . "'")) {
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

if (LibreNMS\Plugins::countHooks('port_container')) {
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

if (dbFetchCell("SELECT count(*) FROM mac_accounting WHERE port_id = '" . $port['port_id'] . "'") > '0') {
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

if (dbFetchCell("SELECT COUNT(*) FROM juniAtmVp WHERE port_id = '" . $port['port_id'] . "'") > '0') {
    // FIXME ATM VPs
    // FIXME URLs BROKEN
    echo ' | ATM VPs : ';
    if ($vars['view'] == 'junose-atm-vp' && $vars['graph'] == 'bits') {
        echo "<span class='pagemenu-selected'>";
    }

    echo "<a href='" . \LibreNMS\Util\Url::generate(['page' => 'device', 'device' => $device['device_id'], 'tab' => 'port', 'port' => $port['port_id']]) . "/junose-atm-vp/bits/'>Bits</a>";
    if ($vars['view'] == 'junose-atm-vp' && $vars['graph'] == 'bits') {
        echo '</span>';
    }

    echo ' | ';
    if ($vars['view'] == 'junose-atm-vp' && $vars['graph'] == 'packets') {
        echo "<span class='pagemenu-selected'>";
    }

    echo "<a href='" . \LibreNMS\Util\Url::generate(['page' => 'device', 'device' => $device['device_id'], 'tab' => 'port', 'port' => $port['port_id']]) . "/junose-atm-vp/packets/'>Packets</a>";
    if ($vars['view'] == 'junose-atm-vp' && $vars['graph'] == 'bits') {
        echo '</span>';
    }

    echo ' | ';
    if ($vars['view'] == 'junose-atm-vp' && $vars['graph'] == 'cells') {
        echo "<span class='pagemenu-selected'>";
    }

    echo "<a href='" . \LibreNMS\Util\Url::generate(['page' => 'device', 'device' => $device['device_id'], 'tab' => 'port', 'port' => $port['port_id']]) . "/junose-atm-vp/cells/'>Cells</a>";
    if ($vars['view'] == 'junose-atm-vp' && $vars['graph'] == 'bits') {
        echo '</span>';
    }

    echo ' | ';
    if ($vars['view'] == 'junose-atm-vp' && $vars['graph'] == 'errors') {
        echo "<span class='pagemenu-selected'>";
    }

    echo "<a href='" . \LibreNMS\Util\Url::generate(['page' => 'device', 'device' => $device['device_id'], 'tab' => 'port', 'port' => $port['port_id']]) . "/junose-atm-vp/errors/'>Errors</a>";
    if ($vars['view'] == 'junose-atm-vp' && $vars['graph'] == 'bits') {
        echo '</span>';
    }
}//end if

if (Auth::user()->hasGlobalAdmin() && \LibreNMS\Config::get('enable_billing') == 1) {
    $bills = dbFetchRows('SELECT `bill_id` FROM `bill_ports` WHERE `port_id`=?', [$port['port_id']]);
    if (count($bills) === 1) {
        echo "<span style='float: right;'><a href='" . \LibreNMS\Util\Url::generate(['page' => 'bill', 'bill_id' => $bills[0]['bill_id']]) . "'><i class='fa fa-money fa-lg icon-theme' aria-hidden='true'></i> View Bill</a></span>";
    } elseif (count($bills) > 1) {
        echo "<span style='float: right;'><a href='" . \LibreNMS\Util\Url::generate(['page' => 'bills']) . "'><i class='fa fa-money fa-lg icon-theme' aria-hidden='true'></i> View Bills</a></span>";
    } else {
        echo "<span style='float: right;'><a href='" . \LibreNMS\Util\Url::generate(['page' => 'bills', 'view' => 'add', 'port' => $port['port_id']]) . "'><i class='fa fa-money fa-lg icon-theme' aria-hidden='true'></i> Create Bill</a></span>";
    }
}

print_optionbar_end();

echo "<div style='margin: 5px;'>";

require 'includes/html/pages/device/port/' . $vars['view'] . '.inc.php';

echo '</div>';
