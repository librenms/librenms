<?php
global $config;

$graphs = array('mysql_command_counters' => 'Command Counters',
                'mysql_connections' => 'Connections',
                'mysql_files_tables' => 'Files and Tables',
                'mysql_innodb_buffer_pool' => 'InnoDB Buffer Pool',
                'mysql_innodb_buffer_pool_activity' => 'InnoDB Buffer Pool Activity',
                'mysql_innodb_insert_buffer' => 'InnoDB Insert Buffer',
                'mysql_innodb_io' => 'InnoDB IO',
                'mysql_innodb_io_pending' => 'InnoDB IO Pending',
                'mysql_innodb_log' => 'InnoDB Log',
                'mysql_innodb_row_operations' => 'InnoDB Row Operations',
                'mysql_innodb_semaphores' => 'InnoDB semaphores',
                'mysql_innodb_transactions' => 'InnoDB Transactions',
                'mysql_myisam_indexes' => 'MyISAM Indexes',
                'mysql_network_traffic' => 'Network Traffic',
                'mysql_query_cache' => 'Query Cache',
                'mysql_query_cache_memory' => 'Query Cache Memory',
                'mysql_select_types' => 'Select Types',
                'mysql_slow_queries' => 'Slow Queries',
                'mysql_sorts' => 'Sorts',
                'mysql_table_locks' => 'Table Locks',
                'mysql_temporary_objects' => 'Temporary Objects');

foreach ($graphs as $key => $text)
{
  $graph_type = $key;
  $graph_array['height'] = "100";
  $graph_array['width']  = "215";
  $graph_array['to']     = $now;
  $graph_array['id']     = $app['app_id'];
  $graph_array['type']   = "application_".$key;
  echo('<h3>'.$text.'</h3>');

  echo("<tr bgcolor='$row_colour'><td colspan=5>");

  include("includes/print-quadgraphs.inc.php");
  echo("</td></tr>");
}

?>