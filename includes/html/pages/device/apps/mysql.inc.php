<?php

print_optionbar_start();

echo "<span style='font-weight: bold;'>" . \LibreNMS\Util\StringHelpers::niceCase($app['app_type']) . '</span> &#187; ';

$app_sections = [
    'system'  => 'System',
    'queries' => 'Queries',
    'innodb'  => 'InnoDB',
];

unset($sep);
foreach ($app_sections as $app_section => $app_section_text) {
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

$graphs['system'] = [
    'mysql_connections'       => 'Connections',
    'mysql_files_tables'      => 'Files and Tables',
    'mysql_myisam_indexes'    => 'MyISAM Indexes',
    'mysql_network_traffic'   => 'Network Traffic',
    'mysql_table_locks'       => 'Table Locks',
    'mysql_temporary_objects' => 'Temporary Objects',
];

$graphs['queries'] = [
    'mysql_command_counters'   => 'Command Counters',
    'mysql_query_cache'        => 'Query Cache',
    'mysql_query_cache_memory' => 'Query Cache Memory',
    'mysql_select_types'       => 'Select Types',
    'mysql_slow_queries'       => 'Slow Queries',
    'mysql_sorts'              => 'Sorts',
];

$graphs['innodb'] = [
    'mysql_innodb_buffer_pool'          => 'InnoDB Buffer Pool',
    'mysql_innodb_buffer_pool_activity' => 'InnoDB Buffer Pool Activity',
    'mysql_innodb_insert_buffer'        => 'InnoDB Insert Buffer',
    'mysql_innodb_io'                   => 'InnoDB IO',
    'mysql_innodb_io_pending'           => 'InnoDB IO Pending',
    'mysql_innodb_log'                  => 'InnoDB Log',
    'mysql_innodb_row_operations'       => 'InnoDB Row Operations',
    'mysql_innodb_semaphores'           => 'InnoDB semaphores',
    'mysql_innodb_transactions'         => 'InnoDB Transactions',
];

foreach ($graphs[$vars['app_section']] as $key => $text) {
    $graph_type = $key;
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = \LibreNMS\Config::get('time.now');
    $graph_array['id'] = $app['app_id'];
    $graph_array['type'] = 'application_' . $key;

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
