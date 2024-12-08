<?php

use App\Models\Ipv4Address;
use App\Models\Ipv6Address;
use App\Models\Port;
use App\Models\Storage;

include 'includes/nfs-shared.inc.php';

if (! isset($vars['flat_mount_options'])) {
    $vars['flat_mount_options'] = 1;
} elseif (isset($vars['flat_mount_options']) &&
          $vars['flat_mount_options'] != '0' &&
          $vars['flat_mount_options'] != '1') {
    $vars['flat_mount_options'] = 1;
}
if (! isset($vars['show_mount_options'])) {
    $vars['show_mount_options'] = 1;
} elseif (isset($vars['show_mount_options']) &&
          $vars['show_mount_options'] != '0' &&
          $vars['show_mount_options'] != '1') {
    $vars['show_mount_options'] = 1;
}

// make sure $vars['app_page'] is something that is understood
if (isset($vars['app_page']) && $vars['app_page'] != 'general'
    && $vars['app_page'] != 'mounts' && $vars['app_page'] != 'mounted_by') {
    $vars['app_page'] = 'general';
} elseif (! isset($vars['app_page'])) {
    $vars['app_page'] = 'general';
}

$link_array = [
    'page' => 'device',
    'device' => $device['device_id'],
    'tab' => 'apps',
    'app' => 'nfs',
];
$link_array_extra = [
    'flat_mount_options' => $vars['flat_mount_options'],
    'show_mount_options' => $vars['show_mount_options'],
    'app_page' => $vars['app_page'],
];

$is_server = $app->data['is_server'] ?? false;
$is_client = $app->data['is_client'] ?? false;

// The following is only relevant if it is a client or server.
// Both can be false if it has been freshly started, which will also mean
// mounts and mounted_by will both be empty so nothing to display for either.
if ($is_server || $is_client) {
    print_optionbar_start();
    echo generate_link('General', $link_array);

    if ($is_client && isset($app->data['mounts']) && isset($app->data['mounts'][0])) {
        $label = $vars['app_page'] == 'mounts'
            ? '<span class="pagemenu-selected">Mounts</span>'
            : 'Mounts';
        echo ', ' . generate_link($label, $link_array, ['app_page' => 'mounts']) . "\n";
    }

    // showmount -a only works for NFSv2 and NFSv3... unfortunately no NFSv4 equivalent
    // this means no mount info if v4
    if ($is_server && isset($app->data['mounted_by']) && isset($app->data['mounted_by'][0])) {
        $label = $vars['app_page'] == 'mounted_by'
            ? '<span class="pagemenu-selected">Mounted By</span>'
            : 'Mounted By';
        echo ', ' . generate_link($label, $link_array, ['app_page' => 'mounted_by']) . "\n";
    }

    if ($vars['app_page'] == 'mounts') {
        echo '<br>Display Options :: ';
        $label = $vars['flat_mount_options'] == '1'
            ? '<span class="pagemenu-selected">Flat</span>'
            : 'Flat';
        $new_link_array_extra = $link_array_extra;
        $new_link_array_extra['flat_mount_options'] = $vars['flat_mount_options'] == '1'
            ? '0' : '1';
        echo generate_link($label, $link_array, $new_link_array_extra) . ', ';

        $label = $vars['show_mount_options'] == '1'
            ? '<span class="pagemenu-selected">Show Mount Options</span>'
            : 'Show Mount Options';
        $new_link_array_extra = $link_array_extra;
        $new_link_array_extra['show_mount_options'] = $vars['show_mount_options'] == '1'
            ? '0' : '1';
        echo generate_link($label, $link_array, $new_link_array_extra);
    }
    print_optionbar_end();
}

// cache stuff for tables to avoid execesive lookups
$host_cache = [];
$host_cache_device_id = [];
$rpath_cache = [];
$path_cache = [];

if ($vars['app_page'] == 'general') {
    $graphs = [];
    if ($is_server) {
        $graphs['nfs_server_cache'] = 'Server Cache';
        if ($app->data['os'] == 'linux') {
            $graphs['nfs_server_rpc_info'] = 'Server General RPC Stats';
            $graphs['nfs_server_RAcache'] = 'Server Read Ahead Cache';
            $graphs['nfs_server_network'] = 'Server Network';
            $graphs['nfs_server_io'] = 'Server IO';
        } elseif ($app->data['os'] == 'freebsd') {
            $graphs['nfs_server_general'] = 'Server General';
        }
        // figure out what chunks we should add for the nfs_server_stats graph
        $graphs['nfs_server_rpc1'] = 'Server RPC';
        if (isset($nfs_graphs['server_rpc'][$app->data['os']])) {
            $nfs_os = $app->data['os'];
        } else {
            $nfs_os = '';
        }
        $nfs_graph_keys = array_keys($nfs_graphs['server_rpc'][$nfs_os]);
        if (isset($nfs_graph_keys[12])) {
            $graphs['nfs_server_rpc2'] = 'Server RPC, continued';
        }
        if (isset($nfs_graph_keys[24])) {
            $graphs['nfs_server_rpc3'] = 'Server RPC, continued';
        }
        if (isset($nfs_graph_keys[36])) {
            $graphs['nfs_server_rpc4'] = 'Server RPC, continued';
        }
        if (isset($nfs_graph_keys[48])) {
            $graphs['nfs_server_rpc5'] = 'Server RPC, continued';
        }
        if (isset($nfs_graph_keys[48])) {
            $graphs['nfs_server_rpc5'] = 'Server RPC, continued';
        }
        if (isset($nfs_graph_keys[60])) {
            $graphs['nfs_server_rpc6'] = 'Server RPC, continued';
        }
        if (isset($nfs_graph_keys[72])) {
            $graphs['nfs_server_rpc7'] = 'Server RPC, continued';
        }
        if (isset($nfs_graph_keys[84])) {
            $graphs['nfs_server_rpc8'] = 'Server RPC, continued';
        }
        if (isset($nfs_graph_keys[96])) {
            $graphs['nfs_server_rpc9'] = 'Server RPC, continued';
        }
    }
    if ($is_client) {
        $graphs['nfs_client_rpc_info'] = 'Client General RPC Stats';
        if ($app->data['os'] == 'freebsd') {
            $graphs['nfs_client_cache'] = 'Client Cache';
            $graphs['nfs_client_general'] = 'Client General';
        }
        if ($app->data['os'] == 'linux') {
            $graphs['nfs_client_network'] = 'Client Network';
        }
        // figure out what chunks we should add for the nfs_client_stats graph
        $graphs['nfs_client_rpc1'] = 'Client RPC';
        if (isset($nfs_graphs['client_rpc'][$app->data['os']])) {
            $nfs_os = $app->data['os'];
        } else {
            $nfs_os = '';
        }
        $nfs_graph_keys = array_keys($nfs_graphs['client_rpc'][$nfs_os]);
        if (isset($nfs_graph_keys[12])) {
            $graphs['nfs_client_rpc2'] = 'Client RPC, continued';
        }
        if (isset($nfs_graph_keys[24])) {
            $graphs['nfs_client_rpc3'] = 'Client RPC, continued';
        }
        if (isset($nfs_graph_keys[36])) {
            $graphs['nfs_client_rpc4'] = 'Client RPC, continued';
        }
        if (isset($nfs_graph_keys[48])) {
            $graphs['nfs_client_rpc5'] = 'Client RPC, continued';
        }
        if (isset($nfs_graph_keys[60])) {
            $graphs['nfs_client_rpc6'] = 'Client RPC, continued';
        }
        if (isset($nfs_graph_keys[72])) {
            $graphs['nfs_client_rpc7'] = 'Client RPC, continued';
        }
        if (isset($nfs_graph_keys[84])) {
            $graphs['nfs_client_rpc8'] = 'Client RPC, continued';
        }
        if (isset($nfs_graph_keys[96])) {
            $graphs['nfs_client_rpc9'] = 'Client RPC, continued';
        }
    }
} elseif ($vars['app_page'] == 'mounted_by') {
    $table_info = [
        'headers' => [
            'Host',
            'Path',
        ],
        'rows' => [],
    ];
    $mounted_by = $app->data['mounted_by'] ?? [];
    foreach ($mounted_by as $array_location => $data) {
        $new_host = ['data' => ''];
        $new_path = ['data' => ''];
        if (isset($data['host'])) {
            // if not cached yet, to see if we can find more info
            if (! isset($host_cache[$data['host']])) {
                // a quick dumb regex check to make determine if it is IPv4 or IPv6
                // and make sure there are no unexpected charters
                if (preg_match('/^[0-9\.]+$/', $data['host'])) {
                    $ip_info = Ipv4Address::firstWhere(['ipv4_address' => $data['host']]);
                } elseif (preg_match('/^[0-9\:a-fA-F]+$/', $data['host'])) {
                    $ip_info = Ipv6Address::firstWhere(['ipv6_address' => $data['host']]);
                }
                if (isset($ip_info)) {
                    $port = Port::with('device')->firstWhere(['port_id' => $ip_info->port_id]);
                    if (isset($port)) {
                        $new_host['raw'] = true;
                        $new_host['data'] = $data['host'] . ' (' .
                            generate_device_link(['device_id' => $port->device_id]) . ', ' .
                            generate_port_link([
                                'label' => $port->label,
                                'port_id' => $port->port_id,
                                'ifName' => $port->ifName,
                                'device_id' => $port->device_id,
                            ]) . ')';
                    } else {
                        $new_host['data'] = $data['host'];
                    }
                } else {
                    $new_host['data'] = $data['host'];
                }
                $host_cache[$data['host']] = $new_host;
            } else {
                $new_host = $host_cache[$data['host']];
            }
        }
        if (isset($data['path'])) {
            if (! isset($path_cache[$data['host']][$data['path']])) {
                $storage_info = Storage::firstWhere(
                    ['storage_descr' => $data['path']],
                    ['device_id' => $device['device_id']]
                );
                if (! isset($storage_info) && ! preg_match('/^\/+$/', $data['path'])) {
                    $data_path_tmp = $data['path'];
                    $data_path_tmp = preg_replace('/\/[^\/]+$/', '', $data_path_tmp);
                    while ($data_path_tmp != '' && ! isset($storage_info)) {
                        $storage_info = Storage::firstWhere(
                            ['storage_descr' => $data_path_tmp],
                            ['device_id' => $device['device_id']]
                        );
                        if (! isset($storage_info)) {
                            $data_path_tmp = preg_replace('/\/[^\/]+$/', '', $data_path_tmp);
                        }
                    }
                }
                if (isset($storage_info)) {
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

                    $new_path['data'] = \LibreNMS\Util\Url::overlibLink($path_link, $data['path'], $path_overlib_content) .
                        ' (' . round($storage_info['storage_perc']) . '%)' .
                        \LibreNMS\Util\Url::overlibLink($path_link, $path_minigraph, $path_overlib_content);
                    $new_path['raw'] = true;

                    $path_cache[$data['host']][$data['path']] = $new_path;
                } else {
                    $new_path['data'] = $data['path'];
                }
            } else {
                $new_path = $path_cache[$data['host']][$data['path']];
            }
        }
        $table_info['rows'][] = [
            $new_host,
            $new_path,
        ];
    }
    echo view('widgets/sortable_table', $table_info);
} elseif ($vars['app_page'] == 'mounts') {
    $table_info = [
        'headers' => [
            'Host',
            'Remote Path',
            'Local Path',
        ],
        'rows' => [],
    ];
    if ($vars['show_mount_options']) {
        $table_info['headers'][] = 'Mount Options';
    }
    $mounts = $app->data['mounts'] ?? [];
    foreach ($mounts as $array_location => $data) {
        $new_host = ['data' => ''];
        $new_rpath = ['data' => ''];
        $new_lpath = ['data' => ''];
        $new_mntopts = ['data' => ''];
        if (isset($data['host'])) {
            // if not cached yet, to see if we can find more info
            if (! isset($host_cache[$data['host']])) {
                // a quick dumb regex check to make determine if it is IPv4 or IPv6
                // and make sure there are no unexpected charters
                if (preg_match('/^[0-9\.]+$/', $data['host'])) {
                    $ip_info = Ipv4Address::firstWhere(['ipv4_address' => $data['host']]);
                } elseif (preg_match('/^[0-9\:a-fA-F]+$/', $data['host'])) {
                    $ip_info = Ipv6Address::firstWhere(['ipv6_address' => $data['host']]);
                }
                if (isset($ip_info)) {
                    $port = Port::with('device')->firstWhere(['port_id' => $ip_info->port_id]);
                    if (isset($port)) {
                        $new_host['raw'] = true;
                        $new_host['data'] = $data['host'] . ' (' .
                            generate_device_link(['device_id' => $port->device_id]) . ', ' .
                            generate_port_link([
                                'label' => $port->label,
                                'port_id' => $port->port_id,
                                'ifName' => $port->ifName,
                                'device_id' => $port->device_id,
                            ]) . ')';
                    } else {
                        $new_host['data'] = $data['host'];
                    }
                } else {
                    $new_host['data'] = $data['host'];
                }
                $host_cache[$data['host']] = $new_host;
                $host_cache_device_id[$data['host']] = $port->device_id;
            } else {
                $new_host = $host_cache[$data['host']];
            }
            if (! isset($rpath_cache[$data['host']])) {
                $rpath_cache[$data['host']] = [];
            }
        }
        if (isset($data['rpath'])) {
            if (! isset($rpath_cache[$data['host']][$data['rpath']]) && isset($host_cache_device_id[$data['host']])) {
                $storage_info = Storage::firstWhere(
                    ['storage_descr' => $data['rpath']],
                    ['device_id' => $host_cache_device_id[$data['host']]]
                );
                if (isset($storage_info)) {
                    $rpath_graph_array = [];
                    $rpath_graph_array['height'] = '100';
                    $rpath_graph_array['width'] = '210';
                    $rpath_graph_array['to'] = \LibreNMS\Config::get('time.now');
                    $rpath_graph_array['id'] = $storage_info['storage_id'];
                    $rpath_graph_array['type'] = 'storage_usage';
                    $rpath_graph_array['from'] = \LibreNMS\Config::get('time.day');
                    $rpath_graph_array['legend'] = 'no';

                    $rpath_link_array = $rpath_graph_array;
                    $rpath_link_array['page'] = 'graphs';
                    unset($rpath_link_array['height'], $rpath_link_array['width'], $rpath_link_array['legend']);

                    $rpath_link = \LibreNMS\Util\Url::generate($rpath_link_array);

                    $rpath_overlib_content = generate_overlib_content($rpath_graph_array, $device['hostname'] . ' - ' . $storage_info['storage_descr']);

                    $rpath_graph_array['width'] = 80;
                    $rpath_graph_array['height'] = 20;
                    $rpath_graph_array['bg'] = 'ffffff00';
                    $rpath_minigraph = \LibreNMS\Util\Url::lazyGraphTag($rpath_graph_array);

                    $new_rpath['data'] = \LibreNMS\Util\Url::overlibLink($rpath_link, $storage_info['storage_descr'], $rpath_overlib_content) .
                        ' (' . round($storage_info['storage_perc']) . '%)' .
                        \LibreNMS\Util\Url::overlibLink($rpath_link, $rpath_minigraph, $rpath_overlib_content);
                    $new_rpath['raw'] = true;

                    $rpath_cache[$data['host']][$data['rpath']] = $new_path;
                } else {
                    $new_rpath['data'] = $data['rpath'];
                }
            } else {
                $new_rpath = $rpath_cache[$data['host']][$data['rpath']];
            }
        }
        if (isset($data['lpath'])) {
            $new_lpath['data'] = $data['lpath'];
        }
        if ($vars['show_mount_options']) {
            if ($vars['flat_mount_options']) {
                $new_mntopts['raw'] = false;
                if (isset($data['flags'])) {
                    $new_mntopts['data'] = implode(',', $data['flags']);
                }
                if (isset($data['opts'])) {
                    $mnt_opts = array_keys($data['opts']);
                    sort($mnt_opts);
                    if (isset($data['flags'][0]) && isset($mnt_opts[0])) {
                        foreach ($mnt_opts as $mnt_opt) {
                            $new_mntopts['data'] = $new_mntopts['data'] . ', ' . $mnt_opt . '=' . $data['opts'][$mnt_opt];
                        }
                    }
                }
            } else {
                $new_mntopts['raw'] = true;
                if (isset($data['flags']) || isset($data['opts'])) {
                    $mntopts_table_info = [
                        'headers' => [
                            'Options',
                            'Value',
                        ],
                        'rows' => [],
                    ];
                    if (isset($data['flags'])) {
                        $mntopts_table_info['rows'][] = [
                            ['data' => 'flags'],
                            ['data' => implode(',', $data['flags'])],
                        ];
                    }
                    if (isset($data['opts'])) {
                        foreach ($data['opts'] as $mntopts_key => $mntopts_data) {
                            $mntopts_table_info['rows'][] = [
                                ['data' => $mntopts_key],
                                ['data' => $mntopts_data],
                            ];
                        }
                    }
                    $new_mntopts['data'] = view('widgets/sortable_table', $mntopts_table_info);
                }
            }
        }
        $new_row = [
            $new_host,
            $new_rpath,
            $new_lpath,
        ];
        if ($vars['show_mount_options']) {
            $new_row[] = $new_mntopts;
        }
        $table_info['rows'][] = $new_row;
    }
    echo view('widgets/sortable_table', $table_info);
}

foreach ($graphs as $key => $text) {
    $graph_type = $key;
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = \LibreNMS\Config::get('time.now');
    $graph_array['id'] = $app['app_id'];
    $graph_array['type'] = 'application_' . $key;

    $graph_array['nfs_os'] = $app->data['os'];

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
