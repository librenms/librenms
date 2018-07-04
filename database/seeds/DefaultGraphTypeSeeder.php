<?php

use Illuminate\Database\Seeder;

class DefaultGraphTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('graph_types')->insert([
            [
                "graph_type" => "device",
                "graph_subtype" => "arbos_flows",
                "graph_section" => "graphs",
                "graph_descr" => "Accumulative flow count per SP device",
                "graph_order" => "0",
            ],
            [
                "graph_type" => "device",
                "graph_subtype" => "asa_conns",
                "graph_section" => "firewall",
                "graph_descr" => "Current connections",
                "graph_order" => "0",
            ],
            [
                "graph_type" => "device",
                "graph_subtype" => "asyncos_conns",
                "graph_section" => "proxy",
                "graph_descr" => "Current Connections",
                "graph_order" => "0",
            ],
            [
                "graph_type" => "device",
                "graph_subtype" => "fortios_lograte",
                "graph_section" => "analyzer",
                "graph_descr" => "Log Rate",
                "graph_order" => "0",
            ],
            [
                "graph_type" => "device",
                "graph_subtype" => "junos_jsrx_spu_flows",
                "graph_section" => "network",
                "graph_descr" => "SPU Flows",
                "graph_order" => "0",
            ],
            [
                "graph_type" => "device",
                "graph_subtype" => "panos_activetunnels",
                "graph_section" => "firewall",
                "graph_descr" => "Active GlobalProtect Tunnels",
                "graph_order" => "0",
            ],
            [
                "graph_type" => "device",
                "graph_subtype" => "pulse_sessions",
                "graph_section" => "firewall",
                "graph_descr" => "Active Sessions",
                "graph_order" => "0",
            ],
            [
                "graph_type" => "device",
                "graph_subtype" => "pulse_users",
                "graph_section" => "firewall",
                "graph_descr" => "Active Users",
                "graph_order" => "0",
            ],
            [
                "graph_type" => "device",
                "graph_subtype" => "riverbed_connections",
                "graph_section" => "network",
                "graph_descr" => "Connections",
                "graph_order" => "0",
            ],
            [
                "graph_type" => "device",
                "graph_subtype" => "riverbed_datastore",
                "graph_section" => "network",
                "graph_descr" => "Datastore productivity",
                "graph_order" => "0",
            ],
            [
                "graph_type" => "device",
                "graph_subtype" => "riverbed_optimization",
                "graph_section" => "network",
                "graph_descr" => "Optimization",
                "graph_order" => "0",
            ],
            [
                "graph_type" => "device",
                "graph_subtype" => "riverbed_passthrough",
                "graph_section" => "network",
                "graph_descr" => "Bandwidth passthrough",
                "graph_order" => "0",
            ],
            [
                "graph_type" => "device",
                "graph_subtype" => "sgos_average_requests",
                "graph_section" => "network",
                "graph_descr" => "Average HTTP Requests",
                "graph_order" => "0",
            ],
            [
                "graph_type" => "device",
                "graph_subtype" => "sgos_client_connections",
                "graph_section" => "network",
                "graph_descr" => "HTTP Client Connections",
                "graph_order" => "0",
            ],
            [
                "graph_type" => "device",
                "graph_subtype" => "sgos_client_connections_active",
                "graph_section" => "network",
                "graph_descr" => "HTTP Client Connections Active",
                "graph_order" => "0",
            ],
            [
                "graph_type" => "device",
                "graph_subtype" => "sgos_client_connections_idle",
                "graph_section" => "network",
                "graph_descr" => "HTTP Client Connections Idle",
                "graph_order" => "0",
            ],
            [
                "graph_type" => "device",
                "graph_subtype" => "sgos_server_connections",
                "graph_section" => "network",
                "graph_descr" => "HTTP Server Connections",
                "graph_order" => "0",
            ],
            [
                "graph_type" => "device",
                "graph_subtype" => "sgos_server_connections_active",
                "graph_section" => "network",
                "graph_descr" => "HTTP Server Connections Active",
                "graph_order" => "0",
            ],
            [
                "graph_type" => "device",
                "graph_subtype" => "sgos_server_connections_idle",
                "graph_section" => "network",
                "graph_descr" => "HTTP Server Connections Idle",
                "graph_order" => "0",
            ],
            [
                "graph_type" => "device",
                "graph_subtype" => "sonicwall_sessions",
                "graph_section" => "firewall",
                "graph_descr" => "Active Sessions",
                "graph_order" => "0",
            ],
            [
                "graph_type" => "device",
                "graph_subtype" => "waas_cwotfostatsactiveoptconn",
                "graph_section" => "graphs",
                "graph_descr" => "Optimized TCP Connections",
                "graph_order" => "0",
            ],
            [
                "graph_type" => "device",
                "graph_subtype" => "zywall_sessions",
                "graph_section" => "firewall",
                "graph_descr" => "Sessions",
                "graph_order" => "0",
            ]
        ]);
    }
}
