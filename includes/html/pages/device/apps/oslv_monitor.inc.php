<?php

$name = 'oslv_monitor';

$link_array = [
    'page' => 'device',
    'device' => $device['device_id'],
    'tab' => 'apps',
    'app' => 'oslv_monitor',
];

$app_data = $app->data;

print_optionbar_start();

$label = isset($vars['oslvm'])
    ? 'Totals'
    : '<span class="pagemenu-selected">Totals</span>';
echo generate_link($label, $link_array);

if (isset($app_data['backend']) && $app_data['backend'] == 'FreeBSD') {
    echo " | Jails: ";
    $index_int = 0;
    foreach ($app_data['oslvms'] as $index => $oslvm) {
        $label = (!isset($vars['oslvm']) || $vars['oslvm'] != $oslvm)
            ? $oslvm
            : '<span class="pagemenu-selected">' . $oslvm . '</span>';
        $index_int++;
        echo generate_link($label, $link_array, ['oslvm' => $oslvm]);
        if (isset($app_data['oslvms'][$index_int])) {
            echo ', ';
        }
    }

    if (isset($vars['oslvm']) && isset($app_data['oslvm_data'][$vars['oslvm']])) {
        if (isset($app_data['oslvm_data'][$vars['oslvm']]['path'])) {
            echo "\n<br>Path: ".$app_data['oslvm_data'][$vars['oslvm']]['path']."<br>\n";
        }
        if (isset($app_data['oslvm_data'][$vars['oslvm']]['ipv4'])) {
            echo "\n<br>IPv4: ".$app_data['oslvm_data'][$vars['oslvm']]['ipv4']."<br>\n";
        }
        if (isset($app_data['oslvm_data'][$vars['oslvm']]['ipv6'])) {
            echo "\n<br>IPv6: ".$app_data['oslvm_data'][$vars['oslvm']]['ipv6']."<br>\n";
        }
    }
}

if (isset($app_data['backend']) && $app_data['backend'] == 'cgroups') {
    $podman_containers = [];
    $docker_containers = [];
    $systemd_containers = [];
    $other_containers = [];
    $user_containers = [];
    foreach ($app_data['oslvms'] as $index => $oslvm) {
        if (preg_match('/^d_.*/', $oslvm)) {
            $docker_containers[] = $oslvm;
        } elseif (preg_match('/^s_.*/', $oslvm)) {
            $systemd_containers[] = $oslvm;
        } elseif (preg_match('/^u_.*/', $oslvm)) {
            $user_containers[] = $oslvm;
        } elseif (preg_match('/^p_.*/', $oslvm) || preg_match('/^libpod.*/', $oslvm)) {
            $podman_containers[] = $oslvm;
        } else {
            $other_containers[] = $oslvm;
        }
    }

    if (isset($podman_containers[0])) {
        echo "\n<br>Podman Containers<b>:</b> ";
        $index_int = 0;
        foreach ($podman_containers as $index => $oslvm) {
            $oslvm_name = $oslvm;
            $oslvm_name = preg_replace('/^p\_/', '', $oslvm_name);
            $label = (!isset($vars['oslvm']) || $vars['oslvm'] != $oslvm)
            ? $oslvm_name
            : '<span class="pagemenu-selected">' . $oslvm_name . '</span>';
            $index_int++;
            echo generate_link($label, $link_array, ['oslvm' => $oslvm]);
            if (isset($podman_containers[$index_int])) {
                echo ', ';
            }
        }
    }
    if (isset($docker_containers[0])) {
        echo "\n<br>Docker Containers<b>:</b> ";
        $index_int = 0;
        foreach ($docker_containers as $index => $oslvm) {
            $oslvm_name = $oslvm;
            $oslvm_name = preg_replace('/^d\_/', '', $oslvm_name);
            $label = (!isset($vars['oslvm']) || $vars['oslvm'] != $oslvm)
                ? $oslvm_name
                : '<span class="pagemenu-selected">' . $oslvm_name . '</span>';
            $index_int++;
            echo generate_link($label, $link_array, ['oslvm' => $oslvm]);
            if (isset($docker_containers[$index_int])) {
                echo ', ';
            }
        }
    }

    if (isset($systemd_containers[0])) {
        echo "\n<br>Systemd Containers<b>:</b> ";
        $index_int = 0;
        foreach ($systemd_containers as $index => $oslvm) {
            $oslvm_name = $oslvm;
            $oslvm_name = preg_replace('/^s\_/', '', $oslvm_name);
            $label = (!isset($vars['oslvm']) || $vars['oslvm'] != $oslvm)
                ? $oslvm_name
                : '<span class="pagemenu-selected">' . $oslvm_name . '</span>';
            $index_int++;
            echo generate_link($label, $link_array, ['oslvm' => $oslvm]);
            if (isset($systemd_containers[$index_int])) {
                echo ', ';
            }
        }
    }

    if (isset($user_containers[0])) {
        echo "\n<br>User Containers<b>:</b> ";
        $index_int = 0;
        foreach ($user_containers as $index => $oslvm) {
            $oslvm_name = $oslvm;
            $oslvm_name = preg_replace('/^u\_/', '', $oslvm_name);
            $label = (!isset($vars['oslvm']) || $vars['oslvm'] != $oslvm)
                ? $oslvm_name
                : '<span class="pagemenu-selected">' . $oslvm_name . '</span>';
            $index_int++;
            echo generate_link($label, $link_array, ['oslvm' => $oslvm]);
            if (isset($user_containers[$index_int])) {
                echo ', ';
            }
        }
    }

    if (isset($other_containers[0])) {
        echo "\n<br>Other Containers<b>:</b> ";
        $index_int = 0;
        foreach ($other_containers as $index => $oslvm) {
            $label = (!isset($vars['oslvm']) || $vars['oslvm'] != $oslvm)
                ? $oslvm
                : '<span class="pagemenu-selected">' . $oslvm . '</span>';
            $index_int++;
            echo generate_link($label, $link_array, ['oslvm' => $oslvm]);
            if (isset($other_containers[$index_int])) {
                echo ', ';
            }
        }
    }
}

print_optionbar_end();

if (isset($app_data['backend']) && $app_data['backend'] == 'FreeBSD') {
    $graphs = [
        [
            'type' => 'cpu_percent',
            'description' => 'CPU Usage Percent',
        ],
        [
            'type' => 'mem_percent',
            'description' => 'Memory Usage Percent',
        ],
        [
            'type' => 'time',
            'description' => 'CPU/System Time in secs/sec',
        ],
        [
            'type' => 'procs',
            'description' => 'Processes',
        ],
        [
            'type' => 'blocks',
            'description' => 'Blocks, Read/Write',
        ],
        [
            'type' => 'cows',
            'description' => 'Copy-on-Write Faults',
        ],
        [
            'type' => 'sizes',
            'description' => 'Data, Stack, Text in Kbytes',
        ],
        [
            'type' => 'rss',
            'description' => 'Real Memory(Resident Set Size) in Kbytes',
        ],
        [
            'type' => 'vsz',
            'description' => 'Virtual Size in Kbytes',
        ],
        [
            'type' => 'messages',
            'description' => 'Messages, Sent/Received',
        ],
        [
            'type' => 'faults',
            'description' => 'Faults, Major/Minor',
        ],
        [
            'type' => 'switches',
            'description' => 'Context Switches, (In)Voluntary',
        ],
        [
            'type' => 'swaps',
            'description' => 'Swaps',
        ],
        [
            'type' => 'signals_taken',
            'description' => 'Signals Taken',
        ],
        [
            'type' => 'etime',
            'description' => 'Elapsed Time in seconds',
        ],
    ];
} elseif (isset($app_data['backend']) && $app_data['backend'] == 'cgroups') {
    $graphs = [
        [
            'type' => 'cpu_percent',
            'description' => 'CPU Usage Percent',
        ],
        [
            'type' => 'mem_percent',
            'description' => 'Memory Usage Percent',
        ],
        [
            'type' => 'time',
            'description' => 'CPU/System Time in secs/sec',
        ],
        [
            'type' => 'procs',
            'description' => 'Processes',
        ],
        [
            'type' => 'sock',
            'description' => 'Sock, network transmission buffers size',
        ],
        [
            'type' => 'ops_rwd',
            'description' => 'Ops, Read/Write/Discard',
        ],
        [
            'type' => 'bytes_rwd',
            'description' => 'Bytes, Read/Write/Discard',
        ],
        [
            'type' => 'sizes',
            'description' => 'Size, Data, Text in kbytes',
        ],
        [
            'type' => 'rss',
            'description' => 'Real Memory(Resident Set Size) in kbytes',
        ],
        [
            'type' => 'vsz',
            'description' => 'Virtual Size in kbytes',
        ],
        [
            'type' => 'faults',
            'description' => 'Faults, Major/Minor',
        ],
        [
            'type' => 'zswap',
            'description' => 'ZSwap Size',
        ],
        [
            'type' => 'zswap_activity',
            'description' => 'ZSwap, Activity',
        ],
        [
            'type' => 'pg',
            'description' => 'Page Stats, non faults',
        ],
        [
            'type' => 'mem_misc',
            'description' => 'Misc. Memory Stats',
        ],
        [
            'type' => 'thp_activity',
            'description' => 'Transparent Huge Page Activity',
        ],
    ];
}

foreach ($graphs as $key => $graph_info) {
    $graph_type = $graph_info['type'];
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = time();
    $graph_array['id'] = $app['app_id'];
    $graph_array['type'] = 'application_' . $name . '_' . $app_data['backend'] . '_' . $graph_info['type'];
    if (isset($vars['oslvm'])) {
        $graph_array['oslvm'] = $vars['oslvm'];
    }

    echo '<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">' . $graph_info['description'] . '</h3>
    </div>
    <div class="panel-body">
    <div class="row">';
    include 'includes/html/print-graphrow.inc.php';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}
