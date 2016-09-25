<?php

$graphs['apache']    = array(
    'bits',
    'hits',
    'scoreboard',
    'cpu',
);

$graphs['drbd']      = array(
    'disk_bits',
    'network_bits',
    'queue',
    'unsynced',
);

$graphs['mysql']     = array(
    'network_traffic',
    'connections',
    'command_counters',
    'select_types',
);

$graphs['memcached'] = array(
    'bits',
    'commands',
    'data',
    'items',
);

$graphs['nginx']     = array(
    'connections',
    'req',
);

$graphs['powerdns-recursor'] = array(
    'questions',
    'answers',
    'cache_performance',
    'outqueries'
);

$graphs['rrdcached'] = array(
    'queue_length',
    'events',
    'tree',
    'journal'
);

$graphs['bind']      = array('queries');

$graphs['tinydns']   = array(
    'queries',
    'errors',
    'dnssec',
    'other',
);

$graphs['powerdns'] = array(
    'latency',
    'fail',
    'packetcache',
    'querycache',
    'recursing',
    'queries',
    'queries_udp',
);

$graphs['ntp-client'] = array(
    'stats',
    'freq',
);

$graphs['ntp-server'] = array(
    'stats',
    'freq',
    'stratum',
    'buffer',
    'bits',
    'packets',
    'uptime',
);

$graphs['nfs-v3-stats'] = array(
    'stats',
    'io',
    'fh',
    'rc',
    'ra',
    'net',
    'rpc',
);

$graphs['os-updates'] = array(
    'packages',
);
$graphs['dhcp-stats'] = array(
     'stats',
);

$graphs['freeswitch'] = array(
    'peak',
    'callsIn',
    'callsOut',
);

$graphs['ups-nut'] = array(
    'remaining',
    'load',
    'voltage_battery',
    'charge',
    'voltage_input',
);

$graphs['ups-apcups'] = array(
    'remaining',
    'load',
    'voltage_battery',
    'charge',
    'voltage_input',
);

print_optionbar_start();

echo "<span style='font-weight: bold;'>Apps</span> &#187; ";

unset($sep);

$link_array = array(
    'page'   => 'device',
    'device' => $device['device_id'],
    'tab'    => 'apps',
);

foreach ($app_list as $app) {
    echo $sep;

    // if (!$vars['app']) { $vars['app'] = $app['app_type']; }
    if ($vars['app'] == $app['app_type']) {
        echo "<span class='pagemenu-selected'>";
        // echo('<img src="images/icons/'.$app['app_type'].'.png" class="optionicon" />');
    } else {
        // echo('<img src="images/icons/greyscale/'.$app['app_type'].'.png" class="optionicon" />');
    }

    echo generate_link(nicecase($app['app_type']), array('page' => 'apps', 'app' => $app['app_type']));
    if ($vars['app'] == $app['app_type']) {
        echo '</span>';
    }

    $sep = ' | ';
}

print_optionbar_end();

if ($vars['app']) {
    if (is_file('pages/apps/'.mres($vars['app']).'.inc.php')) {
        include 'pages/apps/'.mres($vars['app']).'.inc.php';
    } else {
        include 'pages/apps/default.inc.php';
    }
} else {
    include 'pages/apps/overview.inc.php';
}

$pagetitle[] = 'Apps';
