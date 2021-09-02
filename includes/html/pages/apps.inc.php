<?php

$pagetitle[] = 'Apps';
$graphs['apache'] = [
    'bits',
    'hits',
    'scoreboard',
    'cpu',
];
$graphs['drbd'] = [
    'disk_bits',
    'network_bits',
    'queue',
    'unsynced',
];
$graphs['entropy'] = [
    'entropy',
];
$graphs['mysql'] = [
    'network_traffic',
    'connections',
    'command_counters',
    'select_types',
];
$graphs['memcached'] = [
    'bits',
    'commands',
    'data',
    'items',
];
$graphs['redis'] = [
    'clients',
    'objects',
    'fragmentation',
    'usage',
    'defrag',
    'keyspace',
    'sync',
    'commands',
    'connections',
    'net',
];
$graphs['nginx'] = [
    'connections',
    'req',
];
$graphs['postfix'] = [
    'messages',
    'qstats',
    'bytes',
    'sr',
    'deferral',
    'rejects',
];
$graphs['powerdns-recursor'] = [
    'questions',
    'answers',
    'cache_performance',
    'outqueries',
];
$graphs['powermon'] = [
    'consumption',
];
$graphs['pureftpd'] = [
    'bitrate',
    'connections',
    'users',
];
$graphs['rrdcached'] = [
    'queue_length',
    'events',
    'tree',
    'journal',
];
$graphs['bind'] = ['queries'];
$graphs['tinydns'] = [
    'queries',
    'errors',
    'dnssec',
    'other',
];
$graphs['postgres'] = [
    'backends',
    'cr',
    'rows',
    'hr',
    'index',
    'sequential',
];
$graphs['powerdns'] = [
    'latency',
    'fail',
    'packetcache',
    'querycache',
    'recursing',
    'queries',
    'queries_udp',
];
$graphs['ntp-client'] = [
    'stats',
    'freq',
];
$graphs['ntp-server'] = [
    'stats',
    'freq',
    'stratum',
    'buffer',
    'bits',
    'packets',
    'uptime',
];
$graphs['nfs-v3-stats'] = [
    'stats',
    'io',
    'fh',
    'rc',
    'ra',
    'net',
    'rpc',
];
$graphs['nfs-server'] = [
    'io',
    'net_tcp_conns',
    'rpc',
];
$graphs['os-updates'] = [
    'packages',
];
$graphs['dhcp-stats'] = [
    'stats',
    'pools_percent',
    'pools_current',
    'pools_max',
    'networks_percent',
    'networks_current',
    'networks_max',
];
$graphs['fail2ban'] = [
    'banned',
];
$graphs['freeswitch'] = [
    'peak',
    'calls',
    'channels',
    'callsIn',
    'callsOut',
];
$graphs['ups-nut'] = [
    'remaining',
    'load',
    'voltage_battery',
    'charge',
    'voltage_input',
];
$graphs['ups-apcups'] = [
    'remaining',
    'load',
    'voltage_battery',
    'charge',
    'voltage_input',
];
$graphs['gpsd'] = [
    'satellites',
    'dop',
    'mode',
];
$graphs['exim-stats'] = [
    'frozen',
    'queue',
];
$graphs['php-fpm'] = [
    'stats',
];
$graphs['nvidia'] = [
    'sm',
    'mem',
    'enc',
    'dec',
    'rxpci',
    'txpci',
    'fb',
    'bar1',
    'mclk',
    'pclk',
    'pwr',
    'temp',
    'pviol',
    'tviol',
    'sbecc',
    'dbecc',
];
$graphs['seafile'] = [
    'connected',
    'enabled',
    'libraries',
    'trashed_libraries',
    'size_consumption',
    'groups',
    'version',
    'platform',
];
$graphs['squid'] = [
    'memory',
    'clients',
    'cpuusage',
    'objcount',
    'filedescr',
    'httpbw',
    'http',
    'server',
    'serverbw',
    'reqhit',
    'bytehit',
    'sysnumread',
    'pagefaults',
    'cputime',
];
$graphs['opengridscheduler'] = [
    'ogs',
];
$graphs['fbsd-nfs-server'] = [
    'stats',
    'cache',
    'gathering',
];
$graphs['fbsd-nfs-client'] = [
    'stats',
    'cache',
    'rpc',
];
$graphs['unbound'] = [
    'queries',
    'cache',
    'operations',
];
$graphs['bind'] = [
    'incoming',
    'outgoing',
    'rr_positive',
    'rr_negative',
    'rtt',
    'resolver_failure',
    'resolver_qrs',
    'resolver_naf',
    'server_received',
    'server_results',
    'server_issues',
    'cache_hm',
    'adb_in',
    'sockets_active',
    'sockets_errors',
];
$graphs['smart'] = [
    'id5',
    'id9',
    'id10',
    'id173',
    'id183',
    'id184',
    'id187',
    'id188',
    'id190',
    'id194',
    'id196',
    'id197',
    'id198',
    'id199',
    'id231',
    'id233',
];
$graphs['certificate'] = [
    'age',
    'remaining_days',
];
$graphs['puppet-agent'] = [
    'last_run',
    'changes',
    'events',
    'resources',
    'time',
];
$graphs['mdadm'] = [
    'level',
    'size',
    'disc_count',
    'hotspare_count',
    'degraded',
    'sync_speed',
    'sync_completed',
];
$graphs['sdfsinfo'] = [
    'volume',
    'blocks',
    'rates',
];
$graphs['pi-hole'] = [
    'query_types',
    'destinations',
    'query_results',
    'block_percent',
    'blocklist',
];
$graphs['freeradius'] = [
    'access',
    'auth',
    'acct',
    'proxy_access',
    'proxy_auth',
    'proxy_acct',
    'queue',
];
$graphs['zfs'] = [
    'arc_misc',
    'arc_size',
    'arc_size_per',
    'arc_size_breakdown',
    'arc_efficiency',
    'arc_cache_hits_by_list',
    'arc_cache_hits_by_type',
    'arc_cache_misses_by_type',
    'arc_cache_hits',
    'arc_cache_miss',
];
$graphs['powerdns-dnsdist'] = [
    'cache',
    'downstream',
    'dynamic_blocks',
    'latency',
    'queries_latency',
    'queries_stats',
    'rules_stats',
    'queries_drop',
];
$graphs['asterisk'] = [
    'calls',
    'channels',
    'sip',
    'iax2',
];
$graphs['mailcow-postfix'] = [
    'emails',
    'traffic',
    'domains',
];
$graphs['backupninja'] = [
    'backupninja',
];
$graphs['icecast'] = [
    'cpuload',
    'memoryusage',
    'openfiles',
];
$graphs['opensips'] = [
    'load',
    'memory',
    'openfiles',
];
$graphs['voip-monitor'] = [
    'cpuload',
    'memoryusage',
    'openfiles',
];
$graphs['docker'] = [
    'cpu_usage',
    'pids',
    'mem_limit',
    'mem_used',
    'mem_perc',
];
$graphs['chronyd'] = [
    'time',
    'frequency',
    'root',
];

echo '<div class="panel panel-default">';
echo '<div class="panel-heading">';
echo "<span style='font-weight: bold;'>Apps</span> &#187; ";
unset($sep);
$link_array = [
    'page'   => 'device',
    'device' => $device['device_id'],
    'tab'    => 'apps',
];

$apps = \LibreNMS\Util\ObjectCache::applications()->flatten();
foreach ($apps as $app) {
    $app_state = \LibreNMS\Util\Html::appStateIcon($app->app_state);
    if (! empty($app_state['icon'])) {
        $app_state_info = '<font color="' . $app_state['color'] . '"><i title="' . $app_state['hover_text'] . '" class="fa ' . $app_state['icon'] . ' fa-fw fa-lg" aria-hidden="true"></i></font>';
    } else {
        $app_state_info = '';
    }

    echo $sep;
    if ($vars['app'] == $app->app_type) {
        echo "<span class='pagemenu-selected'>";
    }
    echo $app_state_info;
    echo generate_link($app->displayName(), ['page' => 'apps', 'app' => $app->app_type]);
    if ($vars['app'] == $app->app_type) {
        echo '</span>';
    }
    $sep = ' | ';
}
echo '</div>';
echo '<div class="panel-body">';
if (isset($vars['app'])) {
    $app = basename($vars['app']);
    if (is_file("includes/html/pages/apps/$app.inc.php")) {
        include "includes/html/pages/apps/$app.inc.php";
    } else {
        include 'includes/html/pages/apps/default.inc.php';
    }
} else {
    include 'includes/html/pages/apps/overview.inc.php';
}
echo '</div>';
