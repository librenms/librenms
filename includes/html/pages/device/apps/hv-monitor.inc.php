<?php

use App\Models\Port;

$link_array = [
    'page' => 'device',
    'device' => $device['device_id'],
    'tab' => 'apps',
    'app' => 'hv-monitor',
];

print_optionbar_start();

if (! isset($vars['vm'])) {
    echo generate_link('<span class="pagemenu-selected"><b>Totals</b></span>', $link_array);
} else {
    echo generate_link('<b>Totals</b>', $link_array);
}
echo '<b> | VMs: </b>';
$vm_links = [];
foreach ($app->data['VMs'] as $vm) {
    $label = $vm;

    if ($vars['vm'] == $vm) {
        $label = '<span class="pagemenu-selected">' . $vm . '</span>';
    }

    $vm_links[] = generate_link($label, $link_array, ['vm' => $vm]);
}
echo implode(', ', $vm_links);

if (! isset($vars['vmif']) && ! isset($vars['vmdisk'])) {
    if (! isset($vars['vmpage'])) {
        $vars['vmpage'] = 'general';
    }
}

echo '<br><b>Pages: </b>';
if ($vars['vmpage'] == 'general') {
    $page_links[] = '<span class="pagemenu-selected">General</span>';
} else {
    $page_links[] = generate_link('General', $link_array, ['vm' => $vars['vm'], 'vmpage' => 'general']);
}
if ($vars['vmpage'] == 'disk') {
    $page_links[] = '<span class="pagemenu-selected">Disk</span>';
} else {
    $page_links[] = generate_link('Disk', $link_array, ['vm' => $vars['vm'], 'vmpage' => 'disk']);
}
if ($vars['vmpage'] == 'network') {
    $page_links[] = '<span class="pagemenu-selected">Network</span>';
} else {
    $page_links[] = generate_link('Network', $link_array, ['vm' => $vars['vm'], 'vmpage' => 'network']);
}
if ($vars['vmpage'] == 'Snapshots') {
    $page_links[] = '<span class="pagemenu-selected">Network</span>';
} else {
    $page_links[] = generate_link('Snapshots', $link_array, ['vm' => $vars['vm'], 'vmpage' => 'snapshots']);
}
echo implode(', ', $page_links);

if (isset($vars['vm'])) {
    echo '<br><b>Disks:</b> ';
    $disk_links = [];
    foreach ($app->data['VMdisks'][$vars['vm']] as $index => $disk) {
        $label = $disk;

        if ($vars['vmdisk'] == $disk) {
            $label = '<span class="pagemenu-selected">' . $disk . '</span>';
        }
        if ($vars['vmdisk'] == $disk) {
            $disk_links[] = $label;
        } else {
            $disk_links[] = generate_link($label, $link_array, ['vm' => $vars['vm'], 'vmdisk' => $disk]);
        }
    }
    echo implode(', ', $disk_links);

    echo '<br><b>Interfaces:</b> ';
    $if_links = [];
    foreach ($app->data['VMifs'][$vars['vm']] as $vmif => $if_info) {
        $label = $vmif;

        if ($vars['vmif'] == $vmif) {
            $if_links[] = '<span class="pagemenu-selected">' . $vmif . '</span>';
        } else {
            $if_links[] = generate_link($label, $link_array, ['vm' => $vars['vm'], 'vmif' => $vmif]);
        }
    }
    echo implode(', ', $if_links);
}

if (isset($vars['vmif']) and isset($vars['vm'])) {
    $mac = $app->data['VMifs'][$vars['vm']][$vars['vmif']]['mac'];
    $port = Port::with('device')->firstWhere(['ifPhysAddress' => str_replace(':', '', $mac)]);

    echo "\n<br>\n" .
        '<b>MAC:</b> ' . $mac;
    if (isset($port) && isset($mac) && $mac != '') {
        echo ' (' .
               generate_device_link(['device_id' => $port->device_id]) .
               ', ' .
               generate_port_link([
                   'label' => $port->label,
                   'port_id' => $port->port_id,
                   'ifName' => $port->ifName,
                   'device_id' => $port->device_id,
               ]) .
            ')';
    }
    echo "<br>\n";

    // This is likely to be unknown if the device is not up and running
    //
    // Likely not relevant for libvirt as it can't do interface re-use. And vnet interfaces are likely set to be ignored as
    // it bringing them up and down spam the LibreNMS logs. Also massiving spams the RRD dir as well... even more so if
    // it is a CAPE box.
    // $config['bad_if'][] = 'vnet';  <--- a must for Libvirt boxes. :(
    //
    // Mainly for CBSD
    $port = Port::with('device')->firstWhere(['device_id' => $app->device_id, 'ifName' => $app->data['VMifs'][$vars['vm']][$vars['vmif']]['if']]);
    if (! isset($port)) {
        echo '<b>HV if:</b> ' . $app->data['VMifs'][$vars['vm']][$vars['vmif']]['if'] . "\n";
    } else {
        echo '<b>HV if:</b> ' .
            generate_port_link([
                'label' => $port->label,
                'port_id' => $port->port_id,
                'ifName' => $port->ifName,
                'device_id' => $port->device_id,
            ]);
    }

    // Not likely to be known on Libvirt systems thanks to Libvirt sucking at reporting some info... and IF stuff in general
    if ($app->data['VMifs'][$vars['vm']][$vars['vmif']]['parent'] != '') {
        $port = Port::with('device')->firstWhere(['device_id' => $app->device_id, 'ifName' => $app->data['VMifs'][$vars['vm']][$vars['vmif']]['parent']]);
        if (! isset($port)) {
            echo '<br><b>HV parent if:</b> ' . $app->data['VMifs'][$vars['vm']][$vars['vmif']]['parent'];
        } else {
            echo '<br><b>HV parent if:</b> ' .
                generate_port_link([
                    'label' => $port->label,
                    'port_id' => $port->port_id,
                    'ifName' => $port->ifName,
                    'device_id' => $port->device_id,
                ]);
        }
    }
}

print_optionbar_end();

$link_array = [
    'page' => 'device',
    'device' => $device['device_id'],
    'tab' => 'apps',
    'app' => 'hv-monitor',
];

$graphs = [];
if (! isset($vars['vm']) && isset($vars['vmpage']) && $vars['vmpage'] == 'general') {
    $graphs['hv-monitor_status'] = 'VM Statuses Count';
}

if (! isset($vars['vmdisk']) and ! isset($vars['vmif'])) {
    if (isset($vars['vmpage']) && $vars['vmpage'] == 'general') {
        if (isset($vars['vm']) && $app->data['hv'] == 'libvirt') {
            $graphs['hv-monitor_status-int'] = 'VM Status: 0=no state, 1=running, 2=blocked, 3=paused, 4=being shut down, 5=shut off, 6=crashed, 7=PM suspended';
        }
        if (isset($vars['vm']) && $app->data['hv'] == 'CBSD') {
            $graphs['hv-monitor_status-int'] = 'VM Status: 1=Running, 8=Shut Off, 9=Maintenace';
        }
        $graphs['hv-monitor_memory'] = 'Memory Usage';
        $graphs['hv-monitor_pmem'] = 'Memory Percent';
        $graphs['hv-monitor_time'] = 'CPU Time';
        $graphs['hv-monitor_pcpu'] = 'CPU Percent';
        $graphs['hv-monitor_flt'] = 'Faults';
        $graphs['hv-monitor_csw'] = 'Context Switches';
        $graphs['hv-monitor_etimes'] = 'Etimes';
    }

    if (isset($vars['vmpage']) && $vars['vmpage'] == 'disk') {
        $graphs['hv-monitor_disk-size2'] = 'Disk Size';
        // Linux does not support fetching block IO for procs
        if ($app->data['hv'] != 'libvirt') {
            $graphs['hv-monitor_disk-rw-blocks'] = 'Disk RW, Blocks';
        }
        $graphs['hv-monitor_disk-rw-bytes'] = 'Disk RW, Bytes';
        $graphs['hv-monitor_disk-rw-reqs'] = 'Disk RW, Requests';
        $graphs['hv-monitor_cow'] = 'COWs';
        if ($app->data['hv'] == 'CBSD') {
            $graphs['hv-monitor_disk-rw-time'] = 'Disk RW, Time';
        }
        // does not appear to be a tracked stat on FreeBSD
        if ($app->data['hv'] == 'libvirt') {
            $graphs['hv-monitor_disk-ftime'] = 'Disk Flush, Time';
            $graphs['hv-monitor_disk-freqs'] = 'Disk Flush, Requests';
        }
    }

    if (isset($vars['vmpage']) && $vars['vmpage'] == 'snapshots') {
        $graphs['hv-monitor_snaps'] = 'Snapshots';
        // curious not supported by libvirt
        // CBSD and other future bhyve based ones this is easy to get if using ZFS
        if ($app->data['hv'] == 'CBSD') {
            $graphs['hv-monitor_snaps_size'] = 'Snapshots Size';
        }
    }

    if (isset($vars['vmpage']) && $vars['vmpage'] == 'network') {
        $graphs['hv-monitor_net-pkts'] = 'Network Packets';
        $graphs['hv-monitor_net-bytes'] = 'Network Bytes';
        $graphs['hv-monitor_net-errs'] = 'Network Errors';
        $graphs['hv-monitor_net-drops'] = 'Network Drops';
        $graphs['hv-monitor_net-coll'] = 'Network Collisions';
    }
} elseif (isset($vars['vmdisk'])) {
    $graphs['hv-monitor_disk-size'] = 'Size';
    $graphs['hv-monitor_disk-rw-bytes'] = 'Disk RW, Bytes';
    $graphs['hv-monitor_disk-rw-reqs'] = 'Disk RW, Requests';
    if ($app->data['hv'] != 'CBSD') {
        $graphs['hv-monitor_disk-rw-time'] = 'Disk RW, Time';
    }
    if ($app->data['hv'] == 'libvirt') {
        $graphs['hv-monitor_disk-ftime'] = 'Disk Flush, Time';
        $graphs['hv-monitor_disk-freqs'] = 'Disk Flush, Requests';
    }
} elseif (isset($vars['vmif'])) {
    $graphs['hv-monitor_net-pkts'] = 'Packets';
    $graphs['hv-monitor_net-bytes'] = 'Bytes';
    $graphs['hv-monitor_net-errs'] = 'Errors';
    $graphs['hv-monitor_net-drops'] = 'Drops';
    $graphs['hv-monitor_net-coll'] = 'Collisions';
}

foreach ($graphs as $key => $text) {
    $graph_type = $key;
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = \LibreNMS\Config::get('time.now');
    $graph_array['id'] = $app['app_id'];
    $graph_array['type'] = 'application_' . $key;

    if (isset($vars['vm'])) {
        $graph_array['vm'] = $vars['vm'];
    }

    if (isset($vars['vmdisk'])) {
        $graph_array['vmdisk'] = $vars['vmdisk'];
    }

    if (isset($vars['vmif'])) {
        $graph_array['vmif'] = $vars['vmif'];
    }

    $graph_array['hv'] = $app->data['hv'];

    echo '<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">' . $text . '</h3>
    </div>
    <div class="panel-body">
    <div class="row">';
    include 'includes/html/print-graphrow.inc.php';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}
