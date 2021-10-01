<?php
/*
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @package    LibreNMS
 * @subpackage nfs-server
 * @link       https://www.librenms.org
 * @copyright  2017 LibreNMS
 * @author     SvennD <svennd@svennd.be>
*/

print_optionbar_start();

echo "<span style='font-weight: bold;'>" . \LibreNMS\Util\StringHelpers::niceCase($app['app_type']) . '</span> &#187; ';

$app_sections = [
    'default'  => 'NFS',
    'proc2'  => 'NFS v2',
    'proc3'  => 'NFS v3',
    'proc4'  => 'NFS v4',
];

unset($sep);

foreach ($app_sections as $app_section => $app_section_text) {
    // remove entries that have no rrd associated
    // commonly proc2 will be invisible
    $var_rrd = Rrd::name($device['hostname'], 'app-nfs-server-' . $app_section . '-' . $app['app_id']);
    if (! Rrd::checkRrdExists($var_rrd)) {
        continue;
    }

    echo $sep;

    if (! $vars['app_section']) {
        $vars['app_section'] = $app_section;
    }

    if ($vars['app_section'] == $app_section) {
        echo "<span class='pagemenu-selected'>";
    }

    echo generate_link($app_section_text, $vars, ['app_section' => $app_section]);
    if ($vars['app_section'] == $app_section) {
        echo '</span>';
    }

    $sep = ' | ';
}

print_optionbar_end();

unset($graphs);

// stat => array(text, rrd)
$graphs['default'] = [
    'nfs-server_net' => ['Network stats', 'default'],
    'nfs-server_rpc' => ['RPC Stats', 'default'],
    'nfs-server_io' => ['IO', 'default'],
    'nfs-server_fh' => ['File handler', 'default'],
    'nfs-server_rc' => ['Reply cache', 'default'],
    'nfs-server_ra' => ['Read ahead cache', 'default'],
];

$graphs['proc2'] = [
    'nfs-server_stats_v2' => ['NFS v2 Statistics', 'proc2'],
];

$graphs['proc3'] = [
    'nfs-server_stats_io' => ['NFS v3 Read/Write operations', 'proc3'],
    'nfs-server_stats_read' => ['NFS v3 other read operations', 'proc3'],
    'nfs-server_stats_write' => ['NFS v3 other write operations', 'proc3'],
];

$graphs['proc4'] = [
    'nfs-server_stats_v4' => ['NFS v4 Statistics', 'proc4'],
    'nfs-server_v4ops' => ['NFS v4ops Statistics', 'proc4ops'],
];

foreach ($graphs[$vars['app_section']] as $key => $info) {
    // check if they exist
    if (! Rrd::checkRrdExists(Rrd::name($device['hostname'], 'app-nfs-server-' . $info[1] . '-' . $app['app_id']))) {
        continue;
    }

    $graph_type = $key;
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = \LibreNMS\Config::get('time.now');
    $graph_array['id'] = $app['app_id'];
    $graph_array['type'] = 'application_' . $key;

    echo '<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">' . $info[0] . '</h3>
    </div>
    <div class="panel-body">
    <div class="row">';
    include 'includes/html/print-graphrow.inc.php';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}
