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

$rrd_name = ['app', $name, $app_id];

$snmp_fields = [
    'com_delete', 'com_insert', 'com_insert_select', 'com_load', 'com_replace', 'com_replace_select', 'com_select', 'com_update', 'com_update_multi',
    'max_connections','max_used_connections','aborted_clients','aborted_connects','threads_connected','connections',
    'table_open_cache','open_files','open_tables','opened_tables',
    'ib_bpool_size','ib_bpool_dbpages','ib_bpool_free','ib_bpool_modpages',
    'ib_bpool_read','ib_bpool_created','ib_bpool_written',
    'ib_merged_ops_insert','ib_merged_ops_delete','ib_discarded_ops_insert','ib_discarded_ops_delete','ib_search_cache','ib_search_non_cache',
    'ib_io_read','ib_io_write','ib_io_log','ib_io_fsync',
    'ib_iop_log','ib_iop_sync','ib_iop_flush_log','ib_iop_flush_bpool','ib_iop_ibuf_aio',
    'innodb_log_buffer_size','ib_log_flush','ib_log_written',
    'innodb_rows_deleted','innodb_rows_inserted','innodb_rows_read','innodb_rows_updated',
    'ib_spin_rounds','ib_spin_waits','ib_os_waits',
    'ib_tnx',
    'key_read_requests','key_reads','key_write_requests','key_writes',
    'bytes_received','bytes_sent',
    'qcache_queries_in_cache','qcache_hits','qcache_inserts','qcache_not_cached','qcache_lowmem_prunes',
    'query_cache_size','qcache_free_memory',
    'select_full_join','select_full_range_join','select_range','select_range_check','select_scan',
    'slow_queries',
    'sort_rows','sort_range','sort_merge_passes','sort_scan',
    'table_locks_immediate','table_locks_waited',
    'created_tmp_disk_tables','created_tmp_tables','created_tmp_files'
];

$rrd_def = new RrdDefinition();

$fields = [];
foreach ($snmp_fields as $name) {
    if (isset($mysql['data'][$name]) && $mysql['data'][$name] >= 0) {
        $fields[$name] = $mysql['data'][$name];
    } else {
        $fields[$name] = 'U';
    }
    $rrd_def->addDataset($name, 'GAUGE', 0, 125000000000);
}

$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);
update_application($app, 'OK', $fields);
