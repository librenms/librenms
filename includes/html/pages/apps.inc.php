<?php
$pagetitle[] = 'Apps';
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
$graphs['entropy']   = array(
    'entropy',
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
$graphs['redis'] = array(
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
);
$graphs['nginx']     = array(
    'connections',
    'req',
);
$graphs['postfix'] = array(
    'messages',
    'qstats',
    'bytes',
    'sr',
    'deferral',
    'rejects',
);
$graphs['powerdns-recursor'] = array(
    'questions',
    'answers',
    'cache_performance',
    'outqueries'
);
$graphs['pureftpd'] = array(
    'bitrate',
    'connections',
    'users'
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
$graphs['postgres'] = array(
    'backends',
    'cr',
    'rows',
    'hr',
    'index',
    'sequential'
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
$graphs['nfs-server'] = array(
    'io',
    'net_tcp_conns',
    'rpc',
);
$graphs['os-updates'] = array(
    'packages',
);
$graphs['dhcp-stats'] = array(
     'stats',
     'pools_percent',
     'pools_current',
     'pools_max',
     'networks_percent',
     'networks_current',
     'networks_max',
);
$graphs['fail2ban'] = array(
    'banned',
);
$graphs['freeswitch'] = array(
    'peak',
    'calls',
    'channels',
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
$graphs['gpsd'] = array(
    'satellites',
    'dop',
    'mode',
);
$graphs['exim-stats'] = array(
    'frozen',
    'queue'
);
$graphs['php-fpm'] = array(
    'stats'
);
$graphs['nvidia'] = array(
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
);
$graphs['seafile'] = array(
    'connected',
    'enabled',
    'libraries',
    'trashed_libraries',
    'size_consumption',
    'groups',
    'version',
    'platform',
);
$graphs['squid'] = array(
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
);
$graphs['opengridscheduler'] = array(
    'ogs'
);
$graphs['fbsd-nfs-server'] = array(
    'stats',
    'cache',
    'gathering',
);
$graphs['fbsd-nfs-client'] = array(
    'stats',
    'cache',
    'rpc',
);
$graphs['unbound'] = array(
    'queries',
    'cache',
    'operations',
);
$graphs['bind']      = array(
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
);
$graphs['smart'] = array(
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
);
$graphs['certificate'] = array(
    'age',
    'remaining_days',
);
$graphs['puppet-agent'] = array(
    'last_run',
    'changes',
    'events',
    'resources',
    'time',
);
$graphs['mdadm'] = array(
    'level',
    'size',
    'disc_count',
    'hotspare_count',
    'degraded',
    'sync_speed',
    'sync_completed',
);
$graphs['sdfsinfo'] = array(
    'volume',
    'blocks',
    'rates',
);
$graphs['pi-hole'] = array(
    'query_types',
    'destinations',
    'query_results',
    'block_percent',
    'blocklist',
);
$graphs['freeradius'] = array(
    'access',
    'auth',
    'acct',
    'proxy_access',
    'proxy_auth',
    'proxy_acct',
    'queue',
);
$graphs['zfs'] = array(
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
);
$graphs['powerdns-dnsdist'] = array(
    'cache',
    'downstream',
    'dynamic_blocks',
    'latency',
    'queries_latency',
    'queries_stats',
    'rules_stats',
    'queries_drop',
);
$graphs['asterisk'] = array(
    'calls',
    'channels',
    'sip',
    'iax2',
);
$graphs['mailcow-postfix'] = array(
    'emails',
    'traffic',
    'domains',
);
$graphs['backupninja'] = array(
    'backupninja',
);

echo '<div class="panel panel-default">';
echo '<div class="panel-heading">';
echo "<span style='font-weight: bold;'>Apps</span> &#187; ";
unset($sep);
$link_array = array(
    'page'   => 'device',
    'device' => $device['device_id'],
    'tab'    => 'apps',
);


$apps = \LibreNMS\Util\ObjectCache::applications()->flatten();
foreach ($apps as $app) {
    $app_state = \LibreNMS\Util\Html::appStateIcon($app->app_state);
    if (!empty($app_state['icon'])) {
        $app_state_info = "<font color=\"".$app_state['color']."\"><i title=\"".$app_state['hover_text']."\" class=\"fa ".$app_state['icon']." fa-fw fa-lg\" aria-hidden=\"true\"></i></font>";
    } else {
        $app_state_info = '';
    }

    echo $sep;
    if ($vars['app'] == $app->app_type) {
        echo "<span class='pagemenu-selected'>";
    }
    echo $app_state_info;
    echo generate_link($app->displayName(), array('page' => 'apps', 'app' => $app->app_type));
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
