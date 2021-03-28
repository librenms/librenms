<?php

$graphs = [
    'ceph_poolstats'    => 'Pool stats',
    'ceph_osdperf'      => 'OSD Performance',
    'ceph_df'           => 'Usage',
];

foreach ($graphs as $key => $text) {
    echo '<h3>' . $text . '</h3>';
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = \LibreNMS\Config::get('time.now');
    $graph_array['id'] = $app['app_id'];

    if ($key == 'ceph_poolstats') {
        foreach (glob(Rrd::name($device['hostname'], ['app', 'ceph', $app['app_id'], 'pool'], '-*.rrd')) as $rrd_filename) {
            if (preg_match("/.*-pool-(.+)\.rrd$/", $rrd_filename, $pools)) {
                $graph_array['to'] = \LibreNMS\Config::get('time.now');
                $graph_array['id'] = $app['app_id'];
                $pool = $pools[1];
                echo '<h3>' . $pool . ' Reads/Writes</h3>';
                $graph_array['type'] = 'application_ceph_pool_io';
                $graph_array['pool'] = $pool;

                echo "<tr bgcolor='$row_colour'><td colspan=5>";
                include 'includes/html/print-graphrow.inc.php';
                echo '</td></tr>';

                $graph_array['to'] = \LibreNMS\Config::get('time.now');
                $graph_array['id'] = $app['app_id'];
                echo '<h3>' . $pool . ' IOPS</h3>';
                $graph_array['type'] = 'application_ceph_pool_iops';
                $graph_array['pool'] = $pool;

                echo "<tr bgcolor='$row_colour'><td colspan=5>";
                include 'includes/html/print-graphrow.inc.php';
                echo '</td></tr>';
            }
        }
    } elseif ($key == 'ceph_osdperf') {
        foreach (glob(Rrd::name($device['hostname'], ['app', 'ceph', $app['app_id'], 'osd'], '-*.rrd')) as $rrd_filename) {
            $graph_array['to'] = \LibreNMS\Config::get('time.now');
            $graph_array['id'] = $app['app_id'];
            if (preg_match("/.*-osd-(.+)\.rrd$/", $rrd_filename, $osds)) {
                $osd = $osds[1];
                echo '<h3>' . $osd . ' Latency</h3>';
                $graph_array['type'] = 'application_ceph_osd_performance';
                $graph_array['osd'] = $osd;

                echo "<tr bgcolor='$row_colour'><td colspan=5>";
                include 'includes/html/print-graphrow.inc.php';
                echo '</td></tr>';
            }
        }
    } elseif ($key == 'ceph_df') {
        foreach (glob(Rrd::name($device['hostname'], ['app', 'ceph', $app['app_id'], 'df'], '-*.rrd')) as $rrd_filename) {
            if (preg_match("/.*-df-(.+)\.rrd$/", $rrd_filename, $pools)) {
                $pool = $pools[1];
                if ($pool == 'c') {
                    echo '<h3>Cluster Usage</h3>';
                    $graph_array['to'] = \LibreNMS\Config::get('time.now');
                    $graph_array['id'] = $app['app_id'];
                    $graph_array['type'] = 'application_ceph_pool_df';
                    $graph_array['pool'] = $pool;

                    echo "<tr bgcolor='$row_colour'><td colspan=5>";
                    include 'includes/html/print-graphrow.inc.php';
                    echo '</td></tr>';
                } else {
                    echo '<h3>' . $pool . ' Usage</h3>';
                    $graph_array['to'] = \LibreNMS\Config::get('time.now');
                    $graph_array['id'] = $app['app_id'];
                    $graph_array['type'] = 'application_ceph_pool_df';
                    $graph_array['pool'] = $pool;

                    echo "<tr bgcolor='$row_colour'><td colspan=5>";
                    include 'includes/html/print-graphrow.inc.php';
                    echo '</td></tr>';

                    echo '<h3>' . $pool . ' Objects</h3>';
                    $graph_array['to'] = \LibreNMS\Config::get('time.now');
                    $graph_array['id'] = $app['app_id'];
                    $graph_array['type'] = 'application_ceph_pool_objects';
                    $graph_array['pool'] = $pool;

                    echo "<tr bgcolor='$row_colour'><td colspan=5>";
                    include 'includes/html/print-graphrow.inc.php';
                    echo '</td></tr>';
                }
            }
        }
    }
}
