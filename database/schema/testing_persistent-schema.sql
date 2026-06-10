CREATE TABLE IF NOT EXISTS "migrations"(
  "id" integer primary key autoincrement not null,
  "migration" varchar not null,
  "batch" integer not null
);
CREATE TABLE IF NOT EXISTS "alert_device_map"(
  "id" integer primary key autoincrement not null,
  "rule_id" integer not null,
  "device_id" integer not null
);
CREATE UNIQUE INDEX "alert_device_map_rule_id_device_id_unique" on "alert_device_map"(
  "rule_id",
  "device_id"
);
CREATE TABLE IF NOT EXISTS "alert_group_map"(
  "id" integer primary key autoincrement not null,
  "rule_id" integer not null,
  "group_id" integer not null
);
CREATE UNIQUE INDEX "alert_group_map_rule_id_group_id_unique" on "alert_group_map"(
  "rule_id",
  "group_id"
);
CREATE TABLE IF NOT EXISTS "alert_log"(
  "id" integer primary key autoincrement not null,
  "rule_id" integer not null,
  "device_id" integer not null,
  "state" integer not null,
  "details" blob,
  "time_logged" datetime not null default CURRENT_TIMESTAMP
);
CREATE INDEX "alert_log_time_logged_index" on "alert_log"("time_logged");
CREATE TABLE IF NOT EXISTS "alert_rules"(
  "id" integer primary key autoincrement not null,
  "rule" text not null,
  "severity" varchar check("severity" in('ok', 'warning', 'critical')) not null,
  "extra" varchar not null,
  "disabled" tinyint(1) not null,
  "name" varchar not null,
  "query" text not null,
  "builder" text not null,
  "proc" varchar,
  "invert_map" tinyint(1) not null default '0',
  "notes" text
);
CREATE UNIQUE INDEX "alert_rules_name_unique" on "alert_rules"("name");
CREATE TABLE IF NOT EXISTS "alert_schedulables"(
  "item_id" integer primary key autoincrement not null,
  "schedule_id" integer not null,
  "alert_schedulable_id" integer not null,
  "alert_schedulable_type" varchar not null
);
CREATE INDEX "schedulable_morph_index" on "alert_schedulables"(
  "alert_schedulable_type",
  "alert_schedulable_id"
);
CREATE INDEX "alert_schedulables_schedule_id_index" on "alert_schedulables"(
  "schedule_id"
);
CREATE TABLE IF NOT EXISTS "alert_schedule"(
  "schedule_id" integer primary key autoincrement not null,
  "recurring" tinyint(1) not null default '0',
  "start" datetime not null default '1970-01-02 00:00:01',
  "end" datetime not null default '1970-01-02 00:00:01',
  "recurring_day" varchar,
  "title" varchar not null,
  "notes" text not null
);
CREATE TABLE IF NOT EXISTS "alert_template_map"(
  "id" integer primary key autoincrement not null,
  "alert_templates_id" integer not null,
  "alert_rule_id" integer not null
);
CREATE INDEX "alert_templates_id" on "alert_template_map"(
  "alert_templates_id",
  "alert_rule_id"
);
CREATE TABLE IF NOT EXISTS "alert_templates"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "template" text not null,
  "title" varchar,
  "title_rec" varchar
);
CREATE TABLE IF NOT EXISTS "alert_transport_groups"(
  "transport_group_id" integer primary key autoincrement not null,
  "transport_group_name" varchar not null
);
CREATE TABLE IF NOT EXISTS "alert_transport_map"(
  "id" integer primary key autoincrement not null,
  "rule_id" integer not null,
  "transport_or_group_id" integer not null,
  "target_type" varchar not null
);
CREATE TABLE IF NOT EXISTS "alert_transports"(
  "transport_id" integer primary key autoincrement not null,
  "transport_name" varchar not null,
  "transport_type" varchar not null default 'mail',
  "is_default" tinyint(1) not null default '0',
  "transport_config" text
);
CREATE TABLE IF NOT EXISTS "alerts"(
  "id" integer primary key autoincrement not null,
  "device_id" integer not null,
  "rule_id" integer not null,
  "state" integer not null,
  "alerted" integer not null,
  "open" integer not null,
  "note" text,
  "timestamp" datetime not null default CURRENT_TIMESTAMP,
  "info" text not null
);
CREATE UNIQUE INDEX "alerts_device_id_rule_id_unique" on "alerts"(
  "device_id",
  "rule_id"
);
CREATE INDEX "alerts_device_id_index" on "alerts"("device_id");
CREATE INDEX "alerts_rule_id_index" on "alerts"("rule_id");
CREATE TABLE IF NOT EXISTS "api_tokens"(
  "id" integer primary key autoincrement not null,
  "user_id" integer not null,
  "token_hash" varchar,
  "description" varchar not null,
  "disabled" tinyint(1) not null default '0'
);
CREATE UNIQUE INDEX "api_tokens_token_hash_unique" on "api_tokens"(
  "token_hash"
);
CREATE TABLE IF NOT EXISTS "authlog"(
  "id" integer primary key autoincrement not null,
  "datetime" datetime not null default CURRENT_TIMESTAMP,
  "user" text not null,
  "address" text not null,
  "result" text not null
);
CREATE TABLE IF NOT EXISTS "bill_data"(
  "id" integer primary key autoincrement not null,
  "bill_id" integer not null,
  "timestamp" datetime not null,
  "period" integer not null,
  "delta" integer not null,
  "in_delta" integer not null,
  "out_delta" integer not null
);
CREATE INDEX "bill_data_bill_id_index" on "bill_data"("bill_id");
CREATE TABLE IF NOT EXISTS "bill_history"(
  "bill_hist_id" integer primary key autoincrement not null,
  "bill_id" integer not null,
  "updated" datetime not null default CURRENT_TIMESTAMP,
  "bill_datefrom" datetime not null,
  "bill_dateto" datetime not null,
  "bill_type" text not null,
  "bill_allowed" integer not null,
  "bill_used" integer not null,
  "bill_overuse" integer not null,
  "bill_percent" numeric not null,
  "rate_95th_in" integer not null,
  "rate_95th_out" integer not null,
  "rate_95th" integer not null,
  "dir_95th" varchar not null,
  "rate_average" integer not null,
  "rate_average_in" integer not null,
  "rate_average_out" integer not null,
  "traf_in" integer not null,
  "traf_out" integer not null,
  "traf_total" integer not null,
  "pdf" blob,
  "bill_peak_out" integer,
  "bill_peak_in" integer
);
CREATE UNIQUE INDEX "bill_history_bill_id_bill_datefrom_bill_dateto_unique" on "bill_history"(
  "bill_id",
  "bill_datefrom",
  "bill_dateto"
);
CREATE INDEX "bill_history_bill_id_index" on "bill_history"("bill_id");
CREATE TABLE IF NOT EXISTS "bill_perms"(
  "id" integer primary key autoincrement not null,
  "user_id" integer not null,
  "bill_id" integer not null
);
CREATE TABLE IF NOT EXISTS "bill_port_counters"(
  "port_id" integer not null,
  "timestamp" datetime not null default CURRENT_TIMESTAMP,
  "in_counter" integer,
  "in_delta" integer not null default '0',
  "out_counter" integer,
  "out_delta" integer not null default '0',
  "bill_id" integer not null,
  primary key("port_id", "bill_id")
);
CREATE TABLE IF NOT EXISTS "bill_ports"(
  "id" integer primary key autoincrement not null,
  "bill_id" integer not null,
  "port_id" integer not null,
  "bill_port_autoadded" tinyint(1) not null default '0'
);
CREATE TABLE IF NOT EXISTS "bills"(
  "bill_id" integer primary key autoincrement not null,
  "bill_name" text not null,
  "bill_type" text not null,
  "bill_cdr" integer,
  "bill_day" integer not null default '1',
  "bill_quota" integer,
  "rate_95th_in" integer not null,
  "rate_95th_out" integer not null,
  "rate_95th" integer not null,
  "dir_95th" varchar not null,
  "total_data" integer not null,
  "total_data_in" integer not null,
  "total_data_out" integer not null,
  "rate_average_in" integer not null,
  "rate_average_out" integer not null,
  "rate_average" integer not null,
  "bill_last_calc" datetime not null,
  "bill_custid" varchar not null,
  "bill_ref" varchar not null,
  "bill_notes" varchar not null,
  "bill_autoadded" tinyint(1) not null
);
CREATE TABLE IF NOT EXISTS "callback"(
  "callback_id" integer primary key autoincrement not null,
  "name" varchar not null,
  "value" varchar not null
);
CREATE TABLE IF NOT EXISTS "cef_switching"(
  "cef_switching_id" integer primary key autoincrement not null,
  "device_id" integer not null,
  "entPhysicalIndex" integer not null,
  "afi" varchar not null,
  "cef_index" integer not null,
  "cef_path" varchar not null,
  "drop" integer not null,
  "punt" integer not null,
  "punt2host" integer not null,
  "drop_prev" integer not null,
  "punt_prev" integer not null,
  "punt2host_prev" integer not null,
  "updated" integer not null,
  "updated_prev" integer not null
);
CREATE UNIQUE INDEX "cef_switching_device_id_entphysicalindex_afi_cef_index_unique" on "cef_switching"(
  "device_id",
  "entPhysicalIndex",
  "afi",
  "cef_index"
);
CREATE TABLE IF NOT EXISTS "component"(
  "id" integer primary key autoincrement not null,
  "device_id" integer not null,
  "type" varchar not null,
  "label" varchar,
  "status" tinyint(1) not null default '0',
  "disabled" tinyint(1) not null default '0',
  "ignore" tinyint(1) not null default '0',
  "error" varchar
);
CREATE INDEX "component_device_id_index" on "component"("device_id");
CREATE INDEX "component_type_index" on "component"("type");
CREATE TABLE IF NOT EXISTS "customers"(
  "customer_id" integer primary key autoincrement not null,
  "username" varchar not null,
  "password" varchar not null,
  "string" varchar not null,
  "level" integer not null default '0'
);
CREATE UNIQUE INDEX "customers_username_unique" on "customers"("username");
CREATE TABLE IF NOT EXISTS "dashboards"(
  "dashboard_id" integer primary key autoincrement not null,
  "user_id" integer not null default '0',
  "dashboard_name" varchar not null,
  "access" tinyint(1) not null default '0'
);
CREATE TABLE IF NOT EXISTS "device_graphs"(
  "id" integer primary key autoincrement not null,
  "device_id" integer not null,
  "graph" varchar
);
CREATE INDEX "device_graphs_device_id_index" on "device_graphs"("device_id");
CREATE TABLE IF NOT EXISTS "devices_perms"(
  "id" integer primary key autoincrement not null,
  "user_id" integer not null,
  "device_id" integer not null
);
CREATE INDEX "devices_perms_user_id_index" on "devices_perms"("user_id");
CREATE TABLE IF NOT EXISTS "entPhysical_state"(
  "id" integer primary key autoincrement not null,
  "device_id" integer not null,
  "entPhysicalIndex" varchar not null,
  "subindex" varchar,
  "group" varchar not null,
  "key" varchar not null,
  "value" varchar not null
);
CREATE INDEX "device_id_index" on "entPhysical_state"(
  "device_id",
  "entPhysicalIndex"
);
CREATE TABLE IF NOT EXISTS "entityState"(
  "entity_state_id" integer primary key autoincrement not null,
  "device_id" integer,
  "entPhysical_id" integer,
  "entStateLastChanged" datetime,
  "entStateAdmin" integer,
  "entStateOper" integer,
  "entStateUsage" integer,
  "entStateAlarm" text,
  "entStateStandby" integer
);
CREATE INDEX "entitystate_device_id_index" on "entityState"("device_id");
CREATE TABLE IF NOT EXISTS "eventlog"(
  "event_id" integer primary key autoincrement not null,
  "device_id" integer,
  "datetime" datetime not null default '1970-01-02 00:00:01',
  "message" text,
  "type" varchar,
  "reference" varchar,
  "username" varchar,
  "severity" integer not null default '2'
);
CREATE INDEX "eventlog_device_id_index" on "eventlog"("device_id");
CREATE INDEX "eventlog_datetime_index" on "eventlog"("datetime");
CREATE TABLE IF NOT EXISTS "graph_types"(
  "graph_type" varchar not null,
  "graph_subtype" varchar not null,
  "graph_section" varchar not null,
  "graph_descr" varchar,
  "graph_order" integer not null,
  primary key("graph_type", "graph_subtype", "graph_section")
);
CREATE INDEX "graph_types_graph_type_index" on "graph_types"("graph_type");
CREATE INDEX "graph_types_graph_subtype_index" on "graph_types"(
  "graph_subtype"
);
CREATE INDEX "graph_types_graph_section_index" on "graph_types"(
  "graph_section"
);
CREATE TABLE IF NOT EXISTS "hrDevice"(
  "hrDevice_id" integer primary key autoincrement not null,
  "device_id" integer not null,
  "hrDeviceIndex" integer not null,
  "hrDeviceDescr" text not null,
  "hrDeviceType" text not null,
  "hrDeviceErrors" integer not null default '0',
  "hrDeviceStatus" text not null,
  "hrProcessorLoad" integer
);
CREATE INDEX "hrdevice_device_id_index" on "hrDevice"("device_id");
CREATE TABLE IF NOT EXISTS "ipsec_tunnels"(
  "tunnel_id" integer primary key autoincrement not null,
  "device_id" integer not null,
  "peer_port" integer not null,
  "peer_addr" varchar not null,
  "local_addr" varchar not null,
  "local_port" integer not null,
  "tunnel_name" varchar not null,
  "tunnel_status" varchar not null
);
CREATE UNIQUE INDEX "ipsec_tunnels_device_id_peer_addr_unique" on "ipsec_tunnels"(
  "device_id",
  "peer_addr"
);
CREATE TABLE IF NOT EXISTS "ipv4_addresses"(
  "ipv4_address_id" integer primary key autoincrement not null,
  "ipv4_address" varchar not null,
  "ipv4_prefixlen" integer not null,
  "ipv4_network_id" varchar not null,
  "port_id" integer not null,
  "context_name" varchar
);
CREATE INDEX "ipv4_addresses_port_id_index" on "ipv4_addresses"("port_id");
CREATE TABLE IF NOT EXISTS "ipv4_mac"(
  "id" integer primary key autoincrement not null,
  "port_id" integer not null,
  "device_id" integer,
  "mac_address" varchar not null,
  "ipv4_address" varchar not null,
  "context_name" varchar
);
CREATE INDEX "ipv4_mac_port_id_index" on "ipv4_mac"("port_id");
CREATE INDEX "ipv4_mac_mac_address_index" on "ipv4_mac"("mac_address");
CREATE TABLE IF NOT EXISTS "ipv4_networks"(
  "ipv4_network_id" integer primary key autoincrement not null,
  "ipv4_network" varchar not null,
  "context_name" varchar
);
CREATE TABLE IF NOT EXISTS "ipv6_networks"(
  "ipv6_network_id" integer primary key autoincrement not null,
  "ipv6_network" varchar not null,
  "context_name" varchar
);
CREATE TABLE IF NOT EXISTS "juniAtmVp"(
  "id" integer primary key autoincrement not null,
  "juniAtmVp_id" integer not null,
  "port_id" integer not null,
  "vp_id" integer not null,
  "vp_descr" varchar not null
);
CREATE INDEX "juniatmvp_port_id_index" on "juniAtmVp"("port_id");
CREATE TABLE IF NOT EXISTS "links"(
  "id" integer primary key autoincrement not null,
  "local_port_id" integer,
  "local_device_id" integer not null,
  "remote_port_id" integer,
  "active" tinyint(1) not null default '1',
  "protocol" varchar,
  "remote_hostname" varchar not null,
  "remote_device_id" integer not null,
  "remote_port" varchar not null,
  "remote_platform" varchar,
  "remote_version" varchar not null
);
CREATE INDEX "local_device_id" on "links"(
  "local_device_id",
  "remote_device_id"
);
CREATE INDEX "links_local_port_id_index" on "links"("local_port_id");
CREATE INDEX "links_remote_port_id_index" on "links"("remote_port_id");
CREATE TABLE IF NOT EXISTS "loadbalancer_rservers"(
  "rserver_id" integer primary key autoincrement not null,
  "farm_id" varchar not null,
  "device_id" integer not null,
  "StateDescr" varchar not null
);
CREATE TABLE IF NOT EXISTS "loadbalancer_vservers"(
  "id" integer primary key autoincrement not null,
  "classmap_id" integer not null,
  "classmap" varchar not null,
  "serverstate" varchar not null,
  "device_id" integer not null
);
CREATE INDEX "loadbalancer_vservers_device_id_index" on "loadbalancer_vservers"(
  "device_id"
);
CREATE TABLE IF NOT EXISTS "mac_accounting"(
  "ma_id" integer primary key autoincrement not null,
  "port_id" integer not null,
  "mac" varchar not null,
  "in_oid" varchar not null,
  "out_oid" varchar not null,
  "bps_out" integer not null,
  "bps_in" integer not null,
  "cipMacHCSwitchedBytes_input" integer,
  "cipMacHCSwitchedBytes_input_prev" integer,
  "cipMacHCSwitchedBytes_input_delta" integer,
  "cipMacHCSwitchedBytes_input_rate" integer,
  "cipMacHCSwitchedBytes_output" integer,
  "cipMacHCSwitchedBytes_output_prev" integer,
  "cipMacHCSwitchedBytes_output_delta" integer,
  "cipMacHCSwitchedBytes_output_rate" integer,
  "cipMacHCSwitchedPkts_input" integer,
  "cipMacHCSwitchedPkts_input_prev" integer,
  "cipMacHCSwitchedPkts_input_delta" integer,
  "cipMacHCSwitchedPkts_input_rate" integer,
  "cipMacHCSwitchedPkts_output" integer,
  "cipMacHCSwitchedPkts_output_prev" integer,
  "cipMacHCSwitchedPkts_output_delta" integer,
  "cipMacHCSwitchedPkts_output_rate" integer,
  "poll_time" integer,
  "poll_prev" integer,
  "poll_period" integer
);
CREATE INDEX "mac_accounting_port_id_index" on "mac_accounting"("port_id");
CREATE TABLE IF NOT EXISTS "mefinfo"(
  "id" integer primary key autoincrement not null,
  "device_id" integer not null,
  "mefID" integer not null,
  "mefType" varchar not null,
  "mefIdent" varchar not null,
  "mefMTU" integer not null default '1500',
  "mefAdmState" varchar not null,
  "mefRowState" varchar not null
);
CREATE INDEX "mefinfo_device_id_index" on "mefinfo"("device_id");
CREATE INDEX "mefinfo_mefid_index" on "mefinfo"("mefID");
CREATE TABLE IF NOT EXISTS "munin_plugins_ds"(
  "mplug_id" integer not null,
  "ds_name" varchar not null,
  "ds_type" varchar check("ds_type" in('COUNTER', 'ABSOLUTE', 'DERIVE', 'GAUGE')) not null default 'GAUGE',
  "ds_label" varchar not null,
  "ds_cdef" varchar not null,
  "ds_draw" varchar not null,
  "ds_graph" varchar check("ds_graph" in('no', 'yes')) not null default 'yes',
  "ds_info" varchar not null,
  "ds_extinfo" text not null,
  "ds_max" varchar not null,
  "ds_min" varchar not null,
  "ds_negative" varchar not null,
  "ds_warning" varchar not null,
  "ds_critical" varchar not null,
  "ds_colour" varchar not null,
  "ds_sum" text not null,
  "ds_stack" text not null,
  "ds_line" varchar not null
);
CREATE UNIQUE INDEX "munin_plugins_ds_mplug_id_ds_name_unique" on "munin_plugins_ds"(
  "mplug_id",
  "ds_name"
);
CREATE TABLE IF NOT EXISTS "munin_plugins"(
  "mplug_id" integer primary key autoincrement not null,
  "device_id" integer not null,
  "mplug_type" varchar not null,
  "mplug_instance" varchar,
  "mplug_category" varchar,
  "mplug_title" varchar,
  "mplug_info" text,
  "mplug_vlabel" varchar,
  "mplug_args" varchar,
  "mplug_total" tinyint(1) not null default '0',
  "mplug_graph" tinyint(1) not null default '1'
);
CREATE UNIQUE INDEX "munin_plugins_device_id_mplug_type_unique" on "munin_plugins"(
  "device_id",
  "mplug_type"
);
CREATE INDEX "munin_plugins_device_id_index" on "munin_plugins"("device_id");
CREATE TABLE IF NOT EXISTS "netscaler_vservers"(
  "vsvr_id" integer primary key autoincrement not null,
  "device_id" integer not null,
  "vsvr_name" varchar not null,
  "vsvr_ip" varchar not null,
  "vsvr_port" integer not null,
  "vsvr_type" varchar not null,
  "vsvr_state" varchar not null,
  "vsvr_clients" integer not null,
  "vsvr_server" integer not null,
  "vsvr_req_rate" integer not null,
  "vsvr_bps_in" integer not null,
  "vsvr_bps_out" integer not null
);
CREATE TABLE IF NOT EXISTS "notifications_attribs"(
  "attrib_id" integer primary key autoincrement not null,
  "notifications_id" integer not null,
  "user_id" integer not null,
  "key" varchar not null default '',
  "value" varchar not null default ''
);
CREATE TABLE IF NOT EXISTS "notifications"(
  "notifications_id" integer primary key autoincrement not null,
  "title" varchar not null default '',
  "body" text not null,
  "severity" integer default '0',
  "source" varchar not null default '',
  "checksum" varchar not null,
  "datetime" datetime not null default '1970-01-02 00:00:00'
);
CREATE INDEX "notifications_severity_index" on "notifications"("severity");
CREATE UNIQUE INDEX "notifications_checksum_unique" on "notifications"(
  "checksum"
);
CREATE TABLE IF NOT EXISTS "pdb_ix_peers"(
  "pdb_ix_peers_id" integer primary key autoincrement not null,
  "ix_id" integer not null,
  "peer_id" integer not null,
  "remote_asn" integer not null,
  "remote_ipaddr4" varchar,
  "remote_ipaddr6" varchar,
  "name" varchar,
  "timestamp" integer
);
CREATE TABLE IF NOT EXISTS "pdb_ix"(
  "pdb_ix_id" integer primary key autoincrement not null,
  "ix_id" integer not null,
  "name" varchar not null,
  "asn" integer not null,
  "timestamp" integer not null
);
CREATE TABLE IF NOT EXISTS "plugins"(
  "plugin_id" integer primary key autoincrement not null,
  "plugin_name" varchar not null,
  "plugin_active" integer not null,
  "version" integer not null default '1',
  "settings" text
);
CREATE TABLE IF NOT EXISTS "poller_cluster"(
  "id" integer primary key autoincrement not null,
  "node_id" varchar not null,
  "poller_name" varchar not null,
  "poller_version" varchar not null default '',
  "poller_groups" varchar not null default '',
  "last_report" datetime not null,
  "master" tinyint(1) not null,
  "poller_enabled" tinyint(1),
  "poller_frequency" integer,
  "poller_workers" integer,
  "poller_down_retry" integer,
  "discovery_enabled" tinyint(1),
  "discovery_frequency" integer,
  "discovery_workers" integer,
  "services_enabled" tinyint(1),
  "services_frequency" integer,
  "services_workers" integer,
  "billing_enabled" tinyint(1),
  "billing_frequency" integer,
  "billing_calculate_frequency" integer,
  "alerting_enabled" tinyint(1),
  "alerting_frequency" integer,
  "ping_enabled" tinyint(1),
  "ping_frequency" integer,
  "update_enabled" tinyint(1),
  "update_frequency" integer,
  "loglevel" varchar,
  "watchdog_enabled" tinyint(1),
  "watchdog_log" varchar
);
CREATE UNIQUE INDEX "poller_cluster_node_id_unique" on "poller_cluster"(
  "node_id"
);
CREATE TABLE IF NOT EXISTS "poller_groups"(
  "id" integer primary key autoincrement not null,
  "group_name" varchar not null,
  "descr" varchar not null
);
CREATE TABLE IF NOT EXISTS "pollers"(
  "id" integer primary key autoincrement not null,
  "poller_name" varchar not null,
  "last_polled" datetime not null,
  "devices" integer not null,
  "time_taken" double not null
);
CREATE UNIQUE INDEX "pollers_poller_name_unique" on "pollers"("poller_name");
CREATE TABLE IF NOT EXISTS "ports_fdb"(
  "ports_fdb_id" integer primary key autoincrement not null,
  "port_id" integer not null,
  "mac_address" varchar not null,
  "vlan_id" integer not null,
  "device_id" integer not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "ports_fdb_port_id_index" on "ports_fdb"("port_id");
CREATE INDEX "ports_fdb_mac_address_index" on "ports_fdb"("mac_address");
CREATE INDEX "ports_fdb_vlan_id_index" on "ports_fdb"("vlan_id");
CREATE INDEX "ports_fdb_device_id_index" on "ports_fdb"("device_id");
CREATE TABLE IF NOT EXISTS "ports_perms"(
  "id" integer primary key autoincrement not null,
  "user_id" integer not null,
  "port_id" integer not null
);
CREATE TABLE IF NOT EXISTS "ports_stack"(
  "id" integer primary key autoincrement not null,
  "device_id" integer not null,
  "high_ifIndex" integer not null,
  "high_port_id" integer,
  "low_ifIndex" integer not null,
  "low_port_id" integer,
  "ifStackStatus" varchar not null
);
CREATE UNIQUE INDEX "ports_stack_device_id_port_id_high_port_id_low_unique" on "ports_stack"(
  "device_id",
  "high_ifIndex",
  "low_ifIndex"
);
CREATE TABLE IF NOT EXISTS "ports_vlans"(
  "port_vlan_id" integer primary key autoincrement not null,
  "device_id" integer not null,
  "port_id" integer not null,
  "vlan" integer not null,
  "baseport" integer not null,
  "priority" integer not null,
  "state" varchar not null,
  "cost" integer not null,
  "untagged" tinyint(1) not null default '0'
);
CREATE UNIQUE INDEX "ports_vlans_device_id_port_id_vlan_unique" on "ports_vlans"(
  "device_id",
  "port_id",
  "vlan"
);
CREATE TABLE IF NOT EXISTS "processors"(
  "processor_id" integer primary key autoincrement not null,
  "entPhysicalIndex" integer not null default '0',
  "hrDeviceIndex" integer,
  "device_id" integer not null,
  "processor_oid" varchar not null,
  "processor_index" varchar not null,
  "processor_type" varchar not null,
  "processor_usage" integer not null,
  "processor_descr" varchar not null,
  "processor_precision" integer not null default '1',
  "processor_perc_warn" integer default '75'
);
CREATE INDEX "processors_device_id_index" on "processors"("device_id");
CREATE TABLE IF NOT EXISTS "proxmox_ports"(
  "id" integer primary key autoincrement not null,
  "vm_id" integer not null,
  "port" varchar not null,
  "last_seen" datetime not null default CURRENT_TIMESTAMP
);
CREATE UNIQUE INDEX "proxmox_ports_vm_id_port_unique" on "proxmox_ports"(
  "vm_id",
  "port"
);
CREATE TABLE IF NOT EXISTS "proxmox"(
  "id" integer primary key autoincrement not null,
  "device_id" integer not null default '0',
  "vmid" integer not null,
  "cluster" varchar not null,
  "description" varchar,
  "last_seen" datetime not null default CURRENT_TIMESTAMP
);
CREATE UNIQUE INDEX "proxmox_cluster_vmid_unique" on "proxmox"(
  "cluster",
  "vmid"
);
CREATE TABLE IF NOT EXISTS "session"(
  "session_id" integer primary key autoincrement not null,
  "session_username" varchar not null,
  "session_value" varchar not null,
  "session_token" varchar not null,
  "session_auth" varchar not null,
  "session_expiry" integer not null
);
CREATE UNIQUE INDEX "session_session_value_unique" on "session"(
  "session_value"
);
CREATE TABLE IF NOT EXISTS "state_indexes"(
  "state_index_id" integer primary key autoincrement not null,
  "state_name" varchar not null
);
CREATE UNIQUE INDEX "state_indexes_state_name_unique" on "state_indexes"(
  "state_name"
);
CREATE TABLE IF NOT EXISTS "state_translations"(
  "state_translation_id" integer primary key autoincrement not null,
  "state_index_id" integer not null,
  "state_descr" varchar not null,
  "state_draw_graph" tinyint(1) not null,
  "state_value" integer not null default '0',
  "state_generic_value" tinyint(1) not null,
  "state_lastupdated" datetime not null default CURRENT_TIMESTAMP
);
CREATE UNIQUE INDEX "state_translations_state_index_id_state_value_unique" on "state_translations"(
  "state_index_id",
  "state_value"
);
CREATE TABLE IF NOT EXISTS "storage"(
  "storage_id" integer primary key autoincrement not null,
  "device_id" integer not null,
  "type" varchar not null,
  "storage_index" varchar,
  "storage_type" varchar,
  "storage_descr" text not null,
  "storage_size" integer not null,
  "storage_units" integer not null,
  "storage_used" integer not null default '0',
  "storage_free" integer not null default '0',
  "storage_perc" integer not null default '0',
  "storage_perc_warn" integer default '60',
  "storage_size_oid" varchar,
  "storage_used_oid" varchar,
  "storage_free_oid" varchar,
  "storage_perc_oid" varchar
);
CREATE UNIQUE INDEX "storage_device_id_storage_mib_storage_index_unique" on "storage"(
  "device_id",
  "type",
  "storage_index"
);
CREATE INDEX "storage_device_id_index" on "storage"("device_id");
CREATE TABLE IF NOT EXISTS "stp"(
  "stp_id" integer primary key autoincrement not null,
  "device_id" integer not null,
  "rootBridge" tinyint(1) not null,
  "bridgeAddress" varchar not null,
  "protocolSpecification" varchar not null,
  "priority" integer not null,
  "timeSinceTopologyChange" varchar not null,
  "topChanges" integer not null,
  "designatedRoot" varchar not null,
  "rootCost" integer not null,
  "rootPort" integer,
  "maxAge" integer not null,
  "helloTime" integer not null,
  "holdTime" integer not null,
  "forwardDelay" integer not null,
  "bridgeMaxAge" integer not null,
  "bridgeHelloTime" integer not null,
  "bridgeForwardDelay" integer not null,
  "vlan" integer
);
CREATE INDEX "stp_device_id_index" on "stp"("device_id");
CREATE TABLE IF NOT EXISTS "syslog"(
  "device_id" integer,
  "facility" varchar,
  "priority" varchar,
  "level" varchar,
  "tag" varchar,
  "timestamp" datetime not null default CURRENT_TIMESTAMP,
  "program" varchar,
  "msg" text,
  "seq" integer primary key autoincrement not null
);
CREATE INDEX "syslog_priority_level_index" on "syslog"("priority", "level");
CREATE INDEX "syslog_device_id_timestamp_index" on "syslog"(
  "device_id",
  "timestamp"
);
CREATE INDEX "syslog_device_id_index" on "syslog"("device_id");
CREATE INDEX "syslog_timestamp_index" on "syslog"("timestamp");
CREATE INDEX "syslog_program_index" on "syslog"("program");
CREATE TABLE IF NOT EXISTS "tnmsneinfo"(
  "id" integer primary key autoincrement not null,
  "device_id" integer not null,
  "neID" integer not null,
  "neType" varchar not null,
  "neName" varchar not null,
  "neLocation" varchar not null,
  "neAlarm" varchar not null,
  "neOpMode" varchar not null,
  "neOpState" varchar not null
);
CREATE INDEX "tnmsneinfo_device_id_index" on "tnmsneinfo"("device_id");
CREATE INDEX "tnmsneinfo_neid_index" on "tnmsneinfo"("neID");
CREATE TABLE IF NOT EXISTS "transport_group_transport"(
  "id" integer primary key autoincrement not null,
  "transport_group_id" integer not null,
  "transport_id" integer not null
);
CREATE TABLE IF NOT EXISTS "ucd_diskio"(
  "diskio_id" integer primary key autoincrement not null,
  "device_id" integer not null,
  "diskio_index" integer not null,
  "diskio_descr" varchar not null
);
CREATE INDEX "ucd_diskio_device_id_index" on "ucd_diskio"("device_id");
CREATE TABLE IF NOT EXISTS "users_prefs"(
  "user_id" integer not null,
  "pref" varchar not null,
  "value" varchar not null
);
CREATE UNIQUE INDEX "users_prefs_user_id_pref_unique" on "users_prefs"(
  "user_id",
  "pref"
);
CREATE TABLE IF NOT EXISTS "vlans"(
  "vlan_id" integer primary key autoincrement not null,
  "device_id" integer,
  "vlan_vlan" integer,
  "vlan_domain" integer,
  "vlan_name" varchar,
  "vlan_type" varchar,
  "vlan_mtu" integer
);
CREATE INDEX "device_id" on "vlans"("device_id", "vlan_vlan");
CREATE TABLE IF NOT EXISTS "vrf_lite_cisco"(
  "vrf_lite_cisco_id" integer primary key autoincrement not null,
  "device_id" integer not null,
  "context_name" varchar not null,
  "intance_name" varchar default '',
  "vrf_name" varchar default 'Default'
);
CREATE INDEX "vrf_lite_cisco_device_id_context_name_vrf_name_index" on "vrf_lite_cisco"(
  "device_id",
  "context_name",
  "vrf_name"
);
CREATE INDEX "vrf_lite_cisco_device_id_index" on "vrf_lite_cisco"("device_id");
CREATE INDEX "vrf_lite_cisco_context_name_index" on "vrf_lite_cisco"(
  "context_name"
);
CREATE INDEX "vrf_lite_cisco_vrf_name_index" on "vrf_lite_cisco"("vrf_name");
CREATE TABLE IF NOT EXISTS "vrfs"(
  "vrf_id" integer primary key autoincrement not null,
  "vrf_oid" varchar not null,
  "vrf_name" varchar,
  "mplsVpnVrfRouteDistinguisher" varchar,
  "mplsVpnVrfDescription" text not null,
  "device_id" integer not null,
  "bgpLocalAs" integer
);
CREATE INDEX "vrfs_device_id_index" on "vrfs"("device_id");
CREATE TABLE IF NOT EXISTS "component_prefs"(
  "id" integer primary key autoincrement not null,
  "component" integer not null,
  "attribute" varchar not null,
  "value" text not null,
  foreign key("component") references "component"("id") on delete CASCADE on update CASCADE
);
CREATE INDEX "component_prefs_component_index" on "component_prefs"(
  "component"
);
CREATE TABLE IF NOT EXISTS "component_statuslog"(
  "id" integer primary key autoincrement not null,
  "component_id" integer not null,
  "status" tinyint(1) not null default('0'),
  "message" text,
  "timestamp" datetime not null default(CURRENT_TIMESTAMP),
  foreign key("component_id") references "component"("id") on delete CASCADE on update CASCADE
);
CREATE INDEX "component_statuslog_component_id_index" on "component_statuslog"(
  "component_id"
);
CREATE TABLE IF NOT EXISTS "device_group_device"(
  "device_group_id" integer not null,
  "device_id" integer not null,
  foreign key("device_group_id") references "device_groups"("id") on delete CASCADE on update RESTRICT,
  foreign key("device_id") references "devices"("device_id") on delete CASCADE on update RESTRICT,
  primary key("device_group_id", "device_id")
);
CREATE INDEX "device_group_device_device_group_id_index" on "device_group_device"(
  "device_group_id"
);
CREATE INDEX "device_group_device_device_id_index" on "device_group_device"(
  "device_id"
);
CREATE TABLE IF NOT EXISTS "device_relationships"(
  "parent_device_id" integer not null default('0'),
  "child_device_id" integer not null,
  foreign key("child_device_id") references "devices"("device_id") on delete CASCADE on update RESTRICT,
  foreign key("parent_device_id") references "devices"("device_id") on delete CASCADE on update RESTRICT,
  primary key("parent_device_id", "child_device_id")
);
CREATE INDEX "device_relationships_child_device_id_index" on "device_relationships"(
  "child_device_id"
);
CREATE TABLE IF NOT EXISTS "sensors"(
  "sensor_id" integer primary key autoincrement not null,
  "sensor_deleted" tinyint(1) not null default('0'),
  "sensor_class" varchar not null,
  "device_id" integer not null default('0'),
  "poller_type" varchar not null default('snmp'),
  "sensor_oid" varchar not null,
  "sensor_index" varchar,
  "sensor_type" varchar not null,
  "sensor_descr" varchar,
  "group" varchar,
  "sensor_divisor" integer not null default('1'),
  "sensor_multiplier" integer not null default('1'),
  "sensor_current" double,
  "sensor_limit" double,
  "sensor_limit_warn" double,
  "sensor_limit_low" double,
  "sensor_limit_low_warn" double,
  "sensor_alert" tinyint(1) not null default('1'),
  "sensor_custom" varchar not null default('No'),
  "entPhysicalIndex" varchar,
  "entPhysicalIndex_measured" varchar,
  "lastupdate" datetime not null default(CURRENT_TIMESTAMP),
  "sensor_prev" double,
  "user_func" varchar,
  "rrd_type" varchar check("rrd_type" in('GAUGE', 'COUNTER', 'DERIVE', 'DCOUNTER', 'DDERIVE', 'ABSOLUTE')) not null default 'GAUGE',
  foreign key("device_id") references "devices"("device_id") on delete CASCADE on update RESTRICT
);
CREATE INDEX "sensors_device_id_index" on "sensors"("device_id");
CREATE INDEX "sensors_sensor_class_index" on "sensors"("sensor_class");
CREATE INDEX "sensors_sensor_type_index" on "sensors"("sensor_type");
CREATE TABLE IF NOT EXISTS "sensors_to_state_indexes"(
  "sensors_to_state_translations_id" integer primary key autoincrement not null,
  "sensor_id" integer not null,
  "state_index_id" integer not null,
  foreign key("state_index_id") references "state_indexes"("state_index_id") on delete RESTRICT on update RESTRICT,
  foreign key("sensor_id") references "sensors"("sensor_id") on delete CASCADE on update RESTRICT
);
CREATE UNIQUE INDEX "sensors_to_state_indexes_sensor_id_state_index_id_unique" on "sensors_to_state_indexes"(
  "sensor_id",
  "state_index_id"
);
CREATE INDEX "sensors_to_state_indexes_state_index_id_index" on "sensors_to_state_indexes"(
  "state_index_id"
);
CREATE TABLE IF NOT EXISTS "wireless_sensors"(
  "sensor_id" integer primary key autoincrement not null,
  "sensor_deleted" tinyint(1) not null default('0'),
  "sensor_class" varchar not null,
  "device_id" integer not null default('0'),
  "sensor_index" varchar,
  "sensor_type" varchar not null,
  "sensor_descr" varchar,
  "sensor_divisor" integer not null default('1'),
  "sensor_multiplier" integer not null default('1'),
  "sensor_aggregator" varchar not null default('sum'),
  "sensor_current" double,
  "sensor_prev" double,
  "sensor_limit" double,
  "sensor_limit_warn" double,
  "sensor_limit_low" double,
  "sensor_limit_low_warn" double,
  "sensor_alert" tinyint(1) not null default('1'),
  "sensor_custom" varchar not null default('No'),
  "entPhysicalIndex" varchar,
  "entPhysicalIndex_measured" varchar,
  "lastupdate" datetime not null default(CURRENT_TIMESTAMP),
  "sensor_oids" text not null,
  "access_point_id" integer,
  "rrd_type" varchar check("rrd_type" in('GAUGE', 'COUNTER', 'DERIVE', 'DCOUNTER', 'DDERIVE', 'ABSOLUTE')) not null default 'GAUGE',
  foreign key("device_id") references "devices"("device_id") on delete CASCADE on update RESTRICT
);
CREATE INDEX "wireless_sensors_device_id_index" on "wireless_sensors"(
  "device_id"
);
CREATE INDEX "wireless_sensors_sensor_class_index" on "wireless_sensors"(
  "sensor_class"
);
CREATE INDEX "wireless_sensors_sensor_type_index" on "wireless_sensors"(
  "sensor_type"
);
CREATE TABLE IF NOT EXISTS "ports_nac"(
  "ports_nac_id" integer primary key autoincrement not null,
  "auth_id" varchar not null,
  "device_id" integer not null,
  "port_id" integer not null,
  "domain" varchar not null,
  "username" varchar not null,
  "mac_address" varchar not null,
  "ip_address" varchar not null,
  "host_mode" varchar not null,
  "authz_status" varchar not null,
  "authz_by" varchar not null,
  "authc_status" varchar not null,
  "method" varchar not null,
  "timeout" varchar not null,
  "time_left" varchar,
  "vlan" integer,
  "time_elapsed" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  "historical" tinyint(1) not null default '0'
);
CREATE INDEX "ports_nac_device_id_index" on "ports_nac"("device_id");
CREATE INDEX "ports_nac_port_id_mac_address_index" on "ports_nac"(
  "port_id",
  "mac_address"
);
CREATE TABLE IF NOT EXISTS "route"(
  "route_id" integer primary key autoincrement not null,
  "created_at" datetime,
  "updated_at" datetime,
  "device_id" integer not null,
  "port_id" integer not null,
  "context_name" varchar,
  "inetCidrRouteIfIndex" integer not null,
  "inetCidrRouteType" integer not null,
  "inetCidrRouteProto" integer not null,
  "inetCidrRouteNextHopAS" integer not null,
  "inetCidrRouteMetric1" integer not null,
  "inetCidrRouteDestType" varchar not null,
  "inetCidrRouteDest" varchar not null,
  "inetCidrRouteNextHopType" varchar not null,
  "inetCidrRouteNextHop" varchar not null,
  "inetCidrRoutePolicy" varchar not null,
  "inetCidrRoutePfxLen" integer not null
);
CREATE TABLE IF NOT EXISTS "mpls_lsps"(
  "lsp_id" integer primary key autoincrement not null,
  "vrf_oid" integer not null,
  "lsp_oid" integer not null,
  "device_id" integer not null,
  "mplsLspRowStatus" varchar check("mplsLspRowStatus" in('active', 'notInService', 'notReady', 'createAndGo', 'createAndWait', 'destroy')) not null,
  "mplsLspLastChange" integer,
  "mplsLspName" varchar not null,
  "mplsLspAdminState" varchar check("mplsLspAdminState" in('noop', 'inService', 'outOfService')) not null,
  "mplsLspOperState" varchar check("mplsLspOperState" in('unknown', 'inService', 'outOfService', 'transition')) not null,
  "mplsLspFromAddr" varchar not null,
  "mplsLspToAddr" varchar not null,
  "mplsLspType" varchar check("mplsLspType" in('unknown', 'dynamic', 'static', 'bypassOnly', 'p2mpLsp', 'p2mpAuto', 'mplsTp', 'meshP2p', 'oneHopP2p', 'srTe', 'meshP2pSrTe', 'oneHopP2pSrTe')) not null,
  "mplsLspFastReroute" varchar check("mplsLspFastReroute" in('true', 'false')) not null,
  "mplsLspAge" integer,
  "mplsLspTimeUp" integer,
  "mplsLspTimeDown" integer,
  "mplsLspPrimaryTimeUp" integer,
  "mplsLspTransitions" integer,
  "mplsLspLastTransition" integer,
  "mplsLspConfiguredPaths" integer,
  "mplsLspStandbyPaths" integer,
  "mplsLspOperationalPaths" integer
);
CREATE INDEX "mpls_lsps_device_id_index" on "mpls_lsps"("device_id");
CREATE TABLE IF NOT EXISTS "device_groups"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null default(''),
  "desc" varchar default '',
  "pattern" text,
  "type" varchar not null default 'dynamic',
  "rules" text
);
CREATE UNIQUE INDEX "device_groups_name_unique" on "device_groups"("name");
CREATE TABLE IF NOT EXISTS "mpls_sdps"(
  "sdp_id" integer primary key autoincrement not null,
  "sdp_oid" integer not null,
  "device_id" integer not null,
  "sdpRowStatus" varchar check("sdpRowStatus" in('active', 'notInService', 'notReady', 'createAndGo', 'createAndWait', 'destroy')),
  "sdpDelivery" varchar check("sdpDelivery" in('gre', 'mpls', 'l2tpv3', 'greethbridged')),
  "sdpDescription" varchar,
  "sdpAdminStatus" varchar check("sdpAdminStatus" in('up', 'down')),
  "sdpOperStatus" varchar check("sdpOperStatus" in('up', 'notAlive', 'notReady', 'invalidEgressInterface', 'transportTunnelDown', 'down')),
  "sdpAdminPathMtu" integer,
  "sdpOperPathMtu" integer,
  "sdpLastMgmtChange" integer,
  "sdpLastStatusChange" integer,
  "sdpActiveLspType" varchar check("sdpActiveLspType" in('not-applicable', 'rsvp', 'ldp', 'bgp', 'none', 'mplsTp', 'srIsis', 'srOspf', 'srTeLsp', 'fpe')),
  "sdpFarEndInetAddress" varchar,
  "sdpFarEndInetAddressType" varchar check("sdpFarEndInetAddressType" in('unknown', 'ipv4', 'ipv6', 'ipv4z', 'ipv6z', 'dns'))
);
CREATE INDEX "mpls_sdps_device_id_index" on "mpls_sdps"("device_id");
CREATE TABLE IF NOT EXISTS "mpls_services"(
  "svc_id" integer primary key autoincrement not null,
  "svc_oid" integer not null,
  "device_id" integer not null,
  "svcRowStatus" varchar check("svcRowStatus" in('active', 'notInService', 'notReady', 'createAndGo', 'createAndWait', 'destroy')),
  "svcType" varchar check("svcType" in('unknown', 'epipe', 'tls', 'vprn', 'ies', 'mirror', 'apipe', 'fpipe', 'ipipe', 'cpipe', 'intTls', 'evpnIsaTls')),
  "svcCustId" integer,
  "svcAdminStatus" varchar check("svcAdminStatus" in('up', 'down')),
  "svcOperStatus" varchar check("svcOperStatus" in('up', 'down')),
  "svcDescription" varchar,
  "svcMtu" integer,
  "svcNumSaps" integer,
  "svcNumSdps" integer,
  "svcLastMgmtChange" integer,
  "svcLastStatusChange" integer,
  "svcVRouterId" integer,
  "svcTlsMacLearning" varchar check("svcTlsMacLearning" in('enabled', 'disabled')),
  "svcTlsStpAdminStatus" varchar check("svcTlsStpAdminStatus" in('enabled', 'disabled')),
  "svcTlsStpOperStatus" varchar check("svcTlsStpOperStatus" in('up', 'down')),
  "svcTlsFdbTableSize" integer,
  "svcTlsFdbNumEntries" integer
);
CREATE INDEX "mpls_services_device_id_index" on "mpls_services"("device_id");
CREATE TABLE IF NOT EXISTS "mpls_saps"(
  "sap_id" integer primary key autoincrement not null,
  "svc_id" integer not null,
  "svc_oid" integer not null,
  "sapPortId" integer not null,
  "ifName" varchar,
  "device_id" integer not null,
  "sapEncapValue" varchar,
  "sapRowStatus" varchar check("sapRowStatus" in('active', 'notInService', 'notReady', 'createAndGo', 'createAndWait', 'destroy')),
  "sapType" varchar check("sapType" in('unknown', 'epipe', 'tls', 'vprn', 'ies', 'mirror', 'apipe', 'fpipe', 'ipipe', 'cpipe', 'intTls', 'evpnIsaTls')),
  "sapDescription" varchar,
  "sapAdminStatus" varchar check("sapAdminStatus" in('up', 'down')),
  "sapOperStatus" varchar check("sapOperStatus" in('up', 'down')),
  "sapLastMgmtChange" integer,
  "sapLastStatusChange" integer
);
CREATE INDEX "mpls_saps_device_id_index" on "mpls_saps"("device_id");
CREATE INDEX "notifications_attribs_notifications_id_user_id_index" on "notifications_attribs"(
  "notifications_id",
  "user_id"
);
CREATE TABLE IF NOT EXISTS "devices_group_perms"(
  "user_id" integer not null,
  "device_group_id" integer not null,
  primary key("device_group_id", "user_id")
);
CREATE INDEX "devices_group_perms_user_id_index" on "devices_group_perms"(
  "user_id"
);
CREATE INDEX "devices_group_perms_device_group_id_index" on "devices_group_perms"(
  "device_group_id"
);
CREATE TABLE IF NOT EXISTS "mpls_tunnel_ar_hops"(
  "ar_hop_id" integer primary key autoincrement not null,
  "mplsTunnelARHopListIndex" integer not null,
  "mplsTunnelARHopIndex" integer not null,
  "device_id" integer not null,
  "lsp_path_id" integer not null,
  "mplsTunnelARHopAddrType" varchar check("mplsTunnelARHopAddrType" in('unknown', 'ipV4', 'ipV6', 'asNumber', 'lspid', 'unnum')),
  "mplsTunnelARHopIpv4Addr" varchar,
  "mplsTunnelARHopIpv6Addr" varchar,
  "mplsTunnelARHopAsNumber" integer,
  "mplsTunnelARHopStrictOrLoose" varchar check("mplsTunnelARHopStrictOrLoose" in('strict', 'loose')),
  "mplsTunnelARHopRouterId" varchar,
  "localProtected" varchar check("localProtected" in('false', 'true')) not null default 'false',
  "linkProtectionInUse" varchar check("linkProtectionInUse" in('false', 'true')) not null default 'false',
  "bandwidthProtected" varchar check("bandwidthProtected" in('false', 'true')) not null default 'false',
  "nextNodeProtected" varchar check("nextNodeProtected" in('false', 'true')) not null default 'false'
);
CREATE INDEX "mpls_tunnel_ar_hops_device_id_index" on "mpls_tunnel_ar_hops"(
  "device_id"
);
CREATE TABLE IF NOT EXISTS "mpls_tunnel_c_hops"(
  "c_hop_id" integer primary key autoincrement not null,
  "mplsTunnelCHopListIndex" integer not null,
  "mplsTunnelCHopIndex" integer not null,
  "device_id" integer not null,
  "lsp_path_id" integer,
  "mplsTunnelCHopAddrType" varchar check("mplsTunnelCHopAddrType" in('unknown', 'ipV4', 'ipV6', 'asNumber', 'lspid', 'unnum')),
  "mplsTunnelCHopIpv4Addr" varchar,
  "mplsTunnelCHopIpv6Addr" varchar,
  "mplsTunnelCHopAsNumber" integer,
  "mplsTunnelCHopStrictOrLoose" varchar check("mplsTunnelCHopStrictOrLoose" in('strict', 'loose')),
  "mplsTunnelCHopRouterId" varchar
);
CREATE INDEX "mpls_tunnel_c_hops_device_id_index" on "mpls_tunnel_c_hops"(
  "device_id"
);
CREATE TABLE IF NOT EXISTS "customoids"(
  "customoid_id" integer primary key autoincrement not null,
  "device_id" integer not null default '0',
  "customoid_descr" varchar default '',
  "customoid_deleted" integer not null default '0',
  "customoid_current" double,
  "customoid_prev" double,
  "customoid_oid" varchar,
  "customoid_datatype" varchar not null default 'GAUGE',
  "customoid_unit" varchar,
  "customoid_divisor" integer not null default '1',
  "customoid_multiplier" integer not null default '1',
  "customoid_limit" double,
  "customoid_limit_warn" double,
  "customoid_limit_low" double,
  "customoid_limit_low_warn" double,
  "customoid_alert" integer not null default '0',
  "customoid_passed" integer not null default '0',
  "lastupdate" datetime not null default CURRENT_TIMESTAMP,
  "user_func" varchar
);
CREATE TABLE IF NOT EXISTS "mpls_lsp_paths"(
  "lsp_path_id" integer primary key autoincrement not null,
  "lsp_id" integer not null,
  "path_oid" integer not null,
  "device_id" integer not null,
  "mplsLspPathRowStatus" varchar not null,
  "mplsLspPathLastChange" integer not null,
  "mplsLspPathType" varchar not null,
  "mplsLspPathBandwidth" integer not null,
  "mplsLspPathOperBandwidth" integer not null,
  "mplsLspPathAdminState" varchar not null,
  "mplsLspPathOperState" varchar not null,
  "mplsLspPathState" varchar not null,
  "mplsLspPathFailCode" varchar not null,
  "mplsLspPathFailNodeAddr" varchar not null,
  "mplsLspPathMetric" integer not null,
  "mplsLspPathOperMetric" integer,
  "mplsLspPathTimeUp" integer,
  "mplsLspPathTimeDown" integer,
  "mplsLspPathTransitionCount" integer,
  "mplsLspPathTunnelARHopListIndex" integer,
  "mplsLspPathTunnelCHopListIndex" integer
);
CREATE INDEX "mpls_lsp_paths_device_id_index" on "mpls_lsp_paths"("device_id");
CREATE TABLE IF NOT EXISTS "alert_location_map"(
  "id" integer primary key autoincrement not null,
  "rule_id" integer not null,
  "location_id" integer not null
);
CREATE UNIQUE INDEX "alert_location_map_rule_id_location_id_uindex" on "alert_location_map"(
  "rule_id",
  "location_id"
);
CREATE TABLE IF NOT EXISTS "application_metrics"(
  "id" integer primary key autoincrement not null,
  "app_id" integer not null,
  "metric" varchar not null,
  "value" double,
  "value_prev" double
);
CREATE UNIQUE INDEX "application_metrics_app_id_metric_unique" on "application_metrics"(
  "app_id",
  "metric"
);
CREATE TABLE IF NOT EXISTS "device_outages"(
  "id" integer primary key autoincrement not null,
  "device_id" integer not null,
  "going_down" integer not null,
  "up_again" integer
);
CREATE UNIQUE INDEX "device_outages_device_id_going_down_unique" on "device_outages"(
  "device_id",
  "going_down"
);
CREATE INDEX "device_outages_device_id_index" on "device_outages"("device_id");
CREATE TABLE IF NOT EXISTS "availability"(
  "availability_id" integer primary key autoincrement not null,
  "device_id" integer not null,
  "duration" integer not null,
  "availability_perc" numeric not null default '0'
);
CREATE UNIQUE INDEX "availability_device_id_duration_unique" on "availability"(
  "device_id",
  "duration"
);
CREATE INDEX "availability_device_id_index" on "availability"("device_id");
CREATE TABLE IF NOT EXISTS "service_templates"(
  "id" integer primary key autoincrement not null,
  "ip" text,
  "check" varchar not null,
  "type" varchar not null default 'static',
  "rules" text,
  "desc" text,
  "param" text,
  "ignore" tinyint(1) not null default '0',
  "changed" datetime not null default CURRENT_TIMESTAMP,
  "disabled" tinyint(1) not null default '0',
  "name" varchar not null
);
CREATE TABLE IF NOT EXISTS "services"(
  "service_id" integer primary key autoincrement not null,
  "device_id" integer not null,
  "service_ip" text,
  "service_type" varchar not null,
  "service_desc" text,
  "service_param" text,
  "service_ignore" tinyint(1) not null default '0',
  "service_status" integer not null default('0'),
  "service_changed" integer not null default('0'),
  "service_message" text,
  "service_disabled" tinyint(1) not null default('0'),
  "service_ds" text,
  "service_template_id" integer not null default '0',
  "service_name" varchar
);
CREATE INDEX "services_device_id_index" on "services"("device_id");
CREATE TABLE IF NOT EXISTS "service_templates_device_group"(
  "service_template_id" integer not null,
  "device_group_id" integer not null,
  foreign key("service_template_id") references "service_templates"("id") on delete CASCADE on update RESTRICT,
  foreign key("device_group_id") references "device_groups"("id") on delete CASCADE on update RESTRICT,
  primary key("service_template_id", "device_group_id")
);
CREATE INDEX "service_templates_device_group_device_group_id_index" on "service_templates_device_group"(
  "device_group_id"
);
CREATE INDEX "service_templates_device_group_service_template_id_index" on "service_templates_device_group"(
  "service_template_id"
);
CREATE TABLE IF NOT EXISTS "service_templates_device"(
  "service_template_id" integer not null,
  "device_id" integer not null,
  foreign key("service_template_id") references "service_templates"("id") on delete CASCADE on update RESTRICT,
  foreign key("device_id") references "devices"("device_id") on delete CASCADE on update RESTRICT,
  primary key("service_template_id", "device_id")
);
CREATE INDEX "service_templates_device_device_id_index" on "service_templates_device"(
  "device_id"
);
CREATE INDEX "service_templates_device_service_template_id_index" on "service_templates_device"(
  "service_template_id"
);
CREATE INDEX "alert_log_rule_id_device_id_state_index" on "alert_log"(
  "rule_id",
  "device_id",
  "state"
);
CREATE TABLE IF NOT EXISTS "cache_locks"(
  "key" varchar not null,
  "owner" varchar not null,
  "expiration" integer not null,
  primary key("key")
);
CREATE TABLE IF NOT EXISTS "mempools"(
  "mempool_id" integer primary key autoincrement not null,
  "mempool_index" varchar not null,
  "entPhysicalIndex" integer,
  "mempool_type" varchar not null,
  "mempool_precision" integer not null default('1'),
  "mempool_descr" varchar not null,
  "device_id" integer not null,
  "mempool_perc" integer not null,
  "mempool_used" integer not null,
  "mempool_free" integer not null,
  "mempool_total" integer not null,
  "mempool_largestfree" integer,
  "mempool_lowestfree" integer,
  "mempool_deleted" tinyint(1) not null default('0'),
  "mempool_perc_warn" integer,
  "mempool_class" varchar not null default 'system',
  "mempool_perc_oid" varchar,
  "mempool_used_oid" varchar,
  "mempool_free_oid" varchar,
  "mempool_total_oid" varchar
);
CREATE INDEX "mempools_device_id_index" on "mempools"("device_id");
CREATE TABLE IF NOT EXISTS "port_groups"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "desc" varchar
);
CREATE UNIQUE INDEX "port_groups_name_unique" on "port_groups"("name");
CREATE TABLE IF NOT EXISTS "printer_supplies"(
  "supply_id" integer primary key autoincrement not null,
  "device_id" integer not null default('0'),
  "supply_index" integer not null,
  "supply_type" varchar not null,
  "supply_oid" varchar not null,
  "supply_descr" varchar not null default '',
  "supply_capacity" integer not null default('0'),
  "supply_current" integer not null default('0'),
  "supply_capacity_oid" varchar
);
CREATE INDEX "toner_device_id_index" on "printer_supplies"("device_id");
CREATE TABLE IF NOT EXISTS "cache"(
  "key" varchar not null,
  "value" text not null,
  "expiration" integer not null
);
CREATE UNIQUE INDEX "cache_key_unique" on "cache"("key");
CREATE TABLE IF NOT EXISTS "port_group_port"(
  "port_group_id" integer not null,
  "port_id" integer not null,
  foreign key("port_group_id") references "port_groups"("id") on delete CASCADE,
  foreign key("port_id") references "ports"("port_id") on delete CASCADE,
  primary key("port_group_id", "port_id")
);
CREATE INDEX "port_group_port_port_group_id_index" on "port_group_port"(
  "port_group_id"
);
CREATE INDEX "port_group_port_port_id_index" on "port_group_port"("port_id");
CREATE TABLE IF NOT EXISTS "sessions"(
  "id" varchar not null,
  "user_id" integer,
  "ip_address" varchar,
  "user_agent" text,
  "payload" text not null,
  "last_activity" integer not null,
  primary key("id")
);
CREATE INDEX "sessions_user_id_index" on "sessions"("user_id");
CREATE INDEX "sessions_last_activity_index" on "sessions"("last_activity");
CREATE INDEX "syslog_device_id_program_index" on "syslog"(
  "device_id",
  "program"
);
CREATE INDEX "syslog_device_id_priority_index" on "syslog"(
  "device_id",
  "priority"
);
CREATE TABLE IF NOT EXISTS "config"(
  "config_id" integer primary key autoincrement not null,
  "config_name" varchar not null,
  "config_value" text not null
);
CREATE UNIQUE INDEX "config_config_name_unique" on "config"("config_name");
CREATE TABLE IF NOT EXISTS "push_subscriptions"(
  "id" integer primary key autoincrement not null,
  "subscribable_type" varchar not null,
  "subscribable_id" integer not null,
  "endpoint" varchar not null,
  "public_key" varchar,
  "auth_token" varchar,
  "content_encoding" varchar,
  "description" varchar,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "push_subscriptions_subscribable_type_subscribable_id_index" on "push_subscriptions"(
  "subscribable_type",
  "subscribable_id"
);
CREATE UNIQUE INDEX "push_subscriptions_endpoint_unique" on "push_subscriptions"(
  "endpoint"
);
CREATE TABLE IF NOT EXISTS "hrSystem"(
  "hrSystem_id" integer primary key autoincrement not null,
  "device_id" integer not null,
  "hrSystemNumUsers" integer,
  "hrSystemProcesses" integer,
  "hrSystemMaxProcesses" integer
);
CREATE INDEX "hrsystem_device_id_index" on "hrSystem"("device_id");
CREATE TABLE IF NOT EXISTS "devices_attribs"(
  "attrib_id" integer primary key autoincrement not null,
  "device_id" integer not null,
  "attrib_type" varchar not null,
  "attrib_value" text not null,
  "updated" datetime not null default(CURRENT_TIMESTAMP)
);
CREATE INDEX "devices_attribs_device_id_index" on "devices_attribs"(
  "device_id"
);
CREATE TABLE IF NOT EXISTS "pseudowires"(
  "pseudowire_id" integer primary key autoincrement not null,
  "device_id" integer not null,
  "port_id" integer not null,
  "peer_device_id" integer not null,
  "peer_ldp_id" integer not null,
  "cpwVcID" integer not null,
  "cpwOid" integer not null,
  "pw_type" varchar not null,
  "pw_psntype" varchar not null,
  "pw_local_mtu" integer not null,
  "pw_peer_mtu" integer not null,
  "pw_descr" varchar not null
);
CREATE TABLE IF NOT EXISTS "ports_stp"(
  "port_stp_id" integer primary key autoincrement not null,
  "device_id" integer not null,
  "port_id" integer not null,
  "priority" integer not null,
  "state" varchar not null,
  "enable" varchar not null,
  "pathCost" integer not null,
  "designatedRoot" varchar not null,
  "designatedCost" integer not null,
  "designatedBridge" varchar not null,
  "designatedPort" integer not null,
  "forwardTransitions" integer not null,
  "vlan" integer,
  "port_index" integer not null default('0')
);
CREATE UNIQUE INDEX "ports_stp_device_id_vlan_port_index_unique" on "ports_stp"(
  "device_id",
  "vlan",
  "port_index"
);
CREATE TABLE IF NOT EXISTS "isis_adjacencies"(
  "id" integer primary key autoincrement not null,
  "device_id" integer not null,
  "port_id" integer,
  "ifIndex" integer not null,
  "isisISAdjState" varchar not null,
  "isisISAdjNeighSysType" varchar,
  "isisISAdjNeighSysID" varchar,
  "isisISAdjNeighPriority" varchar,
  "isisISAdjLastUpTime" integer,
  "isisISAdjAreaAddress" varchar,
  "isisISAdjIPAddrType" varchar,
  "isisISAdjIPAddrAddress" varchar,
  "isisCircAdminState" varchar not null default('off'),
  "index" varchar
);
CREATE INDEX "isis_adjacencies_device_id_index" on "isis_adjacencies"(
  "device_id"
);
CREATE INDEX "isis_adjacencies_ifindex_index" on "isis_adjacencies"("ifIndex");
CREATE INDEX "isis_adjacencies_port_id_index" on "isis_adjacencies"("port_id");
CREATE TABLE IF NOT EXISTS "users"(
  "user_id" integer primary key autoincrement not null,
  "auth_type" varchar,
  "auth_id" varchar,
  "username" varchar not null,
  "password" varchar,
  "realname" varchar not null,
  "email" varchar not null,
  "descr" varchar not null,
  "can_modify_passwd" tinyint(1) not null default('1'),
  "created_at" datetime not null default('1970-01-02 00:00:01'),
  "updated_at" datetime not null default(CURRENT_TIMESTAMP),
  "remember_token" varchar,
  "enabled" tinyint(1) not null default('1')
);
CREATE UNIQUE INDEX "users_auth_type_username_unique" on "users"(
  "auth_type",
  "username"
);
CREATE TABLE IF NOT EXISTS "users_widgets"(
  "user_widget_id" integer primary key autoincrement not null,
  "user_id" integer not null,
  "col" integer not null,
  "row" integer not null,
  "size_x" integer not null,
  "size_y" integer not null,
  "title" varchar not null,
  "refresh" integer not null default('60'),
  "settings" text not null,
  "dashboard_id" integer not null,
  "widget" varchar not null
);
CREATE INDEX "user_id" on "users_widgets"("user_id");
CREATE UNIQUE INDEX "plugins_version_plugin_name_unique" on "plugins"(
  "version",
  "plugin_name"
);
CREATE TABLE IF NOT EXISTS "ports_vdsl"(
  "port_id" integer not null,
  "port_vdsl_updated" datetime not null default CURRENT_TIMESTAMP,
  "xdsl2LineStatusAttainableRateDs" integer not null default '0',
  "xdsl2LineStatusAttainableRateUs" integer not null default '0',
  "xdsl2ChStatusActDataRateXtur" integer not null default '0',
  "xdsl2ChStatusActDataRateXtuc" integer not null default '0',
  "xdsl2LineStatusActAtpDs" numeric not null default '0',
  "xdsl2LineStatusActAtpUs" numeric not null default '0'
);
CREATE UNIQUE INDEX "ports_vdsl_port_id_unique" on "ports_vdsl"("port_id");
CREATE TABLE IF NOT EXISTS "ports_adsl"(
  "port_id" integer not null,
  "port_adsl_updated" datetime not null default(CURRENT_TIMESTAMP),
  "adslLineCoding" varchar not null default '',
  "adslLineType" varchar not null default '',
  "adslAtucInvVendorID" varchar not null default '',
  "adslAtucInvVersionNumber" varchar not null default '',
  "adslAtucCurrSnrMgn" numeric not null default '0',
  "adslAtucCurrAtn" numeric not null default '0',
  "adslAtucCurrOutputPwr" numeric not null default '0',
  "adslAtucCurrAttainableRate" integer not null default '0',
  "adslAtucChanCurrTxRate" integer not null default '0',
  "adslAturInvSerialNumber" varchar not null default '',
  "adslAturInvVendorID" varchar not null default '',
  "adslAturInvVersionNumber" varchar not null default '',
  "adslAturChanCurrTxRate" integer not null default '0',
  "adslAturCurrSnrMgn" numeric not null default '0',
  "adslAturCurrAtn" numeric not null default '0',
  "adslAturCurrOutputPwr" numeric not null default '0',
  "adslAturCurrAttainableRate" integer not null default '0'
);
CREATE UNIQUE INDEX "ports_adsl_port_id_unique" on "ports_adsl"("port_id");
CREATE TABLE IF NOT EXISTS "ports"(
  "port_id" integer primary key autoincrement not null,
  "device_id" integer not null default('0'),
  "port_descr_type" varchar,
  "port_descr_descr" varchar,
  "port_descr_circuit" varchar,
  "port_descr_speed" varchar,
  "port_descr_notes" varchar,
  "ifDescr" varchar,
  "ifName" varchar,
  "portName" varchar,
  "ifIndex" integer default('0'),
  "ifSpeed" integer,
  "ifConnectorPresent" varchar,
  "ifOperStatus" varchar,
  "ifOperStatus_prev" varchar,
  "ifAdminStatus" varchar,
  "ifAdminStatus_prev" varchar,
  "ifDuplex" varchar,
  "ifMtu" integer,
  "ifType" varchar,
  "ifAlias" varchar,
  "ifPhysAddress" varchar,
  "ifLastChange" integer not null default('0'),
  "ifVlan" varchar,
  "ifTrunk" varchar,
  "ifVrf" integer not null default('0'),
  "ignore" tinyint(1) not null default('0'),
  "disabled" tinyint(1) not null default('0'),
  "deleted" tinyint(1) not null default('0'),
  "pagpOperationMode" varchar,
  "pagpPortState" varchar,
  "pagpPartnerDeviceId" varchar,
  "pagpPartnerLearnMethod" varchar,
  "pagpPartnerIfIndex" integer,
  "pagpPartnerGroupIfIndex" integer,
  "pagpPartnerDeviceName" varchar,
  "pagpEthcOperationMode" varchar,
  "pagpDeviceId" varchar,
  "pagpGroupIfIndex" integer,
  "ifInUcastPkts" integer,
  "ifInUcastPkts_prev" integer,
  "ifInUcastPkts_delta" integer,
  "ifInUcastPkts_rate" integer,
  "ifOutUcastPkts" integer,
  "ifOutUcastPkts_prev" integer,
  "ifOutUcastPkts_delta" integer,
  "ifOutUcastPkts_rate" integer,
  "ifInErrors" integer,
  "ifInErrors_prev" integer,
  "ifInErrors_delta" integer,
  "ifInErrors_rate" integer,
  "ifOutErrors" integer,
  "ifOutErrors_prev" integer,
  "ifOutErrors_delta" integer,
  "ifOutErrors_rate" integer,
  "ifInOctets" integer,
  "ifInOctets_prev" integer,
  "ifInOctets_delta" integer,
  "ifInOctets_rate" integer,
  "ifOutOctets" integer,
  "ifOutOctets_prev" integer,
  "ifOutOctets_delta" integer,
  "ifOutOctets_rate" integer,
  "poll_time" integer,
  "poll_prev" integer,
  "poll_period" integer,
  "ifSpeed_prev" integer
);
CREATE INDEX "ports_ifalias_port_descr_descr_portname_index" on "ports"(
  "ifAlias",
  "port_descr_descr",
  "portName"
);
CREATE INDEX "ports_ifdescr_ifname_index" on "ports"("ifDescr", "ifName");
CREATE TABLE IF NOT EXISTS "devices"(
  "device_id" integer primary key autoincrement not null,
  "hostname" varchar not null,
  "sysName" varchar,
  "ip" blob,
  "community" varchar,
  "authlevel" varchar,
  "authname" varchar,
  "authpass" varchar,
  "authalgo" varchar,
  "cryptopass" varchar,
  "cryptoalgo" varchar,
  "snmpver" varchar not null default('v2c'),
  "port" integer not null default('161'),
  "transport" varchar not null default('udp'),
  "timeout" integer,
  "retries" integer,
  "snmp_disable" tinyint(1) not null default('0'),
  "bgpLocalAs" integer,
  "sysObjectID" varchar,
  "sysDescr" text,
  "sysContact" text,
  "version" text,
  "hardware" text,
  "features" text,
  "location_id" integer,
  "os" varchar,
  "status" tinyint(1) not null default('0'),
  "status_reason" varchar not null,
  "ignore" tinyint(1) not null default('0'),
  "disabled" tinyint(1) not null default('0'),
  "uptime" integer,
  "agent_uptime" integer not null default('0'),
  "last_polled" datetime,
  "last_poll_attempted" datetime,
  "last_polled_timetaken" float,
  "last_discovered_timetaken" float,
  "last_discovered" datetime,
  "last_ping" datetime,
  "last_ping_timetaken" float,
  "purpose" text,
  "type" varchar not null default(''),
  "serial" text,
  "icon" varchar,
  "poller_group" integer not null default('0'),
  "override_sysLocation" tinyint(1) default('0'),
  "notes" text,
  "port_association_mode" integer not null default('1'),
  "max_depth" integer not null default('0'),
  "overwrite_ip" varchar,
  "disable_notify" tinyint(1) not null default('0'),
  "inserted" datetime default(CURRENT_TIMESTAMP),
  "display" varchar,
  "ignore_status" tinyint(1) not null default '0'
);
CREATE INDEX "devices_hostname_sysname_display_index" on "devices"(
  "hostname",
  "sysName",
  "display"
);
CREATE INDEX "devices_last_poll_attempted_index" on "devices"(
  "last_poll_attempted"
);
CREATE INDEX "devices_last_polled_index" on "devices"("last_polled");
CREATE INDEX "devices_os_index" on "devices"("os");
CREATE INDEX "devices_status_index" on "devices"("status");
CREATE INDEX "devices_sysname_index" on "devices"("sysName");
CREATE TABLE IF NOT EXISTS "vendor_ouis"(
  "id" integer primary key autoincrement not null,
  "vendor" varchar not null,
  "oui" varchar not null
);
CREATE UNIQUE INDEX "vendor_ouis_oui_unique" on "vendor_ouis"("oui");
CREATE TABLE IF NOT EXISTS "applications"(
  "app_id" integer primary key autoincrement not null,
  "device_id" integer not null,
  "app_type" varchar not null,
  "app_state" varchar not null default('UNKNOWN'),
  "discovered" integer not null default('0'),
  "app_state_prev" varchar,
  "app_status" varchar not null default '',
  "timestamp" datetime not null default(CURRENT_TIMESTAMP),
  "app_instance" varchar not null default '',
  "data" text,
  "deleted_at" datetime
);
CREATE UNIQUE INDEX "applications_device_id_app_type_unique" on "applications"(
  "device_id",
  "app_type"
);
CREATE TABLE IF NOT EXISTS "processes"(
  "id" integer primary key autoincrement not null,
  "device_id" integer not null,
  "pid" integer not null,
  "vsz" integer not null,
  "rss" integer not null,
  "cputime" varchar not null,
  "user" varchar not null,
  "command" text not null
);
CREATE INDEX "processes_device_id_index" on "processes"("device_id");
CREATE TABLE IF NOT EXISTS "bgpPeers"(
  "bgpPeer_id" integer primary key autoincrement not null,
  "device_id" integer not null,
  "astext" varchar not null,
  "bgpPeerIdentifier" text not null,
  "bgpPeerRemoteAs" integer not null,
  "bgpPeerState" text not null,
  "bgpPeerAdminStatus" text not null,
  "bgpLocalAddr" text not null,
  "bgpPeerRemoteAddr" text not null,
  "bgpPeerDescr" varchar not null default(''),
  "bgpPeerInUpdates" integer not null,
  "bgpPeerOutUpdates" integer not null,
  "bgpPeerInTotalMessages" integer not null,
  "bgpPeerOutTotalMessages" integer not null,
  "bgpPeerFsmEstablishedTime" integer not null,
  "bgpPeerInUpdateElapsedTime" integer not null,
  "context_name" varchar,
  "vrf_id" integer,
  "bgpPeerLastErrorCode" integer,
  "bgpPeerLastErrorSubCode" integer,
  "bgpPeerLastErrorText" varchar,
  "bgpPeerIface" integer
);
CREATE INDEX "bgppeers_device_id_context_name_index" on "bgpPeers"(
  "device_id",
  "context_name"
);
CREATE TABLE IF NOT EXISTS "bgpPeers_cbgp"(
  "device_id" integer not null,
  "bgpPeerIdentifier" varchar not null,
  "afi" varchar not null,
  "safi" varchar not null,
  "AcceptedPrefixes" integer not null,
  "DeniedPrefixes" integer not null,
  "PrefixAdminLimit" integer not null,
  "PrefixThreshold" integer not null,
  "PrefixClearThreshold" integer not null,
  "AdvertisedPrefixes" integer not null,
  "SuppressedPrefixes" integer not null,
  "WithdrawnPrefixes" integer not null,
  "AcceptedPrefixes_delta" integer not null,
  "AcceptedPrefixes_prev" integer not null,
  "DeniedPrefixes_delta" integer not null,
  "DeniedPrefixes_prev" integer not null,
  "AdvertisedPrefixes_delta" integer not null,
  "AdvertisedPrefixes_prev" integer not null,
  "SuppressedPrefixes_delta" integer not null,
  "SuppressedPrefixes_prev" integer not null,
  "WithdrawnPrefixes_delta" integer not null,
  "WithdrawnPrefixes_prev" integer not null,
  "context_name" varchar
);
CREATE UNIQUE INDEX "bgppeers_cbgp_device_id_bgppeeridentifier_afi_safi_unique" on "bgpPeers_cbgp"(
  "device_id",
  "bgpPeerIdentifier",
  "afi",
  "safi"
);
CREATE INDEX "bgppeers_cbgp_device_id_bgppeeridentifier_context_name_index" on "bgpPeers_cbgp"(
  "device_id",
  "bgpPeerIdentifier",
  "context_name"
);
CREATE TABLE IF NOT EXISTS "ospf_areas"(
  "id" integer primary key autoincrement not null,
  "device_id" integer not null,
  "ospfAreaId" varchar not null,
  "ospfAuthType" varchar,
  "ospfImportAsExtern" varchar not null,
  "ospfSpfRuns" integer not null,
  "ospfAreaBdrRtrCount" integer not null,
  "ospfAsBdrRtrCount" integer not null,
  "ospfAreaLsaCount" integer not null,
  "ospfAreaLsaCksumSum" integer not null,
  "ospfAreaSummary" varchar not null,
  "ospfAreaStatus" varchar not null,
  "context_name" varchar
);
CREATE UNIQUE INDEX "ospf_areas_device_id_ospfareaid_context_name_unique" on "ospf_areas"(
  "device_id",
  "ospfAreaId",
  "context_name"
);
CREATE TABLE IF NOT EXISTS "ospf_instances"(
  "id" integer primary key autoincrement not null,
  "device_id" integer not null,
  "ospf_instance_id" integer not null,
  "ospfRouterId" varchar not null,
  "ospfAdminStat" varchar not null,
  "ospfVersionNumber" varchar not null,
  "ospfAreaBdrRtrStatus" varchar not null,
  "ospfASBdrRtrStatus" varchar not null,
  "ospfExternLsaCount" integer not null,
  "ospfExternLsaCksumSum" integer not null,
  "ospfTOSSupport" varchar not null,
  "ospfOriginateNewLsas" integer not null,
  "ospfRxNewLsas" integer not null,
  "ospfExtLsdbLimit" integer,
  "ospfMulticastExtensions" integer,
  "ospfExitOverflowInterval" integer,
  "ospfDemandExtensions" varchar,
  "context_name" varchar
);
CREATE UNIQUE INDEX "ospf_instances_device_id_ospf_instance_id_context_name_unique" on "ospf_instances"(
  "device_id",
  "ospf_instance_id",
  "context_name"
);
CREATE TABLE IF NOT EXISTS "ospf_nbrs"(
  "id" integer primary key autoincrement not null,
  "device_id" integer not null,
  "port_id" integer,
  "ospf_nbr_id" varchar not null,
  "ospfNbrIpAddr" varchar not null,
  "ospfNbrAddressLessIndex" integer not null,
  "ospfNbrRtrId" varchar not null,
  "ospfNbrOptions" integer not null,
  "ospfNbrPriority" integer not null,
  "ospfNbrState" varchar not null,
  "ospfNbrEvents" integer not null,
  "ospfNbrLsRetransQLen" integer not null,
  "ospfNbmaNbrStatus" varchar not null,
  "ospfNbmaNbrPermanence" varchar not null,
  "ospfNbrHelloSuppressed" varchar not null,
  "context_name" varchar
);
CREATE UNIQUE INDEX "ospf_nbrs_device_id_ospf_nbr_id_context_name_unique" on "ospf_nbrs"(
  "device_id",
  "ospf_nbr_id",
  "context_name"
);
CREATE TABLE IF NOT EXISTS "ospf_ports"(
  "id" integer primary key autoincrement not null,
  "device_id" integer not null,
  "port_id" integer not null,
  "ospf_port_id" varchar not null,
  "ospfIfIpAddress" varchar not null,
  "ospfAddressLessIf" integer not null,
  "ospfIfAreaId" varchar not null,
  "ospfIfType" varchar,
  "ospfIfAdminStat" varchar,
  "ospfIfRtrPriority" integer,
  "ospfIfTransitDelay" integer,
  "ospfIfRetransInterval" integer,
  "ospfIfHelloInterval" integer,
  "ospfIfRtrDeadInterval" integer,
  "ospfIfPollInterval" integer,
  "ospfIfState" varchar,
  "ospfIfDesignatedRouter" varchar,
  "ospfIfBackupDesignatedRouter" varchar,
  "ospfIfEvents" integer,
  "ospfIfAuthKey" varchar,
  "ospfIfStatus" varchar,
  "ospfIfMulticastForwarding" varchar,
  "ospfIfDemand" varchar,
  "ospfIfAuthType" varchar,
  "context_name" varchar,
  "ospfIfMetricIpAddress" varchar,
  "ospfIfMetricAddressLessIf" integer,
  "ospfIfMetricTOS" integer,
  "ospfIfMetricValue" integer,
  "ospfIfMetricStatus" varchar
);
CREATE UNIQUE INDEX "ospf_ports_device_id_ospf_port_id_context_name_unique" on "ospf_ports"(
  "device_id",
  "ospf_port_id",
  "context_name"
);
CREATE TABLE IF NOT EXISTS "packages"(
  "pkg_id" integer primary key autoincrement not null,
  "device_id" integer not null,
  "name" varchar not null,
  "manager" varchar not null default('1'),
  "status" tinyint(1) not null,
  "version" varchar not null,
  "build" varchar not null,
  "arch" varchar not null,
  "size" integer
);
CREATE INDEX "packages_device_id_index" on "packages"("device_id");
CREATE UNIQUE INDEX "packages_device_id_name_manager_arch_version_build_unique" on "packages"(
  "device_id",
  "name",
  "manager",
  "arch",
  "version",
  "build"
);
CREATE TABLE IF NOT EXISTS "vminfo"(
  "id" integer primary key autoincrement not null,
  "device_id" integer not null,
  "vm_type" varchar not null default('vmware'),
  "vmwVmVMID" integer not null,
  "vmwVmDisplayName" varchar not null,
  "vmwVmGuestOS" varchar,
  "vmwVmMemSize" integer not null,
  "vmwVmCpus" integer not null,
  "vmwVmState" integer not null
);
CREATE INDEX "vminfo_device_id_index" on "vminfo"("device_id");
CREATE INDEX "vminfo_vmwvmvmid_index" on "vminfo"("vmwVmVMID");
CREATE TABLE IF NOT EXISTS "custom_maps"(
  "custom_map_id" integer primary key autoincrement not null,
  "name" varchar not null,
  "width" varchar not null,
  "height" varchar not null,
  "options" text,
  "newnodeconfig" text not null,
  "newedgeconfig" text not null,
  "created_at" datetime,
  "updated_at" datetime,
  "node_align" integer not null default '0',
  "reverse_arrows" tinyint(1) not null default '0',
  "edge_separation" integer not null default '10',
  "legend_x" integer not null default '-1',
  "legend_y" integer not null default '-1',
  "legend_steps" integer not null default '7',
  "legend_font_size" integer not null default '14',
  "legend_hide_invalid" tinyint(1) not null default '0',
  "legend_hide_overspeed" tinyint(1) not null default '0',
  "menu_group" varchar,
  "background_type" varchar not null default 'none',
  "background_data" text,
  "legend_colours" text
);
CREATE TABLE IF NOT EXISTS "custom_map_edges"(
  "custom_map_edge_id" integer primary key autoincrement not null,
  "custom_map_id" integer not null,
  "custom_map_node1_id" integer not null,
  "custom_map_node2_id" integer not null,
  "port_id" integer,
  "reverse" tinyint(1) not null,
  "style" varchar not null,
  "showpct" tinyint(1) not null,
  "text_face" varchar not null,
  "text_size" integer not null,
  "text_colour" varchar not null,
  "mid_x" integer not null,
  "mid_y" integer not null,
  "created_at" datetime,
  "updated_at" datetime,
  "showbps" tinyint(1) not null default '0',
  "label" varchar not null default '',
  "fixed_width" numeric,
  "text_align" varchar not null default 'horizontal',
  foreign key("custom_map_id") references "custom_maps"("custom_map_id") on delete cascade,
  foreign key("port_id") references "ports"("port_id") on delete set null,
  foreign key("custom_map_node1_id") references "custom_map_nodes"("custom_map_node_id") on delete cascade,
  foreign key("custom_map_node2_id") references "custom_map_nodes"("custom_map_node_id") on delete cascade
);
CREATE INDEX "custom_map_edges_custom_map_id_index" on "custom_map_edges"(
  "custom_map_id"
);
CREATE INDEX "custom_map_edges_custom_map_node1_id_index" on "custom_map_edges"(
  "custom_map_node1_id"
);
CREATE INDEX "custom_map_edges_custom_map_node2_id_index" on "custom_map_edges"(
  "custom_map_node2_id"
);
CREATE INDEX "custom_map_edges_port_id_index" on "custom_map_edges"("port_id");
CREATE TABLE IF NOT EXISTS "custom_map_backgrounds"(
  "custom_map_background_id" integer primary key autoincrement not null,
  "created_at" datetime,
  "updated_at" datetime,
  "custom_map_id" integer not null,
  "background_image" blob not null,
  foreign key("custom_map_id") references "custom_maps"("custom_map_id") on delete cascade
);
CREATE UNIQUE INDEX "custom_map_backgrounds_custom_map_id_unique" on "custom_map_backgrounds"(
  "custom_map_id"
);
CREATE TABLE IF NOT EXISTS "access_points"(
  "accesspoint_id" integer primary key autoincrement not null,
  "device_id" integer not null,
  "name" varchar not null,
  "radio_number" integer,
  "type" varchar not null,
  "mac_addr" varchar not null,
  "deleted" tinyint(1) not null default('0'),
  "channel" integer not null default('0'),
  "txpow" integer not null default('0'),
  "radioutil" integer not null default('0'),
  "numasoclients" integer not null default('0'),
  "nummonclients" integer not null default('0'),
  "numactbssid" integer not null default('0'),
  "nummonbssid" integer not null default '0',
  "interference" integer not null
);
CREATE INDEX "access_points_deleted_index" on "access_points"("deleted");
CREATE INDEX "name" on "access_points"("name", "radio_number");
CREATE VIEW view_port_mac_links
            AS
            -- Gets a list of port IDs for devices linked by MAC address
            SELECT
              p.port_id
              ,arp.id as ipv4_mac_id
              ,rp.port_id as remote_port_id
            FROM
              ports p
              -- Find all ARP entries for this port, excluding the static entries for the local IP
              JOIN ipv4_mac arp
                ON p.port_id=arp.port_id
                  AND arp.mac_address <> p.ifPhysAddress
              -- Find all IPv4 addresses on other devices that have the same IP as the ARP entry
              JOIN ipv4_addresses a
                ON a.ipv4_address=arp.ipv4_address
              -- Find the matching port if the MAC address matches
              JOIN
                ports rp ON a.port_id=rp.port_id
                  AND arp.mac_address=rp.ifPhysAddress
              WHERE
                arp.mac_address NOT IN ('000000000000', 'ffffffffffff')
/* view_port_mac_links(port_id,ipv4_mac_id,remote_port_id) */;
CREATE INDEX "bill_data_bill_id_timestamp_index" on "bill_data"(
  "bill_id",
  "timestamp"
);
CREATE TABLE IF NOT EXISTS "transceivers"(
  "id" integer primary key autoincrement not null,
  "created_at" datetime,
  "updated_at" datetime,
  "device_id" integer not null,
  "port_id" integer not null,
  "index" varchar not null,
  "entity_physical_index" integer,
  "type" varchar,
  "vendor" varchar,
  "oui" varchar,
  "model" varchar,
  "revision" varchar,
  "serial" varchar,
  "date" date,
  "ddm" tinyint(1),
  "encoding" varchar,
  "cable" varchar,
  "distance" integer,
  "wavelength" integer,
  "connector" varchar,
  "channels" integer not null default '1'
);
CREATE INDEX "transceivers_device_id_entity_physical_index_index" on "transceivers"(
  "device_id",
  "entity_physical_index"
);
CREATE TABLE IF NOT EXISTS "entPhysical"(
  "entPhysical_id" integer primary key autoincrement not null,
  "device_id" integer not null,
  "entPhysicalIndex" integer not null,
  "entPhysicalDescr" text,
  "entPhysicalClass" text,
  "entPhysicalName" text,
  "entPhysicalHardwareRev" varchar,
  "entPhysicalFirmwareRev" varchar,
  "entPhysicalSoftwareRev" varchar,
  "entPhysicalAlias" varchar,
  "entPhysicalAssetID" varchar,
  "entPhysicalIsFRU" varchar,
  "entPhysicalModelName" text,
  "entPhysicalVendorType" text,
  "entPhysicalSerialNum" text,
  "entPhysicalContainedIn" integer not null default('0'),
  "entPhysicalParentRelPos" integer not null default('-1'),
  "entPhysicalMfgName" text,
  "ifIndex" integer
);
CREATE INDEX "entphysical_device_id_index" on "entPhysical"("device_id");
CREATE TABLE IF NOT EXISTS "ports_statistics"(
  "port_id" integer primary key autoincrement not null,
  "ifInNUcastPkts" integer,
  "ifInNUcastPkts_prev" integer,
  "ifInNUcastPkts_delta" integer,
  "ifInNUcastPkts_rate" integer,
  "ifOutNUcastPkts" integer,
  "ifOutNUcastPkts_prev" integer,
  "ifOutNUcastPkts_delta" integer,
  "ifOutNUcastPkts_rate" integer,
  "ifInDiscards" integer,
  "ifInDiscards_prev" integer,
  "ifInDiscards_delta" integer,
  "ifInDiscards_rate" integer,
  "ifOutDiscards" integer,
  "ifOutDiscards_prev" integer,
  "ifOutDiscards_delta" integer,
  "ifOutDiscards_rate" integer,
  "ifInUnknownProtos" integer,
  "ifInUnknownProtos_prev" integer,
  "ifInUnknownProtos_delta" integer,
  "ifInUnknownProtos_rate" integer,
  "ifInBroadcastPkts" integer,
  "ifInBroadcastPkts_prev" integer,
  "ifInBroadcastPkts_delta" integer,
  "ifInBroadcastPkts_rate" integer,
  "ifOutBroadcastPkts" integer,
  "ifOutBroadcastPkts_prev" integer,
  "ifOutBroadcastPkts_delta" integer,
  "ifOutBroadcastPkts_rate" integer,
  "ifInMulticastPkts" integer,
  "ifInMulticastPkts_prev" integer,
  "ifInMulticastPkts_delta" integer,
  "ifInMulticastPkts_rate" integer,
  "ifOutMulticastPkts" integer,
  "ifOutMulticastPkts_prev" integer,
  "ifOutMulticastPkts_delta" integer,
  "ifOutMulticastPkts_rate" integer
);
CREATE INDEX "ports_vlans_port_id_index" on "ports_vlans"("port_id");
CREATE TABLE IF NOT EXISTS "custom_map_node_images"(
  "custom_map_node_image_id" integer primary key autoincrement not null,
  "created_at" datetime,
  "updated_at" datetime,
  "image" blob not null,
  "mime" varchar not null,
  "version" varchar not null,
  "name" varchar not null
);
CREATE TABLE IF NOT EXISTS "custom_map_nodes"(
  "custom_map_node_id" integer primary key autoincrement not null,
  "custom_map_id" integer not null,
  "device_id" integer,
  "label" varchar not null,
  "style" varchar not null,
  "icon" varchar,
  "size" integer not null,
  "border_width" integer not null,
  "text_face" varchar not null,
  "text_size" integer not null,
  "text_colour" varchar not null,
  "colour_bg" varchar,
  "colour_bdr" varchar,
  "x_pos" integer not null,
  "y_pos" integer not null,
  "created_at" datetime,
  "updated_at" datetime,
  "image" varchar not null default(''),
  "linked_custom_map_id" integer,
  "node_image_id" integer,
  foreign key("linked_custom_map_id") references custom_maps("custom_map_id") on delete set null on update no action,
  foreign key("device_id") references devices("device_id") on delete set null on update no action,
  foreign key("custom_map_id") references custom_maps("custom_map_id") on delete cascade on update no action,
  foreign key("node_image_id") references "custom_map_node_images"("custom_map_node_image_id")
);
CREATE INDEX "custom_map_nodes_custom_map_id_index" on "custom_map_nodes"(
  "custom_map_id"
);
CREATE INDEX "custom_map_nodes_device_id_index" on "custom_map_nodes"(
  "device_id"
);
CREATE INDEX "custom_map_nodes_linked_custom_map_id_index" on "custom_map_nodes"(
  "linked_custom_map_id"
);
CREATE INDEX "custom_map_nodes_node_image_id_index" on "custom_map_nodes"(
  "node_image_id"
);
CREATE TABLE IF NOT EXISTS "qos"(
  "qos_id" integer primary key autoincrement not null,
  "created_at" datetime,
  "updated_at" datetime,
  "device_id" integer not null,
  "port_id" integer,
  "parent_id" integer,
  "type" varchar not null,
  "title" varchar not null,
  "tooltip" text,
  "snmp_idx" varchar not null,
  "rrd_id" varchar not null,
  "ingress" tinyint(1) not null default '0',
  "egress" tinyint(1) not null default '0',
  "disabled" tinyint(1) not null default '0',
  "ignore" tinyint(1) not null default '0',
  "last_polled" integer,
  "max_in" integer,
  "max_out" integer,
  "last_bytes_in" integer,
  "last_bytes_out" integer,
  "bytes_in_rate" integer,
  "bytes_out_rate" integer,
  "last_bytes_drop_in" integer,
  "last_bytes_drop_out" integer,
  "bytes_drop_in_rate" integer,
  "bytes_drop_out_rate" integer,
  "last_packets_in" integer,
  "last_packets_out" integer,
  "packets_in_rate" integer,
  "packets_out_rate" integer,
  "last_packets_drop_in" integer,
  "last_packets_drop_out" integer,
  "packets_drop_in_rate" integer,
  "packets_drop_out_rate" integer,
  "bytes_drop_in_pct" numeric,
  "bytes_drop_out_pct" numeric,
  "packets_drop_in_pct" numeric,
  "packets_drop_out_pct" numeric,
  foreign key("device_id") references "devices"("device_id") on delete CASCADE,
  foreign key("port_id") references "ports"("port_id") on delete set null,
  foreign key("parent_id") references "qos"("qos_id") on delete set null
);
CREATE INDEX "qos_device_id_index" on "qos"("device_id");
CREATE INDEX "qos_port_id_index" on "qos"("port_id");
CREATE INDEX "qos_parent_id_index" on "qos"("parent_id");
CREATE INDEX "qos_type_index" on "qos"("type");
CREATE TABLE IF NOT EXISTS "mpls_sdp_binds"(
  "bind_id" integer primary key autoincrement not null,
  "sdp_id" integer not null,
  "svc_id" integer not null,
  "sdp_oid" integer not null,
  "svc_oid" integer not null,
  "device_id" integer not null,
  "sdpBindRowStatus" varchar,
  "sdpBindAdminStatus" varchar,
  "sdpBindOperStatus" varchar,
  "sdpBindLastMgmtChange" integer,
  "sdpBindLastStatusChange" integer,
  "sdpBindType" varchar,
  "sdpBindVcType" varchar,
  "sdpBindBaseStatsIngFwdPackets" integer,
  "sdpBindBaseStatsIngFwdOctets" integer,
  "sdpBindBaseStatsEgrFwdPackets" integer,
  "sdpBindBaseStatsEgrFwdOctets" integer
);
CREATE INDEX "mpls_sdp_binds_device_id_index" on "mpls_sdp_binds"("device_id");
CREATE INDEX "alert_log_device_id_rule_id_time_logged_index" on "alert_log"(
  "device_id",
  "rule_id",
  "time_logged"
);
CREATE INDEX "ports_device_id_ifindex_index" on "ports"(
  "device_id",
  "ifIndex"
);
CREATE TABLE IF NOT EXISTS "ipv6_nd"(
  "id" integer primary key autoincrement not null,
  "created_at" datetime,
  "updated_at" datetime,
  "port_id" integer not null,
  "device_id" integer not null,
  "mac_address" varchar not null,
  "ipv6_address" varchar not null,
  "context_name" varchar
);
CREATE TABLE IF NOT EXISTS "permissions"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "guard_name" varchar not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "permissions_name_guard_name_unique" on "permissions"(
  "name",
  "guard_name"
);
CREATE TABLE IF NOT EXISTS "roles"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "guard_name" varchar not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "roles_name_guard_name_unique" on "roles"(
  "name",
  "guard_name"
);
CREATE TABLE IF NOT EXISTS "model_has_permissions"(
  "permission_id" integer not null,
  "model_type" varchar not null,
  "model_id" integer not null,
  foreign key("permission_id") references "permissions"("id") on delete cascade,
  primary key("permission_id", "model_id", "model_type")
);
CREATE INDEX "model_has_permissions_model_id_model_type_index" on "model_has_permissions"(
  "model_id",
  "model_type"
);
CREATE TABLE IF NOT EXISTS "model_has_roles"(
  "role_id" integer not null,
  "model_type" varchar not null,
  "model_id" integer not null,
  foreign key("role_id") references "roles"("id") on delete cascade,
  primary key("role_id", "model_id", "model_type")
);
CREATE INDEX "model_has_roles_model_id_model_type_index" on "model_has_roles"(
  "model_id",
  "model_type"
);
CREATE TABLE IF NOT EXISTS "role_has_permissions"(
  "permission_id" integer not null,
  "role_id" integer not null,
  foreign key("permission_id") references "permissions"("id") on delete cascade,
  foreign key("role_id") references "roles"("id") on delete cascade,
  primary key("permission_id", "role_id")
);
CREATE TABLE IF NOT EXISTS "ospfv3_areas"(
  "id" integer primary key autoincrement not null,
  "device_id" integer not null,
  "ospfv3_instance_id" integer not null,
  "ospfv3AreaId" integer not null,
  "ospfv3AreaImportAsExtern" varchar not null,
  "ospfv3AreaSpfRuns" integer not null,
  "ospfv3AreaBdrRtrCount" integer not null,
  "ospfv3AreaAsBdrRtrCount" integer not null,
  "ospfv3AreaScopeLsaCount" integer not null,
  "ospfv3AreaScopeLsaCksumSum" integer not null,
  "ospfv3AreaSummary" varchar not null,
  "ospfv3AreaStubMetric" integer not null,
  "ospfv3AreaStubMetricType" varchar not null,
  "ospfv3AreaNssaTranslatorRole" varchar not null,
  "ospfv3AreaNssaTranslatorState" varchar not null,
  "ospfv3AreaNssaTranslatorStabInterval" integer not null,
  "ospfv3AreaNssaTranslatorEvents" integer not null,
  "ospfv3AreaTEEnabled" varchar not null,
  "context_name" varchar not null
);
CREATE UNIQUE INDEX "ospfv3_areas_device_id_ospfv3areaid_context_name_unique" on "ospfv3_areas"(
  "device_id",
  "ospfv3AreaId",
  "context_name"
);
CREATE INDEX "ospfv3AreaId" on "ospfv3_areas"("ospfv3_instance_id");
CREATE TABLE IF NOT EXISTS "ospfv3_instances"(
  "id" integer primary key autoincrement not null,
  "device_id" integer not null,
  "router_id" varchar not null,
  "ospfv3RouterId" integer not null,
  "ospfv3AdminStatus" varchar not null,
  "ospfv3VersionNumber" varchar not null,
  "ospfv3AreaBdrRtrStatus" varchar not null,
  "ospfv3ASBdrRtrStatus" varchar not null,
  "ospfv3OriginateNewLsas" integer not null,
  "ospfv3RxNewLsas" integer not null,
  "ospfv3ExtLsaCount" integer not null,
  "ospfv3ExtAreaLsdbLimit" integer not null,
  "ospfv3AsScopeLsaCount" integer not null,
  "ospfv3AsScopeLsaCksumSum" integer not null,
  "ospfv3ExitOverflowInterval" integer not null,
  "ospfv3ReferenceBandwidth" integer not null,
  "ospfv3RestartSupport" varchar not null,
  "ospfv3RestartInterval" integer not null,
  "ospfv3RestartStrictLsaChecking" varchar not null,
  "ospfv3RestartStatus" varchar not null,
  "ospfv3RestartAge" integer not null,
  "ospfv3RestartExitReason" varchar not null,
  "ospfv3StubRouterSupport" varchar not null,
  "ospfv3StubRouterAdvertisement" varchar not null,
  "ospfv3DiscontinuityTime" integer not null,
  "ospfv3RestartTime" integer not null,
  "context_name" varchar
);
CREATE UNIQUE INDEX "ospfv3_instances_device_id_context_name_unique" on "ospfv3_instances"(
  "device_id",
  "context_name"
);
CREATE TABLE IF NOT EXISTS "ospfv3_nbrs"(
  "id" integer primary key autoincrement not null,
  "device_id" integer not null,
  "ospfv3_instance_id" integer not null,
  "port_id" integer,
  "router_id" varchar not null,
  "ospfv3NbrIfIndex" integer not null,
  "ospfv3NbrIfInstId" integer not null,
  "ospfv3NbrRtrId" integer not null,
  "ospfv3NbrAddressType" varchar not null,
  "ospfv3NbrAddress" varchar not null,
  "ospfv3NbrOptions" integer not null,
  "ospfv3NbrPriority" integer not null,
  "ospfv3NbrState" varchar not null,
  "ospfv3NbrEvents" integer not null,
  "ospfv3NbrLsRetransQLen" integer not null,
  "ospfv3NbrHelloSuppressed" varchar not null,
  "ospfv3NbrIfId" integer not null,
  "ospfv3NbrRestartHelperStatus" varchar not null,
  "ospfv3NbrRestartHelperAge" integer not null,
  "ospfv3NbrRestartHelperExitReason" varchar not null,
  "context_name" varchar
);
CREATE UNIQUE INDEX "ospfv3_nbrs_device_id_index_context_name_unique" on "ospfv3_nbrs"(
  "device_id",
  "ospfv3NbrIfIndex",
  "ospfv3NbrIfInstId",
  "ospfv3NbrRtrId",
  "context_name"
);
CREATE INDEX "ospfv3_nbrs_ospfv3_instance_id_index" on "ospfv3_nbrs"(
  "ospfv3_instance_id"
);
CREATE TABLE IF NOT EXISTS "ospfv3_ports"(
  "id" integer primary key autoincrement not null,
  "device_id" integer not null,
  "ospfv3_instance_id" integer not null,
  "ospfv3_area_id" integer,
  "port_id" integer,
  "ospfv3IfIndex" integer not null,
  "ospfv3IfInstId" integer not null,
  "ospfv3IfAreaId" integer not null,
  "ospfv3IfType" varchar not null,
  "ospfv3IfAdminStatus" varchar not null,
  "ospfv3IfRtrPriority" integer not null,
  "ospfv3IfTransitDelay" integer not null,
  "ospfv3IfRetransInterval" integer not null,
  "ospfv3IfHelloInterval" integer not null,
  "ospfv3IfRtrDeadInterval" integer not null,
  "ospfv3IfPollInterval" integer not null,
  "ospfv3IfState" varchar not null,
  "ospfv3IfDesignatedRouter" varchar not null,
  "ospfv3IfBackupDesignatedRouter" varchar not null,
  "ospfv3IfEvents" integer not null,
  "ospfv3IfDemand" varchar not null,
  "ospfv3IfMetricValue" integer not null,
  "ospfv3IfLinkScopeLsaCount" integer,
  "ospfv3IfLinkLsaCksumSum" integer,
  "ospfv3IfDemandNbrProbe" varchar,
  "ospfv3IfDemandNbrProbeRetransLimit" integer,
  "ospfv3IfDemandNbrProbeInterval" integer,
  "ospfv3IfTEDisabled" varchar,
  "ospfv3IfLinkLSASuppression" varchar,
  "context_name" varchar
);
CREATE UNIQUE INDEX "ospfv3_ports_device_id_index_context_name_unique" on "ospfv3_ports"(
  "device_id",
  "ospfv3IfIndex",
  "ospfv3IfInstId",
  "context_name"
);
CREATE INDEX "ospfv3_area_id" on "ospfv3_ports"("ospfv3_instance_id");
CREATE TABLE IF NOT EXISTS "ipv6_addresses"(
  "ipv6_address_id" integer primary key autoincrement not null,
  "ipv6_address" varchar not null,
  "ipv6_compressed" varchar not null,
  "ipv6_prefixlen" integer not null,
  "ipv6_origin" varchar not null,
  "ipv6_network_id" integer not null default '0',
  "port_id" integer not null,
  "context_name" varchar
);
CREATE INDEX "ipv6_addresses_port_id_index" on "ipv6_addresses"("port_id");
CREATE TABLE IF NOT EXISTS "locations"(
  "id" integer primary key autoincrement not null,
  "location" varchar not null,
  "lat" numeric,
  "lng" numeric,
  "timestamp" datetime not null,
  "fixed_coordinates" tinyint(1) not null default('0')
);
CREATE UNIQUE INDEX "locations_location_unique" on "locations"("location");
CREATE TABLE IF NOT EXISTS "slas"(
  "sla_id" integer primary key autoincrement not null,
  "device_id" integer not null,
  "sla_nr" integer not null,
  "owner" varchar not null,
  "tag" varchar not null,
  "rtt_type" varchar not null,
  "status" tinyint(1) not null,
  "deleted" tinyint(1) not null default('0'),
  "rtt" double,
  "opstatus" integer not null default('0')
);
CREATE INDEX "slas_device_id_index" on "slas"("device_id");
CREATE UNIQUE INDEX "slas_device_id_sla_nr_unique" on "slas"(
  "device_id",
  "sla_nr"
);
CREATE TABLE IF NOT EXISTS "poller_cluster_stats"(
  "id" integer primary key autoincrement not null,
  "parent_poller" integer not null default('0'),
  "poller_type" varchar not null default(''),
  "depth" integer not null,
  "devices" integer not null,
  "worker_seconds" double not null,
  "workers" integer not null,
  "frequency" integer not null
);
CREATE UNIQUE INDEX "poller_cluster_stats_parent_poller_poller_type_unique" on "poller_cluster_stats"(
  "parent_poller",
  "poller_type"
);
CREATE INDEX "ipv4_mac_device_id_index" on "ipv4_mac"("device_id");
CREATE INDEX "ipv6_nd_port_id_index" on "ipv6_nd"("port_id");
CREATE INDEX "ipv6_nd_device_id_index" on "ipv6_nd"("device_id");

INSERT INTO migrations VALUES(1,'2018_07_03_091314_create_access_points_table',1);
INSERT INTO migrations VALUES(2,'2018_07_03_091314_create_alert_device_map_table',1);
INSERT INTO migrations VALUES(3,'2018_07_03_091314_create_alert_group_map_table',1);
INSERT INTO migrations VALUES(4,'2018_07_03_091314_create_alert_log_table',1);
INSERT INTO migrations VALUES(5,'2018_07_03_091314_create_alert_rules_table',1);
INSERT INTO migrations VALUES(6,'2018_07_03_091314_create_alert_schedulables_table',1);
INSERT INTO migrations VALUES(7,'2018_07_03_091314_create_alert_schedule_table',1);
INSERT INTO migrations VALUES(8,'2018_07_03_091314_create_alert_template_map_table',1);
INSERT INTO migrations VALUES(9,'2018_07_03_091314_create_alert_templates_table',1);
INSERT INTO migrations VALUES(10,'2018_07_03_091314_create_alert_transport_groups_table',1);
INSERT INTO migrations VALUES(11,'2018_07_03_091314_create_alert_transport_map_table',1);
INSERT INTO migrations VALUES(12,'2018_07_03_091314_create_alert_transports_table',1);
INSERT INTO migrations VALUES(13,'2018_07_03_091314_create_alerts_table',1);
INSERT INTO migrations VALUES(14,'2018_07_03_091314_create_api_tokens_table',1);
INSERT INTO migrations VALUES(15,'2018_07_03_091314_create_application_metrics_table',1);
INSERT INTO migrations VALUES(16,'2018_07_03_091314_create_applications_table',1);
INSERT INTO migrations VALUES(17,'2018_07_03_091314_create_authlog_table',1);
INSERT INTO migrations VALUES(18,'2018_07_03_091314_create_bgpPeers_cbgp_table',1);
INSERT INTO migrations VALUES(19,'2018_07_03_091314_create_bgpPeers_table',1);
INSERT INTO migrations VALUES(20,'2018_07_03_091314_create_bill_data_table',1);
INSERT INTO migrations VALUES(21,'2018_07_03_091314_create_bill_history_table',1);
INSERT INTO migrations VALUES(22,'2018_07_03_091314_create_bill_perms_table',1);
INSERT INTO migrations VALUES(23,'2018_07_03_091314_create_bill_port_counters_table',1);
INSERT INTO migrations VALUES(24,'2018_07_03_091314_create_bill_ports_table',1);
INSERT INTO migrations VALUES(25,'2018_07_03_091314_create_bills_table',1);
INSERT INTO migrations VALUES(26,'2018_07_03_091314_create_callback_table',1);
INSERT INTO migrations VALUES(27,'2018_07_03_091314_create_cef_switching_table',1);
INSERT INTO migrations VALUES(28,'2018_07_03_091314_create_ciscoASA_table',1);
INSERT INTO migrations VALUES(29,'2018_07_03_091314_create_component_prefs_table',1);
INSERT INTO migrations VALUES(30,'2018_07_03_091314_create_component_statuslog_table',1);
INSERT INTO migrations VALUES(31,'2018_07_03_091314_create_component_table',1);
INSERT INTO migrations VALUES(32,'2018_07_03_091314_create_config_table',1);
INSERT INTO migrations VALUES(33,'2018_07_03_091314_create_customers_table',1);
INSERT INTO migrations VALUES(34,'2018_07_03_091314_create_dashboards_table',1);
INSERT INTO migrations VALUES(35,'2018_07_03_091314_create_dbSchema_table',1);
INSERT INTO migrations VALUES(36,'2018_07_03_091314_create_device_graphs_table',1);
INSERT INTO migrations VALUES(37,'2018_07_03_091314_create_device_group_device_table',1);
INSERT INTO migrations VALUES(38,'2018_07_03_091314_create_device_groups_table',1);
INSERT INTO migrations VALUES(39,'2018_07_03_091314_create_device_mibs_table',1);
INSERT INTO migrations VALUES(40,'2018_07_03_091314_create_device_oids_table',1);
INSERT INTO migrations VALUES(41,'2018_07_03_091314_create_device_perf_table',1);
INSERT INTO migrations VALUES(42,'2018_07_03_091314_create_device_relationships_table',1);
INSERT INTO migrations VALUES(43,'2018_07_03_091314_create_devices_attribs_table',1);
INSERT INTO migrations VALUES(44,'2018_07_03_091314_create_devices_perms_table',1);
INSERT INTO migrations VALUES(45,'2018_07_03_091314_create_devices_table',1);
INSERT INTO migrations VALUES(46,'2018_07_03_091314_create_entPhysical_state_table',1);
INSERT INTO migrations VALUES(47,'2018_07_03_091314_create_entPhysical_table',1);
INSERT INTO migrations VALUES(48,'2018_07_03_091314_create_entityState_table',1);
INSERT INTO migrations VALUES(49,'2018_07_03_091314_create_eventlog_table',1);
INSERT INTO migrations VALUES(50,'2018_07_03_091314_create_graph_types_table',1);
INSERT INTO migrations VALUES(51,'2018_07_03_091314_create_hrDevice_table',1);
INSERT INTO migrations VALUES(52,'2018_07_03_091314_create_ipsec_tunnels_table',1);
INSERT INTO migrations VALUES(53,'2018_07_03_091314_create_ipv4_addresses_table',1);
INSERT INTO migrations VALUES(54,'2018_07_03_091314_create_ipv4_mac_table',1);
INSERT INTO migrations VALUES(55,'2018_07_03_091314_create_ipv4_networks_table',1);
INSERT INTO migrations VALUES(56,'2018_07_03_091314_create_ipv6_addresses_table',1);
INSERT INTO migrations VALUES(57,'2018_07_03_091314_create_ipv6_networks_table',1);
INSERT INTO migrations VALUES(58,'2018_07_03_091314_create_juniAtmVp_table',1);
INSERT INTO migrations VALUES(59,'2018_07_03_091314_create_links_table',1);
INSERT INTO migrations VALUES(60,'2018_07_03_091314_create_loadbalancer_rservers_table',1);
INSERT INTO migrations VALUES(61,'2018_07_03_091314_create_loadbalancer_vservers_table',1);
INSERT INTO migrations VALUES(62,'2018_07_03_091314_create_locations_table',1);
INSERT INTO migrations VALUES(63,'2018_07_03_091314_create_mac_accounting_table',1);
INSERT INTO migrations VALUES(64,'2018_07_03_091314_create_mefinfo_table',1);
INSERT INTO migrations VALUES(65,'2018_07_03_091314_create_mempools_table',1);
INSERT INTO migrations VALUES(66,'2018_07_03_091314_create_mibdefs_table',1);
INSERT INTO migrations VALUES(67,'2018_07_03_091314_create_munin_plugins_ds_table',1);
INSERT INTO migrations VALUES(68,'2018_07_03_091314_create_munin_plugins_table',1);
INSERT INTO migrations VALUES(69,'2018_07_03_091314_create_netscaler_vservers_table',1);
INSERT INTO migrations VALUES(70,'2018_07_03_091314_create_notifications_attribs_table',1);
INSERT INTO migrations VALUES(71,'2018_07_03_091314_create_notifications_table',1);
INSERT INTO migrations VALUES(72,'2018_07_03_091314_create_ospf_areas_table',1);
INSERT INTO migrations VALUES(73,'2018_07_03_091314_create_ospf_instances_table',1);
INSERT INTO migrations VALUES(74,'2018_07_03_091314_create_ospf_nbrs_table',1);
INSERT INTO migrations VALUES(75,'2018_07_03_091314_create_ospf_ports_table',1);
INSERT INTO migrations VALUES(76,'2018_07_03_091314_create_packages_table',1);
INSERT INTO migrations VALUES(77,'2018_07_03_091314_create_pdb_ix_peers_table',1);
INSERT INTO migrations VALUES(78,'2018_07_03_091314_create_pdb_ix_table',1);
INSERT INTO migrations VALUES(79,'2018_07_03_091314_create_perf_times_table',1);
INSERT INTO migrations VALUES(80,'2018_07_03_091314_create_plugins_table',1);
INSERT INTO migrations VALUES(81,'2018_07_03_091314_create_poller_cluster_stats_table',1);
INSERT INTO migrations VALUES(82,'2018_07_03_091314_create_poller_cluster_table',1);
INSERT INTO migrations VALUES(83,'2018_07_03_091314_create_poller_groups_table',1);
INSERT INTO migrations VALUES(84,'2018_07_03_091314_create_pollers_table',1);
INSERT INTO migrations VALUES(85,'2018_07_03_091314_create_ports_adsl_table',1);
INSERT INTO migrations VALUES(86,'2018_07_03_091314_create_ports_fdb_table',1);
INSERT INTO migrations VALUES(87,'2018_07_03_091314_create_ports_nac_table',1);
INSERT INTO migrations VALUES(88,'2018_07_03_091314_create_ports_perms_table',1);
INSERT INTO migrations VALUES(89,'2018_07_03_091314_create_ports_stack_table',1);
INSERT INTO migrations VALUES(90,'2018_07_03_091314_create_ports_statistics_table',1);
INSERT INTO migrations VALUES(91,'2018_07_03_091314_create_ports_stp_table',1);
INSERT INTO migrations VALUES(92,'2018_07_03_091314_create_ports_table',1);
INSERT INTO migrations VALUES(93,'2018_07_03_091314_create_ports_vlans_table',1);
INSERT INTO migrations VALUES(94,'2018_07_03_091314_create_processes_table',1);
INSERT INTO migrations VALUES(95,'2018_07_03_091314_create_processors_table',1);
INSERT INTO migrations VALUES(96,'2018_07_03_091314_create_proxmox_ports_table',1);
INSERT INTO migrations VALUES(97,'2018_07_03_091314_create_proxmox_table',1);
INSERT INTO migrations VALUES(98,'2018_07_03_091314_create_pseudowires_table',1);
INSERT INTO migrations VALUES(99,'2018_07_03_091314_create_route_table',1);
INSERT INTO migrations VALUES(100,'2018_07_03_091314_create_sensors_table',1);
INSERT INTO migrations VALUES(101,'2018_07_03_091314_create_sensors_to_state_indexes_table',1);
INSERT INTO migrations VALUES(102,'2018_07_03_091314_create_services_table',1);
INSERT INTO migrations VALUES(103,'2018_07_03_091314_create_session_table',1);
INSERT INTO migrations VALUES(104,'2018_07_03_091314_create_slas_table',1);
INSERT INTO migrations VALUES(105,'2018_07_03_091314_create_state_indexes_table',1);
INSERT INTO migrations VALUES(106,'2018_07_03_091314_create_state_translations_table',1);
INSERT INTO migrations VALUES(107,'2018_07_03_091314_create_storage_table',1);
INSERT INTO migrations VALUES(108,'2018_07_03_091314_create_stp_table',1);
INSERT INTO migrations VALUES(109,'2018_07_03_091314_create_syslog_table',1);
INSERT INTO migrations VALUES(110,'2018_07_03_091314_create_tnmsneinfo_table',1);
INSERT INTO migrations VALUES(111,'2018_07_03_091314_create_toner_table',1);
INSERT INTO migrations VALUES(112,'2018_07_03_091314_create_transport_group_transport_table',1);
INSERT INTO migrations VALUES(113,'2018_07_03_091314_create_ucd_diskio_table',1);
INSERT INTO migrations VALUES(114,'2018_07_03_091314_create_users_prefs_table',1);
INSERT INTO migrations VALUES(115,'2018_07_03_091314_create_users_table',1);
INSERT INTO migrations VALUES(116,'2018_07_03_091314_create_users_widgets_table',1);
INSERT INTO migrations VALUES(117,'2018_07_03_091314_create_vlans_table',1);
INSERT INTO migrations VALUES(118,'2018_07_03_091314_create_vminfo_table',1);
INSERT INTO migrations VALUES(119,'2018_07_03_091314_create_vrf_lite_cisco_table',1);
INSERT INTO migrations VALUES(120,'2018_07_03_091314_create_vrfs_table',1);
INSERT INTO migrations VALUES(121,'2018_07_03_091314_create_widgets_table',1);
INSERT INTO migrations VALUES(122,'2018_07_03_091314_create_wireless_sensors_table',1);
INSERT INTO migrations VALUES(123,'2018_07_03_091322_add_foreign_keys_to_component_prefs_table',1);
INSERT INTO migrations VALUES(124,'2018_07_03_091322_add_foreign_keys_to_component_statuslog_table',1);
INSERT INTO migrations VALUES(125,'2018_07_03_091322_add_foreign_keys_to_device_group_device_table',1);
INSERT INTO migrations VALUES(126,'2018_07_03_091322_add_foreign_keys_to_device_relationships_table',1);
INSERT INTO migrations VALUES(127,'2018_07_03_091322_add_foreign_keys_to_sensors_table',1);
INSERT INTO migrations VALUES(128,'2018_07_03_091322_add_foreign_keys_to_sensors_to_state_indexes_table',1);
INSERT INTO migrations VALUES(129,'2018_07_03_091322_add_foreign_keys_to_wireless_sensors_table',1);
INSERT INTO migrations VALUES(130,'2019_01_16_132200_add_vlan_and_elapsed_to_nac',1);
INSERT INTO migrations VALUES(131,'2019_01_16_195644_add_vrf_id_and_bgpLocalAs',1);
INSERT INTO migrations VALUES(132,'2019_02_05_140857_remove_config_definition_from_db',1);
INSERT INTO migrations VALUES(133,'2019_02_10_220000_add_dates_to_fdb',1);
INSERT INTO migrations VALUES(134,'2019_04_22_220000_update_route_table',1);
INSERT INTO migrations VALUES(135,'2019_05_12_202407_create_mpls_lsps_table',1);
INSERT INTO migrations VALUES(136,'2019_05_12_202408_create_mpls_lsp_paths_table',1);
INSERT INTO migrations VALUES(137,'2019_05_30_225937_device_groups_rewrite',1);
INSERT INTO migrations VALUES(138,'2019_06_30_190400_create_mpls_sdps_table',1);
INSERT INTO migrations VALUES(139,'2019_06_30_190401_create_mpls_sdp_binds_table',1);
INSERT INTO migrations VALUES(140,'2019_06_30_190402_create_mpls_services_table',1);
INSERT INTO migrations VALUES(141,'2019_07_03_132417_create_mpls_saps_table',1);
INSERT INTO migrations VALUES(142,'2019_07_09_150217_update_users_widgets_settings',1);
INSERT INTO migrations VALUES(143,'2019_08_10_223200_add_enabled_to_users',1);
INSERT INTO migrations VALUES(144,'2019_08_28_105051_fix-template-linefeeds',1);
INSERT INTO migrations VALUES(145,'2019_09_05_153524_create_notifications_attribs_index',1);
INSERT INTO migrations VALUES(146,'2019_09_29_114433_change_default_mempool_perc_warn_in_mempools_table',1);
INSERT INTO migrations VALUES(147,'2019_10_03_211702_serialize_config',1);
INSERT INTO migrations VALUES(148,'2019_10_21_105350_devices_group_perms',1);
INSERT INTO migrations VALUES(149,'2019_11_30_191013_create_mpls_tunnel_ar_hops_table',1);
INSERT INTO migrations VALUES(150,'2019_11_30_191013_create_mpls_tunnel_c_hops_table',1);
INSERT INTO migrations VALUES(151,'2019_12_01_165514_add_indexes_to_mpls_lsp_paths_table',1);
INSERT INTO migrations VALUES(152,'2019_12_05_164700_alerts_disable_on_update_current_timestamp',1);
INSERT INTO migrations VALUES(153,'2019_12_16_140000_create_customoids_table',1);
INSERT INTO migrations VALUES(154,'2019_12_17_151314_add_invert_map_to_alert_rules',1);
INSERT INTO migrations VALUES(155,'2019_12_28_180000_add_overwrite_ip_to_devices',1);
INSERT INTO migrations VALUES(156,'2020_01_09_1300_migrate_devices_attribs_table',1);
INSERT INTO migrations VALUES(157,'2020_01_10_075852_alter_mpls_lsp_paths_table',1);
INSERT INTO migrations VALUES(158,'2020_02_05_093457_add_inserted_to_devices',1);
INSERT INTO migrations VALUES(159,'2020_02_05_224042_device_inserted_null',1);
INSERT INTO migrations VALUES(160,'2020_02_10_223323_create_alert_location_map_table',1);
INSERT INTO migrations VALUES(161,'2020_03_24_0844_add_primary_key_to_device_graphs',1);
INSERT INTO migrations VALUES(162,'2020_03_25_165300_add_column_to_ports',1);
INSERT INTO migrations VALUES(163,'2020_04_06_001048_the_great_index_rename',1);
INSERT INTO migrations VALUES(164,'2020_04_08_172357_alert_schedule_utc',1);
INSERT INTO migrations VALUES(165,'2020_04_13_150500_add_last_error_fields_to_bgp_peers',1);
INSERT INTO migrations VALUES(166,'2020_04_19_010532_eventlog_sensor_reference_cleanup',1);
INSERT INTO migrations VALUES(167,'2020_05_22_020303_alter_metric_column',1);
INSERT INTO migrations VALUES(168,'2020_05_24_212054_poller_cluster_settings',1);
INSERT INTO migrations VALUES(169,'2020_05_30_162638_remove_mib_polling_tables',1);
INSERT INTO migrations VALUES(170,'2020_06_06_222222_create_device_outages_table',1);
INSERT INTO migrations VALUES(171,'2020_06_23_00522_alter_availability_perc_column',1);
INSERT INTO migrations VALUES(172,'2020_06_24_155119_drop_ports_if_high_speed',1);
INSERT INTO migrations VALUES(173,'2020_07_27_00522_alter_devices_snmp_algo_columns',1);
INSERT INTO migrations VALUES(174,'2020_07_29_143221_add_device_perf_index',1);
INSERT INTO migrations VALUES(175,'2020_08_28_212054_drop_uptime_column_outages',1);
INSERT INTO migrations VALUES(176,'2020_09_18_223431_create_cache_table',1);
INSERT INTO migrations VALUES(177,'2020_09_18_230114_create_service_templates_device_group_table',1);
INSERT INTO migrations VALUES(178,'2020_09_18_230114_create_service_templates_device_table',1);
INSERT INTO migrations VALUES(179,'2020_09_18_230114_create_service_templates_table',1);
INSERT INTO migrations VALUES(180,'2020_09_18_230114_extend_services_table_for_service_templates_table',1);
INSERT INTO migrations VALUES(181,'2020_09_19_230114_add_foreign_keys_to_service_templates_device_group_table',1);
INSERT INTO migrations VALUES(182,'2020_09_19_230114_add_foreign_keys_to_service_templates_device_table',1);
INSERT INTO migrations VALUES(183,'2020_09_22_172321_add_alert_log_index',1);
INSERT INTO migrations VALUES(184,'2020_09_24_000500_create_cache_locks_table',1);
INSERT INTO migrations VALUES(185,'2020_10_03_1000_add_primary_key_bill_perms',1);
INSERT INTO migrations VALUES(186,'2020_10_03_1000_add_primary_key_bill_ports',1);
INSERT INTO migrations VALUES(187,'2020_10_03_1000_add_primary_key_devices_perms',1);
INSERT INTO migrations VALUES(188,'2020_10_03_1000_add_primary_key_entPhysical_state',1);
INSERT INTO migrations VALUES(189,'2020_10_03_1000_add_primary_key_ipv4_mac',1);
INSERT INTO migrations VALUES(190,'2020_10_03_1000_add_primary_key_juniAtmVp',1);
INSERT INTO migrations VALUES(191,'2020_10_03_1000_add_primary_key_loadbalancer_vservers',1);
INSERT INTO migrations VALUES(192,'2020_10_03_1000_add_primary_key_ports_perms',1);
INSERT INTO migrations VALUES(193,'2020_10_03_1000_add_primary_key_processes',1);
INSERT INTO migrations VALUES(194,'2020_10_03_1000_add_primary_key_transport_group_transport',1);
INSERT INTO migrations VALUES(195,'2020_10_12_095504_mempools_add_oids',1);
INSERT INTO migrations VALUES(196,'2020_10_21_124101_allow_nullable_ospf_columns',1);
INSERT INTO migrations VALUES(197,'2020_10_30_093601_add_tos_to_ospf_ports',1);
INSERT INTO migrations VALUES(198,'2020_11_02_164331_add_powerstate_enum_to_vminfo',1);
INSERT INTO migrations VALUES(199,'2020_12_14_091314_create_port_group_port_table',1);
INSERT INTO migrations VALUES(200,'2020_12_14_091314_create_port_groups_table',1);
INSERT INTO migrations VALUES(201,'2021_02_08_224355_fix_invalid_dates',1);
INSERT INTO migrations VALUES(202,'2021_02_09_084318_remove_perf_times',1);
INSERT INTO migrations VALUES(203,'2021_02_09_122930_migrate_to_utf8mb4',1);
INSERT INTO migrations VALUES(204,'2021_02_21_203415_location_add_fixed_coordinates_flag',1);
INSERT INTO migrations VALUES(205,'2021_03_11_003540_rename_toner_table',1);
INSERT INTO migrations VALUES(206,'2021_03_11_003713_rename_printer_columns',1);
INSERT INTO migrations VALUES(207,'2021_03_17_160729_service_templates_cleanup',1);
INSERT INTO migrations VALUES(208,'2021_03_26_014054_change_cache_to_mediumtext',1);
INSERT INTO migrations VALUES(209,'2021_04_08_151101_add_foreign_keys_to_port_group_port_table',1);
INSERT INTO migrations VALUES(210,'2021_06_07_123600_create_sessions_table',1);
INSERT INTO migrations VALUES(211,'2021_06_11_084830_slas_add_rtt_field',1);
INSERT INTO migrations VALUES(212,'2021_07_06_1845_alter_bill_history_max_min',1);
INSERT INTO migrations VALUES(213,'2021_07_28_102443_plugins_add_version_and_settings',1);
INSERT INTO migrations VALUES(214,'2021_08_04_102914_add_syslog_indexes',1);
INSERT INTO migrations VALUES(215,'2021_08_26_093522_config_value_to_medium_text',1);
INSERT INTO migrations VALUES(216,'2021_09_07_094310_create_push_subscriptions_table',1);
INSERT INTO migrations VALUES(217,'2021_09_26_164200_create_hrsystem_table',1);
INSERT INTO migrations VALUES(218,'2021_10_02_190310_add_device_outages_index',1);
INSERT INTO migrations VALUES(219,'2021_10_03_164200_update_hrsystem_table',1);
INSERT INTO migrations VALUES(220,'2021_10_20_072929_disable_example_plugin',1);
INSERT INTO migrations VALUES(221,'2021_10_20_224207_increase_length_of_attrib_type_column',1);
INSERT INTO migrations VALUES(222,'2021_11_12_123037_change_cpwVcID_to_unsignedInteger',1);
INSERT INTO migrations VALUES(223,'2021_11_17_105321_device_add_display_field',1);
INSERT INTO migrations VALUES(224,'2021_11_29_160744_change_ports_text_fields_to_varchar',1);
INSERT INTO migrations VALUES(225,'2021_11_29_165046_improve_devices_search_index',1);
INSERT INTO migrations VALUES(226,'2021_11_29_165436_improve_ports_search_index',1);
INSERT INTO migrations VALUES(227,'2021_12_02_100709_remove_ports_stp_unique_index',1);
INSERT INTO migrations VALUES(228,'2021_12_02_101739_add_vlan_field_to_stp_table',1);
INSERT INTO migrations VALUES(229,'2021_12_02_101810_add_vlan_and_port_index_fields_to_ports_stp_table',1);
INSERT INTO migrations VALUES(230,'2021_12_02_110154_update_ports_stp_unique_index',1);
INSERT INTO migrations VALUES(231,'2021_12_02_113537_ports_stp_designated_cost_change_to_int',1);
INSERT INTO migrations VALUES(232,'2021_25_01_0127_create_isis_adjacencies_table',1);
INSERT INTO migrations VALUES(233,'2021_25_01_0128_isis_adjacencies_add_admin_status',1);
INSERT INTO migrations VALUES(234,'2021_25_01_0129_isis_adjacencies_nullable',1);
INSERT INTO migrations VALUES(235,'2022_02_03_164059_increase_auth_id_length',1);
INSERT INTO migrations VALUES(236,'2022_02_21_073500_add_iface_field_to_bgp_peers',1);
INSERT INTO migrations VALUES(237,'2022_04_08_085504_isis_adjacencies_table_add_index',1);
INSERT INTO migrations VALUES(238,'2022_05_25_084506_add_widgets_column_to_users_widgets_table',1);
INSERT INTO migrations VALUES(239,'2022_05_25_084617_migrate_widget_ids',1);
INSERT INTO migrations VALUES(240,'2022_05_25_085715_remove_user_widgets_id',1);
INSERT INTO migrations VALUES(241,'2022_05_25_090027_drop_widgets_table',1);
INSERT INTO migrations VALUES(242,'2022_05_30_084932_update-app-status-length',1);
INSERT INTO migrations VALUES(243,'2022_07_03_1947_add_app_data',1);
INSERT INTO migrations VALUES(244,'2022_07_19_081224_plugins_unique_index',1);
INSERT INTO migrations VALUES(245,'2022_08_15_084506_add_rrd_type_to_sensors_table',1);
INSERT INTO migrations VALUES(246,'2022_08_15_084507_add_rrd_type_to_wireless_sensors_table',1);
INSERT INTO migrations VALUES(247,'2022_08_15_091314_create_ports_vdsl_table',1);
INSERT INTO migrations VALUES(248,'2022_09_03_091314_update_ports_adsl_table_with_defaults',1);
INSERT INTO migrations VALUES(249,'2023_03_14_130653_migrate_empty_user_funcs_to_null',1);
INSERT INTO migrations VALUES(250,'2023_04_12_174529_modify_ports_table',1);
INSERT INTO migrations VALUES(251,'2023_04_26_185850_change_vminfo_vmw_vm_guest_o_s_nullable',1);
INSERT INTO migrations VALUES(252,'2023_04_27_164904_update_slas_opstatus_tinyint',1);
INSERT INTO migrations VALUES(253,'2023_05_12_071412_devices_expand_timetaken_doubles',1);
INSERT INTO migrations VALUES(254,'2023_06_02_230406_create_vendor_oui_table',1);
INSERT INTO migrations VALUES(255,'2023_06_18_195618_create_bouncer_tables',1);
INSERT INTO migrations VALUES(256,'2023_06_18_201914_migrate_level_to_roles',1);
INSERT INTO migrations VALUES(257,'2023_08_02_090027_drop_dbschema_table',1);
INSERT INTO migrations VALUES(258,'2023_08_02_120455_vendor_ouis_unique_index',1);
INSERT INTO migrations VALUES(259,'2023_08_30_105156_add_applications_soft_deleted',1);
INSERT INTO migrations VALUES(260,'2023_09_01_084057_application_new_defaults',1);
INSERT INTO migrations VALUES(261,'2023_10_07_170735_increase_processes_cputime_length',1);
INSERT INTO migrations VALUES(262,'2023_10_07_231037_application_metrics_add_primary_key',1);
INSERT INTO migrations VALUES(263,'2023_10_12_183306_ports_statistics_table_unsigned_stats',1);
INSERT INTO migrations VALUES(264,'2023_10_12_184311_bgp_peers_cbgp_table_unsigned_stats',1);
INSERT INTO migrations VALUES(265,'2023_10_12_184652_bgp_peers_table_unsigned_stats',1);
INSERT INTO migrations VALUES(266,'2023_10_14_162039_restore_ports_delta_fields',1);
INSERT INTO migrations VALUES(267,'2023_10_14_162234_restore_bgp_peers_cbgp_delta_fields',1);
INSERT INTO migrations VALUES(268,'2023_10_20_075853_cisco_asa_add_default_limits',1);
INSERT INTO migrations VALUES(269,'2023_10_31_074547_ospf_areas_unsigned',1);
INSERT INTO migrations VALUES(270,'2023_10_31_074901_ospf_instances_unsigned',1);
INSERT INTO migrations VALUES(271,'2023_10_31_075239_ospf_nbrs_unsigned',1);
INSERT INTO migrations VALUES(272,'2023_10_31_080052_ospf_ports_unsigned',1);
INSERT INTO migrations VALUES(273,'2023_11_04_125846_packages_increase_name_column_length',1);
INSERT INTO migrations VALUES(274,'2023_11_21_172239_increase_vminfo.vmwvmguestos_column_length',1);
INSERT INTO migrations VALUES(275,'2023_12_08_080319_create_custom_map_table',1);
INSERT INTO migrations VALUES(276,'2023_12_08_081420_create_custom_map_node_table',1);
INSERT INTO migrations VALUES(277,'2023_12_08_082518_create_custom_map_edge_table',1);
INSERT INTO migrations VALUES(278,'2023_12_08_083319_create_custom_map_background_table',1);
INSERT INTO migrations VALUES(279,'2023_12_08_184652_mpls_addrtype_fix',1);
INSERT INTO migrations VALUES(280,'2023_12_10_130000_historical_data_to_ports_nac',1);
INSERT INTO migrations VALUES(281,'2023_12_12_171400_alert_rule_note',1);
INSERT INTO migrations VALUES(282,'2023_12_15_105529_access_points_nummonbssid_integer',1);
INSERT INTO migrations VALUES(283,'2023_12_19_082112_custom_map_grid_snap',1);
INSERT INTO migrations VALUES(284,'2023_12_21_085427_create_view_port_mac_link',1);
INSERT INTO migrations VALUES(285,'2024_01_04_195618_add_ignore_status_to_devices_tables',1);
INSERT INTO migrations VALUES(286,'2024_01_08_223812_custom_map_node_image',1);
INSERT INTO migrations VALUES(287,'2024_01_09_211518_custom_map_node_maplink',1);
INSERT INTO migrations VALUES(288,'2024_01_09_223917_bill_data_new_primary',1);
INSERT INTO migrations VALUES(289,'2024_01_09_223927_bill_data_updated_indexes',1);
INSERT INTO migrations VALUES(290,'2024_02_03_201014_custom_map_edge_additions',1);
INSERT INTO migrations VALUES(291,'2024_02_07_151845_custom_map_additions',1);
INSERT INTO migrations VALUES(292,'2024_03_27_123152_create_transceivers_table',1);
INSERT INTO migrations VALUES(293,'2024_04_10_093513_remove_device_perf',1);
INSERT INTO migrations VALUES(294,'2024_04_22_161711_custom_maps_add_group',1);
INSERT INTO migrations VALUES(295,'2024_04_29_180911_custom_maps_add_background_type_and_background_data',1);
INSERT INTO migrations VALUES(296,'2024_04_29_183605_custom_maps_drop_background_suffix_and_background_version',1);
INSERT INTO migrations VALUES(297,'2024_07_13_133839_modify_ent_physical_defaults',1);
INSERT INTO migrations VALUES(298,'2024_07_19_120719_update_ports_stack_table',1);
INSERT INTO migrations VALUES(299,'2024_07_28_162410_ent_physical_table_ifindex_unsigned',1);
INSERT INTO migrations VALUES(300,'2024_08_12_232009_ent_physical_table_rev_length',1);
INSERT INTO migrations VALUES(301,'2024_08_27_182000_ports_statistics_table_rev_length',1);
INSERT INTO migrations VALUES(302,'2024_10_06_002633_ports_vlans_table_add_port_id_index',1);
INSERT INTO migrations VALUES(303,'2024_10_12_164214_custom_map_edge_width',1);
INSERT INTO migrations VALUES(304,'2024_10_12_210114_custom_map_legend_colours',1);
INSERT INTO migrations VALUES(305,'2024_10_13_161616_create_custom_map_nodeimage_table',1);
INSERT INTO migrations VALUES(306,'2024_10_13_162920_add_custom_map_nodeimage_column',1);
INSERT INTO migrations VALUES(307,'2024_10_20_154356_create_qos_table',1);
INSERT INTO migrations VALUES(308,'2024_10_24_131715_mpls_sdp_bindings_enum_string',1);
INSERT INTO migrations VALUES(309,'2024_11_07_110342_custommap_edge_add_text_align',1);
INSERT INTO migrations VALUES(310,'2024_11_22_135845_alert_log_refactor_indexes',1);
INSERT INTO migrations VALUES(311,'2025_01_07_223946_drop_cisco_a_s_a_table',1);
INSERT INTO migrations VALUES(312,'2025_01_20_125000_create_ospfv3_areas_table',1);
INSERT INTO migrations VALUES(313,'2025_01_20_125000_create_ospfv3_instances_table',1);
INSERT INTO migrations VALUES(314,'2025_01_20_125000_create_ospfv3_nbrs_table',1);
INSERT INTO migrations VALUES(315,'2025_01_20_125000_create_ospfv3_ports_table',1);
INSERT INTO migrations VALUES(316,'2025_01_22_194300_add_storage_oids_to_storage_table',1);
INSERT INTO migrations VALUES(317,'2025_01_22_194342_drop_storage_deleted',1);
INSERT INTO migrations VALUES(318,'2025_01_28_135558_ports_drop_unique_ifindex',1);
INSERT INTO migrations VALUES(319,'2025_01_30_000121_add_ifindex_index_to_ports_table',1);
INSERT INTO migrations VALUES(320,'2025_01_30_214311_create_ipv6_nd_table',1);
INSERT INTO migrations VALUES(321,'2025_03_11_031114_drop_ospfv3ifinstid',1);
INSERT INTO migrations VALUES(322,'2025_03_17_144000_drop_ospfv3nbrifindex',1);
INSERT INTO migrations VALUES(323,'2025_03_17_222255_rename_existing_permissions_tables',1);
INSERT INTO migrations VALUES(324,'2025_03_17_222652_create_permission_tables',1);
INSERT INTO migrations VALUES(325,'2025_03_17_222734_migrate_bouncer_to_spatie',1);
INSERT INTO migrations VALUES(326,'2025_03_18_003446_drop_bouncer_tables',1);
INSERT INTO migrations VALUES(327,'2025_03_19_205644_fix_ospfv3_areas_table',1);
INSERT INTO migrations VALUES(328,'2025_03_19_205648_fix_ospfv3_instances_table',1);
INSERT INTO migrations VALUES(329,'2025_03_19_205655_fix_ospfv3_nbrs_table',1);
INSERT INTO migrations VALUES(330,'2025_03_19_205700_fix_ospfv3_ports_table',1);
INSERT INTO migrations VALUES(331,'2025_03_22_134124_fix_ipv6_addresses_id_type',1);
INSERT INTO migrations VALUES(332,'2025_04_15_122034_laravel_11_fix_types',1);
INSERT INTO migrations VALUES(333,'2025_04_29_133233_add_device_index_to_ipv4_mac_table',1);
INSERT INTO migrations VALUES(334,'2025_04_29_133533_add_indexes_to_ipv6_nd_table',1);
INSERT INTO migrations VALUES(335,'2025_04_29_150404_context_nullable_in_ipv4_mac_table',1);
INSERT INTO migrations VALUES(336,'2025_04_29_150423_context_nullable_in_ipv6_nd_table',1);
INSERT INTO migrations VALUES(337,'2025_05_02_133959_filter_empty_socialite_configs',1);
INSERT INTO migrations VALUES(338,'2025_05_03_152418_remove_invalid_sensor_classes',1);
