<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TheGreatIndexRename extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // try to run index like this to hopefully allow mysql to optimize away the reindex
        if (\LibreNMS\DB\Eloquent::getDriver() == 'mysql') {
            $this->renameIndex('access_points', 'deleted', 'access_points_deleted_index', ['deleted']);
            $this->renameIndex('access_points', 'deleted', 'access_points_deleted_index', ['deleted']);
            $this->renameIndex('alerts', 'device_id', 'alerts_device_id_index', ['device_id']);
            $this->renameIndex('alerts', 'rule_id', 'alerts_device_id_rule_id_unique', ['device_id', 'rule_id'], true);
            $this->renameIndex('alerts', 'unique_alert', 'alerts_rule_id_index', ['rule_id']);
            $this->renameIndex('alert_device_map', 'alert_device_map_rule_id_device_id_uindex', 'alert_device_map_rule_id_device_id_unique', ['rule_id', 'device_id'], true);
            $this->renameIndex('alert_group_map', 'alert_group_map_rule_id_group_id_uindex', 'alert_group_map_rule_id_group_id_unique', ['rule_id', 'group_id'], true);
            $this->renameIndex('alert_log', 'device_id', 'alert_log_device_id_index', ['device_id']);
            $this->renameIndex('alert_log', 'rule_id', 'alert_log_rule_id_index', ['rule_id']);
            $this->renameIndex('alert_log', 'time_logged', 'alert_log_time_logged_index', ['time_logged']);
            $this->renameIndex('alert_rules', 'name', 'alert_rules_name_unique', ['name'], true);
            $this->renameIndex('alert_schedulables', 'schedule_id', 'alert_schedulables_schedule_id_index', ['schedule_id']);
            $this->renameIndex('api_tokens', 'token_hash', 'api_tokens_token_hash_unique', ['token_hash'], true);
            $this->renameIndex('applications', 'unique_index', 'applications_device_id_app_type_unique', ['device_id', 'app_type'], true);
            $this->renameIndex('application_metrics', 'application_metrics_app_id_metric_uindex', 'application_metrics_app_id_metric_unique', ['app_id', 'metric'], true);
            $this->renameIndex('bgpPeers', 'device_id', 'bgppeers_device_id_context_name_index', ['device_id', 'context_name']);
            $this->renameIndex('bgpPeers_cbgp', 'unique_index', 'bgppeers_cbgp_device_id_bgppeeridentifier_afi_safi_unique', ['device_id', 'bgpPeerIdentifier', 'afi', 'safi'], true);
            $this->renameIndex('bgpPeers_cbgp', 'device_id', 'bgppeers_cbgp_device_id_bgppeeridentifier_context_name_index', ['device_id', 'bgpPeerIdentifier', 'context_name']);
            $this->renameIndex('bill_data', 'bill_id', 'bill_data_bill_id_index', ['bill_id']);
            $this->renameIndex('bill_history', 'unique_index', 'bill_history_bill_id_bill_datefrom_bill_dateto_unique', ['bill_id', 'bill_datefrom', 'bill_dateto'], true);
            $this->renameIndex('bill_history', 'bill_id', 'bill_history_bill_id_index', ['bill_id']);
            $this->renameIndex('cef_switching', 'device_id', 'cef_switching_device_id_entphysicalindex_afi_cef_index_unique', ['device_id', 'entPhysicalIndex', 'afi', 'cef_index'], true);
            $this->renameIndex('ciscoASA', 'device_id', 'ciscoasa_device_id_index', ['device_id']);
            $this->renameIndex('component', 'device', 'component_device_id_index', ['device_id']);
            $this->renameIndex('component', 'type', 'component_type_index', ['type']);
            $this->renameIndex('component_prefs', 'component', 'component_prefs_component_index', ['component']);
            $this->renameIndex('component_statuslog', 'device', 'component_statuslog_component_id_index', ['component_id']);
            $this->renameIndex('config', 'uniqueindex_configname', 'config_config_name_unique', ['config_name'], true);
            $this->renameIndex('customers', 'username', 'customers_username_unique', ['username'], true);
            $this->renameIndex('devices', 'hostname', 'devices_hostname_index', ['hostname']);
            $this->renameIndex('devices', 'last_poll_attempted', 'devices_last_poll_attempted_index', ['last_poll_attempted']);
            $this->renameIndex('devices', 'last_polled', 'devices_last_polled_index', ['last_polled']);
            $this->renameIndex('devices', 'os', 'devices_os_index', ['os']);
            $this->renameIndex('devices', 'status', 'devices_status_index', ['status']);
            $this->renameIndex('devices', 'sysName', 'devices_sysname_index', ['sysName']);
            $this->renameIndex('devices_attribs', 'device_id', 'devices_attribs_device_id_index', ['device_id']);
            $this->renameIndex('devices_perms', 'user_id', 'devices_perms_user_id_index', ['user_id']);
            $this->renameIndex('device_graphs', 'device_id', 'device_graphs_device_id_index', ['device_id']);
            $this->renameIndex('device_groups', 'name', 'device_groups_name_unique', ['name'], true);
            $this->renameIndex('device_perf', 'device_id', 'device_perf_device_id_index', ['device_id']);
            $this->renameIndex('device_relationships', 'device_relationship_child_device_id_fk', 'device_relationships_child_device_id_index', ['child_device_id']);
            $this->renameIndex('entPhysical', 'device_id', 'entphysical_device_id_index', ['device_id']);
            $this->renameIndex('eventlog', 'datetime', 'eventlog_datetime_index', ['datetime']);
            $this->renameIndex('eventlog', 'device_id', 'eventlog_device_id_index', ['device_id']);
            if (! $this->indexExists('entityState', 'entitystate_device_id_index')) {
                Schema::table('entityState', function (Blueprint $table) {
                    // must be dropped and re-added because of case insensitivity
                    if ($this->indexExists('entityState', 'entityState_device_id_index')) {
                        $table->dropIndex('entityState_device_id_index');
                    }
                    $table->index('device_id');
                });
            }
            $this->renameIndex('graph_types', 'graph_section', 'graph_types_graph_section_index', ['graph_section']);
            $this->renameIndex('graph_types', 'graph_subtype', 'graph_types_graph_subtype_index', ['graph_subtype']);
            $this->renameIndex('graph_types', 'graph_type', 'graph_types_graph_type_index', ['graph_type']);
            $this->renameIndex('hrDevice', 'device_id', 'hrdevice_device_id_index', ['device_id']);
            $this->renameIndex('ipsec_tunnels', 'unique_index', 'ipsec_tunnels_device_id_peer_addr_unique', ['device_id', 'peer_addr'], true);
            $this->renameIndex('ipv4_addresses', 'interface_id', 'ipv4_addresses_port_id_index', ['port_id']);
            $this->renameIndex('ipv4_mac', 'mac_address', 'ipv4_mac_mac_address_index', ['mac_address']);
            $this->renameIndex('ipv4_mac', 'port_id', 'ipv4_mac_port_id_index', ['port_id']);
            $this->renameIndex('ipv6_addresses', 'interface_id', 'ipv6_addresses_port_id_index', ['port_id']);
            $this->renameIndex('juniAtmVp', 'port_id', 'juniatmvp_port_id_index', ['port_id']);
            $this->renameIndex('links', 'src_if', 'links_local_port_id_index', ['local_port_id']);
            $this->renameIndex('links', 'dst_if', 'links_remote_port_id_index', ['remote_port_id']);
            $this->renameIndex('loadbalancer_vservers', 'device_id', 'loadbalancer_vservers_device_id_index', ['device_id']);
            $this->renameIndex('locations', 'locations_location_uindex', 'locations_location_unique', ['location'], true);
            $this->renameIndex('mac_accounting', 'interface_id', 'mac_accounting_port_id_index', ['port_id']);
            if ($this->indexExists('mac_accounting', 'interface_id_2')) {
                DB::statement('ALTER TABLE mac_accounting DROP INDEX interface_id_2;');
            }
            $this->renameIndex('mefinfo', 'device_id', 'mefinfo_device_id_index', ['device_id']);
            $this->renameIndex('mefinfo', 'mefID', 'mefinfo_mefid_index', ['mefID']);
            $this->renameIndex('mempools', 'device_id', 'mempools_device_id_index', ['device_id']);
            $this->renameIndex('mpls_lsps', 'device_id', 'mpls_lsps_device_id_index', ['device_id']);
            $this->renameIndex('mpls_lsp_paths', 'device_id', 'mpls_lsp_paths_device_id_index', ['device_id']);
            $this->renameIndex('mpls_saps', 'device_id', 'mpls_saps_device_id_index', ['device_id']);
            $this->renameIndex('mpls_sdps', 'device_id', 'mpls_sdps_device_id_index', ['device_id']);
            $this->renameIndex('mpls_sdp_binds', 'device_id', 'mpls_sdp_binds_device_id_index', ['device_id']);
            $this->renameIndex('mpls_services', 'device_id', 'mpls_services_device_id_index', ['device_id']);
            $this->renameIndex('mpls_tunnel_ar_hops', 'device_id', 'mpls_tunnel_ar_hops_device_id_index', ['device_id']);
            $this->renameIndex('mpls_tunnel_c_hops', 'device_id', 'mpls_tunnel_c_hops_device_id_index', ['device_id']);
            $this->renameIndex('munin_plugins', 'device_id', 'munin_plugins_device_id_index', ['device_id']);
            $this->renameIndex('munin_plugins', '`UNIQUE`', 'munin_plugins_device_id_mplug_type_unique', ['device_id', 'mplug_type'], true);
            $this->renameIndex('munin_plugins_ds', 'splug_id', 'munin_plugins_ds_mplug_id_ds_name_unique', ['mplug_id', 'ds_name'], true);
            $this->renameIndex('notifications', 'checksum', 'notifications_checksum_unique', ['checksum'], true);
            $this->renameIndex('ospf_areas', 'device_area', 'ospf_areas_device_id_ospfareaid_context_name_unique', ['device_id', 'ospfAreaId', 'context_name'], true);
            $this->renameIndex('ospf_instances', 'device_id', 'ospf_instances_device_id_ospf_instance_id_context_name_unique', ['device_id', 'ospf_instance_id', 'context_name'], true);
            $this->renameIndex('ospf_nbrs', 'device_id', 'ospf_nbrs_device_id_ospf_nbr_id_context_name_unique', ['device_id', 'ospf_nbr_id', 'context_name'], true);
            $this->renameIndex('ospf_ports', 'device_id', 'ospf_ports_device_id_ospf_port_id_context_name_unique', ['device_id', 'ospf_port_id', 'context_name'], true);
            $this->renameIndex('packages', 'device_id', 'packages_device_id_index', ['device_id']);
            $this->renameIndex('packages', 'unique_key', 'packages_device_id_name_manager_arch_version_build_unique', ['device_id, name, manager, arch, version, build'], true);
            $this->renameIndex('perf_times', 'type', 'perf_times_type_index', ['type']);
            $this->renameIndex('pollers', 'poller_name', 'pollers_poller_name_unique', ['poller_name'], true);
            $this->renameIndex('poller_cluster_stats', 'parent_poller_poller_type', 'poller_cluster_stats_parent_poller_poller_type_unique', ['parent_poller', 'poller_type'], true);
            $this->renameIndex('ports', 'device_ifIndex', 'ports_device_id_ifindex_unique', ['device_id', 'ifIndex'], true);
            $this->renameIndex('ports', 'if_2', 'ports_ifdescr_index', ['ifDescr']);
            $this->renameIndex('ports_adsl', 'interface_id', 'ports_adsl_port_id_unique', ['port_id'], true);
            $this->renameIndex('ports_fdb', 'mac_address', 'ports_fdb_mac_address_index', ['mac_address']);
            $this->renameIndex('ports_stack', 'device_id', 'ports_stack_device_id_port_id_high_port_id_low_unique', ['device_id', 'port_id_high', 'port_id_low'], true);
            $this->renameIndex('ports_stp', 'device_id', 'ports_stp_device_id_port_id_unique', ['device_id', 'port_id'], true);
            $this->renameIndex('ports_vlans', '`unique`', 'ports_vlans_device_id_port_id_vlan_unique', ['device_id', 'port_id', 'vlan'], true);
            $this->renameIndex('processes', 'device_id', 'processes_device_id_index', ['device_id']);
            $this->renameIndex('processors', 'device_id', 'processors_device_id_index', ['device_id']);
            $this->renameIndex('proxmox', 'cluster_vm', 'proxmox_cluster_vmid_unique', ['cluster', 'vmid'], true);
            $this->renameIndex('proxmox_ports', 'vm_port', 'proxmox_ports_vm_id_port_unique', ['vm_id', 'port'], true);
            $this->renameIndex('sensors', 'sensor_host', 'sensors_device_id_index', ['device_id']);
            $this->renameIndex('sensors', 'sensor_class', 'sensors_sensor_class_index', ['sensor_class']);
            $this->renameIndex('sensors', 'sensor_type', 'sensors_sensor_type_index', ['sensor_type']);
            $this->renameIndex('sensors_to_state_indexes', 'sensor_id_state_index_id', 'sensors_to_state_indexes_sensor_id_state_index_id_unique', ['sensor_id', 'state_index_id'], true);
            $this->renameIndex('sensors_to_state_indexes', 'state_index_id', 'sensors_to_state_indexes_state_index_id_index', ['state_index_id']);
            $this->renameIndex('services', 'service_host', 'services_device_id_index', ['device_id']);
            $this->renameIndex('session', 'session_value', 'session_session_value_unique', ['session_value'], true);
            $this->renameIndex('slas', 'device_id', 'slas_device_id_index', ['device_id']);
            $this->renameIndex('slas', 'unique_key', 'slas_device_id_sla_nr_unique', ['device_id', 'sla_nr'], true);
            $this->renameIndex('state_indexes', 'state_name', 'state_indexes_state_name_unique', ['state_name'], true);
            $this->renameIndex('state_translations', 'state_index_id_value', 'state_translations_state_index_id_state_value_unique', ['state_index_id', 'state_value'], true);
            $this->renameIndex('storage', 'device_id', 'storage_device_id_index', ['device_id']);
            $this->renameIndex('storage', 'index_unique', 'storage_device_id_storage_mib_storage_index_unique', ['device_id', 'storage_mib', 'storage_index'], true);
            $this->renameIndex('stp', 'stp_host', 'stp_device_id_index', ['device_id']);
            $this->renameIndex('syslog', 'device_id', 'syslog_device_id_index', ['device_id']);
            $this->renameIndex('syslog', 'program', 'syslog_program_index', ['program']);
            $this->renameIndex('syslog', 'datetime', 'syslog_timestamp_index', ['timestamp']);
            $this->renameIndex('syslog', '`device_id-timestamp`', 'syslog_device_id_timestamp_index', ['device_id', 'timestamp']);
            $this->renameIndex('syslog', 'priority_level', 'syslog_priority_level_index', ['priority', 'level']);
            $this->renameIndex('tnmsneinfo', 'device_id', 'tnmsneinfo_device_id_index', ['device_id']);
            $this->renameIndex('tnmsneinfo', 'neID', 'tnmsneinfo_neid_index', ['neID']);
            $this->renameIndex('toner', 'device_id', 'toner_device_id_index', ['device_id']);
            $this->renameIndex('ucd_diskio', 'device_id', 'ucd_diskio_device_id_index', ['device_id']);
            $this->renameIndex('users', 'username', 'users_auth_type_username_unique', ['auth_type', 'username'], true);
            $this->renameIndex('vminfo', 'device_id', 'vminfo_device_id_index', ['device_id']);
            $this->renameIndex('vminfo', 'vmwVmVMID', 'vminfo_vmwvmvmid_index', ['vmwVmVMID']);
            $this->renameIndex('vrfs', 'device_id', 'vrfs_device_id_index', ['device_id']);
            $this->renameIndex('vrf_lite_cisco', 'context', 'vrf_lite_cisco_context_name_index', ['context_name']);
            $this->renameIndex('vrf_lite_cisco', 'mix', 'vrf_lite_cisco_device_id_context_name_vrf_name_index', ['device_id', 'context_name', 'vrf_name']);
            $this->renameIndex('vrf_lite_cisco', 'device', 'vrf_lite_cisco_device_id_index', ['device_id']);
            $this->renameIndex('vrf_lite_cisco', 'vrf', 'vrf_lite_cisco_vrf_name_index', ['vrf_name']);
            $this->renameIndex('widgets', 'widget', 'widgets_widget_unique', ['widget'], true);
            $this->renameIndex('wireless_sensors', 'sensor_host', 'wireless_sensors_device_id_index', ['device_id']);
            $this->renameIndex('wireless_sensors', 'sensor_class', 'wireless_sensors_sensor_class_index', ['sensor_class']);
            $this->renameIndex('wireless_sensors', 'sensor_type', 'wireless_sensors_sensor_type_index', ['sensor_type']);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }

    private function indexExists($table, $name)
    {
        $indexes = Schema::getConnection()->getDoctrineSchemaManager()->listTableIndexes($table);

        return array_key_exists($name, $indexes);
    }

    private function renameIndex($table, $old, $new, array $fields, $unique = false)
    {
        // skip pre-existing new index
        if (! $this->indexExists($table, $new)) {
            $query = "ALTER TABLE $table ";

            // don't try to remove non-existent old index
            if ($this->indexExists($table, $old)) {
                $query .= "DROP INDEX $old, ";
            }

            $query .= 'ADD ';
            if ($unique) {
                $query .= 'UNIQUE ';
            }
            $field_list = implode(', ', $fields);
            $query .= "INDEX $new($field_list);";

            DB::statement($query);
        }
    }
}
