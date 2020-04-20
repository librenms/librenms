<?php
/*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*
* @package    LibreNMS
* @link       http://librenms.org
* @copyright  2020 LibreNMS
* @author     Cercel Valentin <crc@nuamchefazi.ro>
*/

use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\RRD\RrdDefinition;

$name = 'mysql';
$app_id = $app['app_id'];
d_echo($name);

try {
    $mysql = json_app_get($device, $name);
} catch (JsonAppException $e) {
    echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
    update_application($app, $e->getCode() . ':' . $e->getMessage(), []); // Set empty metrics and error message
    return;
}

$rrd_name = array('app', $name, $app_id);

$rrd_def = RrdDefinition::make()
    ->addDataset('com_delete', 'GAUGE', 0)
    ->addDataset('com_insert', 'GAUGE', 0)
    ->addDataset('com_insert_select', 'GAUGE', 0)
    ->addDataset('com_load', 'GAUGE', 0)
    ->addDataset('com_replace', 'GAUGE', 0)
    ->addDataset('com_replace_select', 'GAUGE', 0)
    ->addDataset('com_select', 'GAUGE', 0)
    ->addDataset('com_update', 'GAUGE', 0)
    ->addDataset('com_update_multi', 'GAUGE', 0)
    ->addDataset('max_connections', 'GAUGE', 0)
    ->addDataset('max_used_connections', 'GAUGE', 0)
    ->addDataset('aborted_clients', 'GAUGE', 0)
    ->addDataset('aborted_connects', 'GAUGE', 0)
    ->addDataset('threads_connected', 'GAUGE', 0)
    ->addDataset('connections', 'GAUGE', 0)
    ->addDataset('table_open_cache', 'GAUGE', 0)
    ->addDataset('open_files', 'GAUGE', 0)
    ->addDataset('open_tables', 'GAUGE', 0)
    ->addDataset('opened_tables', 'GAUGE', 0)
    ->addDataset('innodb_log_buffer_size', 'GAUGE', 0)
    ->addDataset('innodb_rows_deleted', 'GAUGE', 0)
    ->addDataset('innodb_rows_inserted', 'GAUGE', 0)
    ->addDataset('innodb_rows_read', 'GAUGE', 0)
    ->addDataset('innodb_rows_updated', 'GAUGE', 0)
    ->addDataset('key_read_requests', 'GAUGE', 0)
    ->addDataset('key_reads', 'GAUGE', 0)
    ->addDataset('key_write_requests', 'GAUGE', 0)
    ->addDataset('key_writes', 'GAUGE', 0)
    ->addDataset('bytes_received', 'GAUGE', 0)
    ->addDataset('bytes_sent', 'GAUGE', 0)
    ->addDataset('qcache_queries_in_cache', 'GAUGE', 0)
    ->addDataset('qcache_hits', 'GAUGE', 0)
    ->addDataset('qcache_inserts', 'GAUGE', 0)
    ->addDataset('qcache_not_cached', 'GAUGE', 0)
    ->addDataset('qcache_lowmem_prunes', 'GAUGE', 0)
    ->addDataset('query_cache_size', 'GAUGE', 0)
    ->addDataset('qcache_free_memory', 'GAUGE', 0)
    ->addDataset('select_full_join', 'GAUGE', 0)
    ->addDataset('select_full_range_join', 'GAUGE', 0)
    ->addDataset('select_range', 'GAUGE', 0)
    ->addDataset('select_range_check', 'GAUGE', 0)
    ->addDataset('select_scan', 'GAUGE', 0)
    ->addDataset('slow_queries', 'GAUGE', 0)
    ->addDataset('sort_rows', 'GAUGE', 0)
    ->addDataset('sort_range', 'GAUGE', 0)
    ->addDataset('sort_merge_passes', 'GAUGE', 0)
    ->addDataset('sort_scan', 'GAUGE', 0)
    ->addDataset('table_locks_immediate', 'GAUGE', 0)
    ->addDataset('table_locks_waited', 'GAUGE', 0)
    ->addDataset('created_tmp_disk_tables', 'GAUGE', 0)
    ->addDataset('created_tmp_tables', 'GAUGE', 0)
    ->addDataset('created_tmp_files', 'GAUGE', 0);


$fields = array(
    'com_delete' => $mysql['data']['com_delete'],
    'com_insert' => $mysql['data']['com_insert'],
    'com_insert_select' => $mysql['data']['com_insert_select'],
    'com_load' => $mysql['data']['com_load'],
    'com_replace' => $mysql['data']['com_replace'],
    'com_replace_select' => $mysql['data']['com_replace_select'],
    'com_select' => $mysql['data']['com_select'],
    'com_update' => $mysql['data']['com_update'],
    'com_update_multi' => $mysql['data']['com_update_multi'],
    'max_connections' => $mysql['data']['max_connections'],
    'max_used_connections' => $mysql['data']['max_used_connections'],
    'aborted_clients' => $mysql['data']['aborted_clients'],
    'aborted_connects' => $mysql['data']['aborted_connects'],
    'threads_connected' => $mysql['data']['threads_connected'],
    'connections' => $mysql['data']['connections'],
    'table_open_cache' => $mysql['data']['table_open_cache'],
    'open_files' => $mysql['data']['open_files'],
    'open_tables' => $mysql['data']['open_tables'],
    'opened_tables' => $mysql['data']['opened_tables'],
    'innodb_log_buffer_size' => $mysql['data']['innodb_log_buffer_size'],
    'innodb_rows_deleted' => $mysql['data']['innodb_rows_deleted'],
    'innodb_rows_inserted' => $mysql['data']['innodb_rows_inserted'],
    'innodb_rows_read' => $mysql['data']['innodb_rows_read'],
    'innodb_rows_updated' => $mysql['data']['innodb_rows_updated'],
    'key_read_requests' => $mysql['data']['key_read_requests'],
    'key_reads' => $mysql['data']['key_reads'],
    'key_write_requests' => $mysql['data']['key_write_requests'],
    'key_writes' => $mysql['data']['key_writes'],
    'bytes_received' => $mysql['data']['bytes_received'],
    'bytes_sent' => $mysql['data']['bytes_sent'],
    'qcache_queries_in_cache' => $mysql['data']['qcache_queries_in_cache'],
    'qcache_hits' => $mysql['data']['qcache_hits'],
    'qcache_inserts' => $mysql['data']['qcache_inserts'],
    'qcache_not_cached' => $mysql['data']['qcache_not_cached'],
    'qcache_lowmem_prunes' => $mysql['data']['qcache_lowmem_prunes'],
    'query_cache_size' => $mysql['data']['query_cache_size'],
    'qcache_free_memory' => $mysql['data']['qcache_free_memory'],
    'select_full_join' => $mysql['data']['select_full_join'],
    'select_full_range_join' => $mysql['data']['select_full_range_join'],
    'select_range' => $mysql['data']['select_range'],
    'select_range_check' => $mysql['data']['select_range_check'],
    'select_scan' => $mysql['data']['select_scan'],
    'slow_queries' => $mysql['data']['slow_queries'],
    'sort_rows' => $mysql['data']['sort_rows'],
    'sort_range' => $mysql['data']['sort_range'],
    'sort_merge_passes' => $mysql['data']['sort_merge_passes'],
    'sort_scan' => $mysql['data']['sort_scan'],
    'table_locks_immediate' => $mysql['data']['table_locks_immediate'],
    'table_locks_waited' => $mysql['data']['table_locks_waited'],
    'created_tmp_disk_tables' => $mysql['data']['created_tmp_disk_tables'],
    'created_tmp_tables' => $mysql['data']['created_tmp_tables'],
    'created_tmp_files' => $mysql['data']['created_tmp_files'],
);

$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);
update_application($app, 'OK', $fields);
