<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
        if (\LibreNMS\DB\Eloquent::getDriver() == 'mysql' && !$this->indexExists('wireless_sensors', 'wireless_sensors_sensor_type_index')) {
            DB::statement('ALTER TABLE access_points DROP INDEX deleted, ADD INDEX access_points_deleted_index(deleted);');
            DB::statement('ALTER TABLE alerts DROP INDEX device_id, ADD INDEX alerts_device_id_index(device_id);');
            DB::statement('ALTER TABLE alerts DROP INDEX rule_id, ADD UNIQUE INDEX alerts_device_id_rule_id_unique(device_id, rule_id);');
            DB::statement('ALTER TABLE alerts DROP INDEX unique_alert, ADD INDEX alerts_rule_id_index(rule_id);');
            DB::statement('ALTER TABLE alert_device_map DROP INDEX alert_device_map_rule_id_device_id_uindex, ADD UNIQUE INDEX alert_device_map_rule_id_device_id_unique(rule_id, device_id);');
            DB::statement('ALTER TABLE alert_group_map DROP INDEX alert_group_map_rule_id_group_id_uindex, ADD UNIQUE INDEX alert_group_map_rule_id_group_id_unique(rule_id, group_id);');
            DB::statement('ALTER TABLE alert_log DROP INDEX device_id, ADD INDEX alert_log_device_id_index(device_id);');
            DB::statement('ALTER TABLE alert_log DROP INDEX rule_id, ADD INDEX alert_log_rule_id_index(rule_id);');
            DB::statement('ALTER TABLE alert_log DROP INDEX time_logged, ADD INDEX alert_log_time_logged_index(time_logged);');
            DB::statement('ALTER TABLE alert_rules DROP INDEX name, ADD UNIQUE INDEX alert_rules_name_unique(name);');
            DB::statement('ALTER TABLE alert_schedulables DROP INDEX schedule_id, ADD INDEX alert_schedulables_schedule_id_index(schedule_id);');
            DB::statement('ALTER TABLE api_tokens DROP INDEX token_hash, ADD UNIQUE INDEX api_tokens_token_hash_unique(token_hash);');
            DB::statement('ALTER TABLE applications DROP INDEX unique_index, ADD UNIQUE INDEX applications_device_id_app_type_unique(device_id, app_type);');
            DB::statement('ALTER TABLE application_metrics DROP INDEX application_metrics_app_id_metric_uindex, ADD UNIQUE INDEX application_metrics_app_id_metric_unique(app_id, metric);');
            DB::statement('ALTER TABLE bgpPeers DROP INDEX device_id, ADD INDEX bgppeers_device_id_context_name_index(device_id, context_name);');
            DB::statement('ALTER TABLE bgpPeers_cbgp DROP INDEX unique_index, ADD UNIQUE INDEX bgppeers_cbgp_device_id_bgppeeridentifier_afi_safi_unique(device_id, bgpPeerIdentifier, afi, safi);');
            DB::statement('ALTER TABLE bgpPeers_cbgp DROP INDEX device_id, ADD INDEX bgppeers_cbgp_device_id_bgppeeridentifier_context_name_index(device_id, bgpPeerIdentifier, context_name);');
            DB::statement('ALTER TABLE bill_data DROP INDEX bill_id, ADD INDEX bill_data_bill_id_index(bill_id);');
            DB::statement('ALTER TABLE bill_history DROP INDEX unique_index, ADD UNIQUE INDEX bill_history_bill_id_bill_datefrom_bill_dateto_unique(bill_id, bill_datefrom, bill_dateto);');
            DB::statement('ALTER TABLE bill_history DROP INDEX bill_id, ADD INDEX bill_history_bill_id_index(bill_id);');
            DB::statement('ALTER TABLE cef_switching DROP INDEX device_id, ADD UNIQUE INDEX cef_switching_device_id_entphysicalindex_afi_cef_index_unique(device_id, entPhysicalIndex, afi, cef_index);');
            DB::statement('ALTER TABLE ciscoASA DROP INDEX device_id, ADD INDEX ciscoasa_device_id_index(device_id);');
            DB::statement('ALTER TABLE component DROP INDEX device, ADD INDEX component_device_id_index(device_id);');
            DB::statement('ALTER TABLE component DROP INDEX type, ADD INDEX component_type_index(type);');
            DB::statement('ALTER TABLE component_prefs DROP INDEX component, ADD INDEX component_prefs_component_index(component);');
            DB::statement('ALTER TABLE component_statuslog DROP INDEX device, ADD INDEX component_statuslog_component_id_index(component_id);');
            DB::statement('ALTER TABLE config DROP INDEX uniqueindex_configname, ADD UNIQUE INDEX config_config_name_unique(config_name);');
            DB::statement('ALTER TABLE customers DROP INDEX username, ADD UNIQUE INDEX customers_username_unique(username);');
            DB::statement('ALTER TABLE devices DROP INDEX hostname, ADD INDEX devices_hostname_index(hostname);');
            DB::statement('ALTER TABLE devices DROP INDEX last_poll_attempted, ADD INDEX devices_last_poll_attempted_index(last_poll_attempted);');
            DB::statement('ALTER TABLE devices DROP INDEX last_polled, ADD INDEX devices_last_polled_index(last_polled);');
            DB::statement('ALTER TABLE devices DROP INDEX os, ADD INDEX devices_os_index(os);');
            DB::statement('ALTER TABLE devices DROP INDEX status, ADD INDEX devices_status_index(status);');
            DB::statement('ALTER TABLE devices DROP INDEX sysName, ADD INDEX devices_sysname_index(sysName);');
            DB::statement('ALTER TABLE devices_attribs DROP INDEX device_id, ADD INDEX devices_attribs_device_id_index(device_id);');
            DB::statement('ALTER TABLE devices_perms DROP INDEX user_id, ADD INDEX devices_perms_user_id_index(user_id);');
            DB::statement('ALTER TABLE device_graphs DROP INDEX device_id, ADD INDEX device_graphs_device_id_index(device_id);');
            DB::statement('ALTER TABLE device_groups DROP INDEX name, ADD UNIQUE INDEX device_groups_name_unique(name);');
            DB::statement('ALTER TABLE device_perf DROP INDEX device_id, ADD INDEX device_perf_device_id_index(device_id);');
            DB::statement('ALTER TABLE device_relationships DROP INDEX device_relationship_child_device_id_fk, ADD INDEX device_relationships_child_device_id_index(child_device_id);');
            DB::statement('ALTER TABLE entPhysical DROP INDEX device_id, ADD INDEX entphysical_device_id_index(device_id);');
            DB::statement('ALTER TABLE eventlog DROP INDEX datetime, ADD INDEX eventlog_datetime_index(datetime);');
            DB::statement('ALTER TABLE eventlog DROP INDEX device_id, ADD INDEX eventlog_device_id_index(device_id);');
            Schema::table('entityState', function (Blueprint $table) {
                // must be dropped and re-added because of case insensitivity
                $table->dropIndex('entityState_device_id_index');
                $table->index('device_id');
            });
            DB::statement('ALTER TABLE graph_types DROP INDEX graph_section, ADD INDEX graph_types_graph_section_index(graph_section);');
            DB::statement('ALTER TABLE graph_types DROP INDEX graph_subtype, ADD INDEX graph_types_graph_subtype_index(graph_subtype);');
            DB::statement('ALTER TABLE graph_types DROP INDEX graph_type, ADD INDEX graph_types_graph_type_index(graph_type);');
            DB::statement('ALTER TABLE hrDevice DROP INDEX device_id, ADD INDEX hrdevice_device_id_index(device_id);');
            DB::statement('ALTER TABLE ipsec_tunnels DROP INDEX unique_index, ADD UNIQUE INDEX ipsec_tunnels_device_id_peer_addr_unique(device_id, peer_addr);');
            DB::statement('ALTER TABLE ipv4_addresses DROP INDEX interface_id, ADD INDEX ipv4_addresses_port_id_index(port_id);');
            DB::statement('ALTER TABLE ipv4_mac DROP INDEX mac_address, ADD INDEX ipv4_mac_mac_address_index(mac_address);');
            DB::statement('ALTER TABLE ipv4_mac DROP INDEX port_id, ADD INDEX ipv4_mac_port_id_index(port_id);');
            DB::statement('ALTER TABLE ipv6_addresses DROP INDEX interface_id, ADD INDEX ipv6_addresses_port_id_index(port_id);');
            DB::statement('ALTER TABLE juniAtmVp DROP INDEX port_id, ADD INDEX juniatmvp_port_id_index(port_id);');
            DB::statement('ALTER TABLE links DROP INDEX src_if, ADD INDEX links_local_port_id_index(local_port_id);');
            DB::statement('ALTER TABLE links DROP INDEX dst_if, ADD INDEX links_remote_port_id_index(remote_port_id);');
            DB::statement('ALTER TABLE loadbalancer_vservers DROP INDEX device_id, ADD INDEX loadbalancer_vservers_device_id_index(device_id);');
            DB::statement('ALTER TABLE locations DROP INDEX locations_location_uindex, ADD UNIQUE INDEX locations_location_unique(location);');
            DB::statement('ALTER TABLE mac_accounting DROP INDEX interface_id, ADD INDEX mac_accounting_port_id_index(port_id);');
            DB::statement('ALTER TABLE mac_accounting DROP INDEX interface_id_2;');
            DB::statement('ALTER TABLE mefinfo DROP INDEX device_id, ADD INDEX mefinfo_device_id_index(device_id);');
            DB::statement('ALTER TABLE mefinfo DROP INDEX mefID, ADD INDEX mefinfo_mefid_index(mefID);');
            DB::statement('ALTER TABLE mempools DROP INDEX device_id, ADD INDEX mempools_device_id_index(device_id);');
            DB::statement('ALTER TABLE mpls_lsps DROP INDEX device_id, ADD INDEX mpls_lsps_device_id_index(device_id);');
            DB::statement('ALTER TABLE mpls_lsp_paths DROP INDEX device_id, ADD INDEX mpls_lsp_paths_device_id_index(device_id);');
            DB::statement('ALTER TABLE mpls_saps DROP INDEX device_id, ADD INDEX mpls_saps_device_id_index(device_id);');
            DB::statement('ALTER TABLE mpls_sdps DROP INDEX device_id, ADD INDEX mpls_sdps_device_id_index(device_id);');
            DB::statement('ALTER TABLE mpls_sdp_binds DROP INDEX device_id, ADD INDEX mpls_sdp_binds_device_id_index(device_id);');
            DB::statement('ALTER TABLE mpls_services DROP INDEX device_id, ADD INDEX mpls_services_device_id_index(device_id);');
            DB::statement('ALTER TABLE mpls_tunnel_ar_hops DROP INDEX device_id, ADD INDEX mpls_tunnel_ar_hops_device_id_index(device_id);');
            DB::statement('ALTER TABLE mpls_tunnel_c_hops DROP INDEX device_id, ADD INDEX mpls_tunnel_c_hops_device_id_index(device_id);');
            DB::statement('ALTER TABLE munin_plugins DROP INDEX device_id, ADD INDEX munin_plugins_device_id_index(device_id);');
            DB::statement('ALTER TABLE munin_plugins DROP INDEX `UNIQUE`, ADD UNIQUE INDEX munin_plugins_device_id_mplug_type_unique(device_id, mplug_type);');
            DB::statement('ALTER TABLE munin_plugins_ds DROP INDEX splug_id, ADD UNIQUE INDEX munin_plugins_ds_mplug_id_ds_name_unique(mplug_id, ds_name);');
            DB::statement('ALTER TABLE notifications DROP INDEX checksum, ADD UNIQUE INDEX notifications_checksum_unique(checksum);');
            DB::statement('ALTER TABLE ospf_areas DROP INDEX device_area, ADD UNIQUE INDEX ospf_areas_device_id_ospfareaid_context_name_unique(device_id, ospfAreaId, context_name);');
            DB::statement('ALTER TABLE ospf_instances DROP INDEX device_id, ADD UNIQUE INDEX ospf_instances_device_id_ospf_instance_id_context_name_unique(device_id, ospf_instance_id, context_name);');
            DB::statement('ALTER TABLE ospf_nbrs DROP INDEX device_id, ADD UNIQUE INDEX ospf_nbrs_device_id_ospf_nbr_id_context_name_unique(device_id, ospf_nbr_id, context_name);');
            DB::statement('ALTER TABLE ospf_ports DROP INDEX device_id, ADD UNIQUE INDEX ospf_ports_device_id_ospf_port_id_context_name_unique(device_id, ospf_port_id, context_name);');
            DB::statement('ALTER TABLE packages DROP INDEX device_id, ADD INDEX packages_device_id_index(device_id);');
            DB::statement('ALTER TABLE packages DROP INDEX unique_key, ADD UNIQUE INDEX packages_device_id_name_manager_arch_version_build_unique(device_id, name, manager, arch, version, build);');
            DB::statement('ALTER TABLE perf_times DROP INDEX type, ADD INDEX perf_times_type_index(type);');
            DB::statement('ALTER TABLE pollers DROP INDEX poller_name, ADD UNIQUE INDEX pollers_poller_name_unique(poller_name);');
            DB::statement('ALTER TABLE poller_cluster_stats DROP INDEX parent_poller_poller_type, ADD UNIQUE INDEX poller_cluster_stats_parent_poller_poller_type_unique(parent_poller, poller_type);');
            DB::statement('ALTER TABLE ports DROP INDEX device_ifIndex, ADD UNIQUE INDEX ports_device_id_ifindex_unique(device_id, ifIndex);');
            DB::statement('ALTER TABLE ports DROP INDEX if_2, ADD INDEX ports_ifdescr_index(ifDescr);');
            DB::statement('ALTER TABLE ports_adsl DROP INDEX interface_id, ADD UNIQUE INDEX ports_adsl_port_id_unique(port_id);');
            DB::statement('ALTER TABLE ports_fdb DROP INDEX mac_address, ADD INDEX ports_fdb_mac_address_index(mac_address);');
            DB::statement('ALTER TABLE ports_stack DROP INDEX device_id, ADD UNIQUE INDEX ports_stack_device_id_port_id_high_port_id_low_unique(device_id, port_id_high, port_id_low);');
            DB::statement('ALTER TABLE ports_stp DROP INDEX device_id, ADD UNIQUE INDEX ports_stp_device_id_port_id_unique(device_id, port_id);');
            DB::statement('ALTER TABLE ports_vlans DROP INDEX `unique`, ADD UNIQUE INDEX ports_vlans_device_id_port_id_vlan_unique(device_id, port_id, vlan);');
            DB::statement('ALTER TABLE processes DROP INDEX device_id, ADD INDEX processes_device_id_index(device_id);');
            DB::statement('ALTER TABLE processors DROP INDEX device_id, ADD INDEX processors_device_id_index(device_id);');
            DB::statement('ALTER TABLE proxmox DROP INDEX cluster_vm, ADD UNIQUE INDEX proxmox_cluster_vmid_unique(cluster, vmid);');
            DB::statement('ALTER TABLE proxmox_ports DROP INDEX vm_port, ADD UNIQUE INDEX proxmox_ports_vm_id_port_unique(vm_id, port);');
            DB::statement('ALTER TABLE sensors DROP INDEX sensor_host, ADD INDEX sensors_device_id_index(device_id);');
            DB::statement('ALTER TABLE sensors DROP INDEX sensor_class, ADD INDEX sensors_sensor_class_index(sensor_class);');
            DB::statement('ALTER TABLE sensors DROP INDEX sensor_type, ADD INDEX sensors_sensor_type_index(sensor_type);');
            DB::statement('ALTER TABLE sensors_to_state_indexes DROP INDEX sensor_id_state_index_id, ADD UNIQUE INDEX sensors_to_state_indexes_sensor_id_state_index_id_unique(sensor_id, state_index_id);');
            DB::statement('ALTER TABLE sensors_to_state_indexes DROP INDEX state_index_id, ADD INDEX sensors_to_state_indexes_state_index_id_index(state_index_id);');
            DB::statement('ALTER TABLE services DROP INDEX service_host, ADD INDEX services_device_id_index(device_id);');
            DB::statement('ALTER TABLE session DROP INDEX session_value, ADD UNIQUE INDEX session_session_value_unique(session_value);');
            DB::statement('ALTER TABLE slas DROP INDEX device_id, ADD INDEX slas_device_id_index(device_id);');
            DB::statement('ALTER TABLE slas DROP INDEX unique_key, ADD UNIQUE INDEX slas_device_id_sla_nr_unique(device_id, sla_nr);');
            DB::statement('ALTER TABLE state_indexes DROP INDEX state_name, ADD UNIQUE INDEX state_indexes_state_name_unique(state_name);');
            DB::statement('ALTER TABLE state_translations DROP INDEX state_index_id_value, ADD UNIQUE INDEX state_translations_state_index_id_state_value_unique(state_index_id, state_value);');
            DB::statement('ALTER TABLE storage DROP INDEX device_id, ADD INDEX storage_device_id_index(device_id);');
            DB::statement('ALTER TABLE storage DROP INDEX index_unique, ADD UNIQUE INDEX storage_device_id_storage_mib_storage_index_unique(device_id, storage_mib, storage_index);');
            DB::statement('ALTER TABLE stp DROP INDEX stp_host, ADD INDEX stp_device_id_index(device_id);');
            DB::statement('ALTER TABLE syslog DROP INDEX device_id, ADD INDEX syslog_device_id_index(device_id);');
            DB::statement('ALTER TABLE syslog DROP INDEX program, ADD INDEX syslog_program_index(program);');
            DB::statement('ALTER TABLE syslog DROP INDEX datetime, ADD INDEX syslog_timestamp_index(timestamp);');
            DB::statement('ALTER TABLE syslog DROP INDEX `device_id-timestamp`, ADD INDEX syslog_device_id_timestamp_index(device_id, timestamp);');
            DB::statement('ALTER TABLE syslog DROP INDEX priority_level, ADD INDEX syslog_priority_level_index(priority, level);');
            DB::statement('ALTER TABLE tnmsneinfo DROP INDEX device_id, ADD INDEX tnmsneinfo_device_id_index(device_id);');
            DB::statement('ALTER TABLE tnmsneinfo DROP INDEX neID, ADD INDEX tnmsneinfo_neid_index(neID);');
            DB::statement('ALTER TABLE toner DROP INDEX device_id, ADD INDEX toner_device_id_index(device_id);');
            DB::statement('ALTER TABLE ucd_diskio DROP INDEX device_id, ADD INDEX ucd_diskio_device_id_index(device_id);');
            DB::statement('ALTER TABLE users DROP INDEX username, ADD UNIQUE INDEX users_auth_type_username_unique(auth_type, username);');
            DB::statement('ALTER TABLE vminfo DROP INDEX device_id, ADD INDEX vminfo_device_id_index(device_id);');
            DB::statement('ALTER TABLE vminfo DROP INDEX vmwVmVMID, ADD INDEX vminfo_vmwvmvmid_index(vmwVmVMID);');
            DB::statement('ALTER TABLE vrfs DROP INDEX device_id, ADD INDEX vrfs_device_id_index(device_id);');
            DB::statement('ALTER TABLE vrf_lite_cisco DROP INDEX context, ADD INDEX vrf_lite_cisco_context_name_index(context_name);');
            DB::statement('ALTER TABLE vrf_lite_cisco DROP INDEX mix, ADD INDEX vrf_lite_cisco_device_id_context_name_vrf_name_index(device_id, context_name, vrf_name);');
            DB::statement('ALTER TABLE vrf_lite_cisco DROP INDEX device, ADD INDEX vrf_lite_cisco_device_id_index(device_id);');
            DB::statement('ALTER TABLE vrf_lite_cisco DROP INDEX vrf, ADD INDEX vrf_lite_cisco_vrf_name_index(vrf_name);');
            DB::statement('ALTER TABLE widgets DROP INDEX widget, ADD UNIQUE INDEX widgets_widget_unique(widget);');
            DB::statement('ALTER TABLE wireless_sensors DROP INDEX sensor_host, ADD INDEX wireless_sensors_device_id_index(device_id);');
            DB::statement('ALTER TABLE wireless_sensors DROP INDEX sensor_class, ADD INDEX wireless_sensors_sensor_class_index(sensor_class);');
            DB::statement('ALTER TABLE wireless_sensors DROP INDEX sensor_type, ADD INDEX wireless_sensors_sensor_type_index(sensor_type);');
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
}
