<?php

use App\Models\Ipv4Address;
use App\Models\Ipv6Address;
use App\Models\Port;
use App\Models\Storage;

$name = 'oslv_monitor';

$device_obj = DeviceCache::get($device['device_id']);

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
    if (!isset($app_data['inactive']) || !isset($app_data['inactive'][0])) {
        echo "\n | Jails: \n";
    } else {
        echo "\n<br>Current Jails: \n";
    }
    $index_int = 0;
    foreach ($app_data['oslvms'] as $index => $oslvm) {
        $label = (! isset($vars['oslvm']) || $vars['oslvm'] != $oslvm)
            ? $oslvm
            : '<span class="pagemenu-selected">' . $oslvm . '</span>';
        $index_int++;
        echo generate_link($label, $link_array, ['oslvm' => $oslvm]);
        if (isset($app_data['oslvms'][$index_int])) {
            echo ', ';
        }
    }
    if (isset($app_data['inactive']) && isset($app_data['inactive'][0])) {
        echo "\n<br>Old Jails: ";
        sort($app_data['inactive']);
        foreach ($app_data['inactive'] as $index => $oslvm) {
            $label = (! isset($vars['inactive']) || $vars['oslvm'] != $oslvm)
                ? $oslvm
                : '<span class="pagemenu-selected">' . $oslvm . '</span>';
            $index_int++;
            echo generate_link($label, $link_array, ['oslvm' => $oslvm]);
            if (isset($app_data['inactive'][$index_int])) {
                echo ', ';
            }
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
    $seen_podman_containers = [];
    $seen_docker_containers = [];
    $seen_systemd_containers = [];
    $seen_other_containers = [];
    $seen_user_containers = [];
    foreach ($app_data['inactive'] as $index => $oslvm) {
        if (preg_match('/^d_.*/', $oslvm)) {
            $seen_docker_containers[] = $oslvm;
        } elseif (preg_match('/^s_.*/', $oslvm)) {
            $seen_systemd_containers[] = $oslvm;
        } elseif (preg_match('/^u_.*/', $oslvm)) {
            $seen_user_containers[] = $oslvm;
        } elseif (preg_match('/^p_.*/', $oslvm) || preg_match('/^libpod.*/', $oslvm)) {
            $seen_podman_containers[] = $oslvm;
        } else {
            $seen_other_containers[] = $oslvm;
        }
    }
    sort($seen_podman_containers);
    sort($seen_docker_containers);
    sort($seen_systemd_containers);
    sort($seen_other_containers);
    sort($seen_user_containers);

    if (isset($podman_containers[0])) {
        if (!isset($seen_podman_containers[0])) {
            echo "\n<br>Podman Containers<b>:</b> \n";
        } else {
            echo "\n<br>Current Podman Containers<b>:</b> \n";
        }
        $index_int = 0;
        foreach ($podman_containers as $index => $oslvm) {
            $oslvm_name = $oslvm;
            $oslvm_name = preg_replace('/^p\_/', '', $oslvm_name);
            $label = (! isset($vars['oslvm']) || $vars['oslvm'] != $oslvm)
            ? $oslvm_name
            : '<span class="pagemenu-selected">' . $oslvm_name . '</span>';
            $index_int++;
            echo generate_link($label, $link_array, ['oslvm' => $oslvm]);
            if (isset($podman_containers[$index_int])) {
                echo ', ';
            }
        }
    }
    if (isset($seen_podman_containers[0])) {
        echo "\n<br>Previous Podman Containers<b>:</b> \n";
        $index_int = 0;
        foreach ($seen_podman_containers as $index => $oslvm) {
            $oslvm_name = $oslvm;
            $oslvm_name = preg_replace('/^p\_/', '', $oslvm_name);
            $label = (! isset($vars['oslvm']) || $vars['oslvm'] != $oslvm)
            ? $oslvm_name
            : '<span class="pagemenu-selected">' . $oslvm_name . '</span>';
            $index_int++;
            echo generate_link($label, $link_array, ['oslvm' => $oslvm]);
            if (isset($seen_podman_containers[$index_int])) {
                echo ', ';
            }
        }
    }

    if (isset($docker_containers[0])) {
        if (!isset($seen_docker_containers[0])) {
            echo "\n<br>Docker Containers<b>:</b> \n";
        } else {
            echo "\n<br>Current Docker Containers<b>:</b> \n";
        }
        $index_int = 0;
        foreach ($docker_containers as $index => $oslvm) {
            $oslvm_name = $oslvm;
            $oslvm_name = preg_replace('/^d\_/', '', $oslvm_name);
            $label = (! isset($vars['oslvm']) || $vars['oslvm'] != $oslvm)
                ? $oslvm_name
                : '<span class="pagemenu-selected">' . $oslvm_name . '</span>';
            $index_int++;
            echo generate_link($label, $link_array, ['oslvm' => $oslvm]);
            if (isset($docker_containers[$index_int])) {
                echo ', ';
            }
        }
    }
    if (isset($seen_docker_containers[0])) {
        echo "\n<br>Previous Docker Containers<b>:</b> \n";
        $index_int = 0;
        foreach ($seen_docker_containers as $index => $oslvm) {
            $oslvm_name = $oslvm;
            $oslvm_name = preg_replace('/^d\_/', '', $oslvm_name);
            $label = (! isset($vars['oslvm']) || $vars['oslvm'] != $oslvm)
            ? $oslvm_name
            : '<span class="pagemenu-selected">' . $oslvm_name . '</span>';
            $index_int++;
            echo generate_link($label, $link_array, ['oslvm' => $oslvm]);
            if (isset($seen_docker_containers[$index_int])) {
                echo ', ';
            }
        }
    }

    if (isset($systemd_containers[0])) {
        if (!isset($seen_systemd_containers[0])) {
            echo "\n<br>SystemD Containers<b>:</b> \n";
        } else {
            echo "\n<br>Current SystemD Containers<b>:</b> \n";
        }
        $index_int = 0;
        foreach ($systemd_containers as $index => $oslvm) {
            $oslvm_name = $oslvm;
            $oslvm_name = preg_replace('/^s\_/', '', $oslvm_name);
            $label = (! isset($vars['oslvm']) || $vars['oslvm'] != $oslvm)
                ? $oslvm_name
                : '<span class="pagemenu-selected">' . $oslvm_name . '</span>';
            $index_int++;
            echo generate_link($label, $link_array, ['oslvm' => $oslvm]);
            if (isset($systemd_containers[$index_int])) {
                echo ', ';
            }
        }
    }
    if (isset($seen_systemd_containers[0])) {
        echo "\n<br>Previous SystemD Containers<b>:</b> \n";
        $index_int = 0;
        foreach ($seen_systemd_containers as $index => $oslvm) {
            $oslvm_name = $oslvm;
            $oslvm_name = preg_replace('/^s\_/', '', $oslvm_name);
            $label = (! isset($vars['oslvm']) || $vars['oslvm'] != $oslvm)
            ? $oslvm_name
            : '<span class="pagemenu-selected">' . $oslvm_name . '</span>';
            $index_int++;
            echo generate_link($label, $link_array, ['oslvm' => $oslvm]);
            if (isset($seen_systemd_containers[$index_int])) {
                echo ', ';
            }
        }
    }

    if (isset($user_containers[0])) {
        if (!isset($seen_user_containers[0])) {
            echo "\n<br>User Containers<b>:</b> \n";
        } else {
            echo "\n<br>Current User Containers<b>:</b> \n";
        }
        $index_int = 0;
        foreach ($user_containers as $index => $oslvm) {
            $oslvm_name = $oslvm;
            $oslvm_name = preg_replace('/^u\_/', '', $oslvm_name);
            $label = (! isset($vars['oslvm']) || $vars['oslvm'] != $oslvm)
                ? $oslvm_name
                : '<span class="pagemenu-selected">' . $oslvm_name . '</span>';
            $index_int++;
            echo generate_link($label, $link_array, ['oslvm' => $oslvm]);
            if (isset($user_containers[$index_int])) {
                echo ', ';
            }
        }
    }
    if (isset($seen_user_containers[0])) {
        echo "\n<br>Previous User Containers<b>:</b> \n";
        $index_int = 0;
        foreach ($seen_user_containers as $index => $oslvm) {
            $oslvm_name = $oslvm;
            $oslvm_name = preg_replace('/^d\_/', '', $oslvm_name);
            $label = (! isset($vars['oslvm']) || $vars['oslvm'] != $oslvm)
            ? $oslvm_name
            : '<span class="pagemenu-selected">' . $oslvm_name . '</span>';
            $index_int++;
            echo generate_link($label, $link_array, ['oslvm' => $oslvm]);
            if (isset($seen_user_containers[$index_int])) {
                echo ', ';
            }
        }
    }

    if (isset($other_containers[0])) {
        if (!isset($seen_other_containers[0])) {
            echo "\n<br>Other Containers<b>:</b> \n";
        } else {
            echo "\n<br>Current Other Containers<b>:</b> \n";
        }
        $index_int = 0;
        foreach ($other_containers as $index => $oslvm) {
            $label = (! isset($vars['oslvm']) || $vars['oslvm'] != $oslvm)
                ? $oslvm
                : '<span class="pagemenu-selected">' . $oslvm . '</span>';
            $index_int++;
            echo generate_link($label, $link_array, ['oslvm' => $oslvm]);
            if (isset($other_containers[$index_int])) {
                echo ', ';
            }
        }
    }
    if (isset($seen_other_containers[0])) {
        echo "\n<br>Previous Other Containers<b>:</b> \n";
        $index_int = 0;
        foreach ($seen_other_containers as $index => $oslvm) {
            $oslvm_name = $oslvm;
            $oslvm_name = preg_replace('/^d\_/', '', $oslvm_name);
            $label = (! isset($vars['oslvm']) || $vars['oslvm'] != $oslvm)
            ? $oslvm_name
            : '<span class="pagemenu-selected">' . $oslvm_name . '</span>';
            $index_int++;
            echo generate_link($label, $link_array, ['oslvm' => $oslvm]);
            if (isset($seen_other_containers[$index_int])) {
                echo ', ';
            }
        }
    }
}

if (isset($vars['oslvm']) && isset($app_data['oslvm_data'][$vars['oslvm']])) {
    if (isset($app_data['oslvm_data'][$vars['oslvm']]['path'][0])) {
        $table_info = [
            'headers' => [
                'Path',
                'Mount Point',
                'Usage',
                '',
                ],
            'rows' => [],
        ];
        foreach ($app_data['oslvm_data'][$vars['oslvm']]['path'] as $index => $path) {
            $path = preg_replace('/\/$/', '', $path);
            $mount_path = $path;
            $mount_path_raw = false;
            $mount_path_usage = '';
            $mount_path_usage_raw = false;
            $mount_path_usage_graph = '';
            $mount_path_usage_graph_raw = false;
            $storage_info = Storage::firstWhere(
                ['storage_descr' => $mount_path],
                ['device_id' => $device['device_id']]
            );
            if (! isset($storage_info) && ! preg_match('/^\/+$/', $mount_path)) {
                $mount_path = preg_replace('/\/[^\/]+$/', '', $mount_path);
                while ($mount_path != '' && ! isset($storage_info)) {
                    $storage_info = Storage::firstWhere(
                        ['storage_descr' => $mount_path],
                        ['device_id' => $device['device_id']]
                    );
                    if (! isset($storage_info)) {
                        $mount_path = preg_replace('/\/[^\/]+$/', '', $mount_path);
                    }
                }
            }
            if (isset($storage_info)) {
                $mount_path_raw = true;
                $mount_path_usage_raw = true;
                $mount_path_usage_graph_raw = true;

                $path_graph_array = [];
                $path_graph_array['height'] = '100';
                $path_graph_array['width'] = '210';
                $path_graph_array['to'] = \LibreNMS\Config::get('time.now');
                $path_graph_array['id'] = $storage_info['storage_id'];
                $path_graph_array['type'] = 'storage_usage';
                $path_graph_array['from'] = \LibreNMS\Config::get('time.day');
                $path_graph_array['legend'] = 'no';

                $path_link_array = $path_graph_array;
                $path_link_array['page'] = 'graphs';
                unset($rpath_link_array['height'], $path_link_array['width'], $path_link_array['legend']);

                $path_link = \LibreNMS\Util\Url::generate($path_link_array);

                $path_overlib_content = generate_overlib_content($path_graph_array, $device['hostname'] . ' - ' . $storage_info['storage_descr']);

                $path_graph_array['width'] = 80;
                $path_graph_array['height'] = 20;
                $path_graph_array['bg'] = 'ffffff00';
                $path_minigraph = \LibreNMS\Util\Url::lazyGraphTag($path_graph_array);

                $mount_path = \LibreNMS\Util\Url::overlibLink($path_link, $mount_path, $path_overlib_content);
                $mount_path_usage = round($storage_info['storage_perc']) . '% ';
                $mount_path_usage_graph =  \LibreNMS\Util\Url::overlibLink($path_link, $path_minigraph, $path_overlib_content);
            }
        }
        $table_info['rows'][] = [
            [
                'data' => $path,
            ],
            [
                'data' => $mount_path,
                'raw' => $mount_path_raw,
            ],
            [
                'data' => $mount_path_usage,
                'raw' => $mount_path_usage_raw,
            ],
            [
                'data' => $mount_path_usage_graph,
                'raw' => $mount_path_usage_graph_raw,
            ],
        ];
        echo view('widgets/sortable_table', $table_info);
    }
    if (isset($app_data['oslvm_data'][$vars['oslvm']]['ip'][0])) {
        $table_info = [
            'headers' => [
                'IP',
                'Interface',
                'Device',
                ],
            'rows' => [],
        ];
        foreach ($app_data['oslvm_data'][$vars['oslvm']]['ip'] as $index => $ip) {
            $device = '';
            $device_raw = false;
            $interface = '';
            $interface_raw = false;
            if (preg_match('/^[\:A-Fa-f0-9]+$/', $ip)) {
                if (!preg_match('/^::1/', $ip)) {
                    $ip_obj = Ipv6Address::firstWhere(['ipv6_address' => $ip]);
                } else {
                    $interface = 'loopback';
                }
            } elseif (preg_match('/^[\.0-9]+$/', $ip)) {
                if (!preg_match('/^127./', $ip)) {
                    $ip_obj = Ipv4Address::firstWhere(['ipv4_address' => $ip]);
                } else {
                    $interface = 'loopback';
                }
            }
            // if we have a non-loopback IP and found a hit for it, add additional info
            if (isset($ip_obj)) {
                $interface_raw = true;
                $device_raw = true;
                $port = Port::with('device')->firstWhere(['port_id' => $ip_obj->port_id]);
                $interface = generate_port_link([
                        'label' => $port->label,
                        'port_id' => $port->port_id,
                        'ifName' => $port->ifName,
                        'device_id' => $port->device_id,
                    ]);
                $device = generate_device_link(['device_id' => $port->device_id]);
            }
            $table_info['rows'][] = [
                [
                    'data' => $ip,
                ],
                [
                    'data' => $interface,
                    'raw' => $interface_raw,
                ],
                [
                    'data' => $device,
                    'raw' => $device_raw,
                ],
            ];
            echo view('widgets/sortable_table', $table_info);
        }
        echo "<br>\n";
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
