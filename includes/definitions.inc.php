<?php
/*
 NO CHANGES TO THIS FILE, IT IS NOT USER-EDITABLE

 YES, THAT MEANS YOU

 Any changes you want to make here, make in config.php instead.
*/

$config['os']['default']['over'][0]['graph'] = 'device_processor';
$config['os']['default']['over'][0]['text']  = 'Processor Usage';
$config['os']['default']['over'][1]['graph'] = 'device_mempool';
$config['os']['default']['over'][1]['text']  = 'Memory Usage';

$os_group = 'unix';
$config['os_group'][$os_group]['type']              = 'server';
$config['os_group'][$os_group]['processor_stacked'] = 1;
$config['os_group'][$os_group]['over'][0]['graph']  = 'device_processor';
$config['os_group'][$os_group]['over'][0]['text']   = 'Processor Usage';
$config['os_group'][$os_group]['over'][1]['graph']  = 'device_ucd_memory';
$config['os_group'][$os_group]['over'][1]['text']   = 'Memory Usage';


// Device  - AirFIBER
$config['graph_types']['device']['ubnt_airfiber_RadioFreqs']['section'] = 'wireless';
$config['graph_types']['device']['ubnt_airfiber_RadioFreqs']['order'] = '0';
$config['graph_types']['device']['ubnt_airfiber_RadioFreqs']['descr'] = 'Radio Frequencies';

$config['graph_types']['device']['ubnt_airfiber_TxPower']['section'] = 'wireless';
$config['graph_types']['device']['ubnt_airfiber_TxPower']['order'] = '0';
$config['graph_types']['device']['ubnt_airfiber_TxPower']['descr'] = 'Radio Tx Power';

$config['graph_types']['device']['ubnt_airfiber_LinkDist']['section'] = 'wireless';
$config['graph_types']['device']['ubnt_airfiber_LinkDist']['order'] = '1';
$config['graph_types']['device']['ubnt_airfiber_LinkDist']['descr'] = 'Link Distance';

$config['graph_types']['device']['ubnt_airfiber_Capacity']['section'] = 'wireless';
$config['graph_types']['device']['ubnt_airfiber_Capacity']['order'] = '2';
$config['graph_types']['device']['ubnt_airfiber_Capacity']['descr'] = 'Link Capacity';

$config['graph_types']['device']['ubnt_airfiber_RadioTemp']['section'] = 'wireless';
$config['graph_types']['device']['ubnt_airfiber_RadioTemp']['order'] = '3';
$config['graph_types']['device']['ubnt_airfiber_RadioTemp']['descr'] = 'Radio Temperatures';

$config['graph_types']['device']['ubnt_airfiber_RFTotOctetsTx']['section'] = 'wireless';
$config['graph_types']['device']['ubnt_airfiber_RFTotOctetsTx']['order'] = '4';
$config['graph_types']['device']['ubnt_airfiber_RFTotOctetsTx']['descr'] = 'RF Total Octets Tx';

$config['graph_types']['device']['ubnt_airfiber_RFTotPktsTx']['section'] = 'wireless';
$config['graph_types']['device']['ubnt_airfiber_RFTotPktsTx']['order'] = '5';
$config['graph_types']['device']['ubnt_airfiber_RFTotPktsTx']['descr'] = 'RF Total Packets Tx';

$config['graph_types']['device']['ubnt_airfiber_RFTotOctetsRx']['section'] = 'wireless';
$config['graph_types']['device']['ubnt_airfiber_RFTotOctetsRx']['order'] = '6';
$config['graph_types']['device']['ubnt_airfiber_RFTotOctetsRx']['descr'] = 'RF Total Octets Rx';

$config['graph_types']['device']['ubnt_airfiber_RFTotPktsRx']['section'] = 'wireless';
$config['graph_types']['device']['ubnt_airfiber_RFTotPktsRx']['order'] = '7';
$config['graph_types']['device']['ubnt_airfiber_RFTotPktsRx']['descr'] = 'RF Total Packets Rx';

$config['graph_types']['device']['ubnt_airfiber_RxPower']['section'] = 'wireless';
$config['graph_types']['device']['ubnt_airfiber_RxPower']['order'] = '8';
$config['graph_types']['device']['ubnt_airfiber_RxPower']['descr'] = 'Radio Rx Power';

// Siklu support
$config['graph_types']['device']['siklu_rfinterfacePkts']['section'] = 'wireless';
$config['graph_types']['device']['siklu_rfinterfacePkts']['order'] = '3';
$config['graph_types']['device']['siklu_rfinterfacePkts']['descr'] = 'Packets';

$config['graph_types']['device']['siklu_rfinterfaceOtherPkts']['section'] = 'wireless';
$config['graph_types']['device']['siklu_rfinterfaceOtherPkts']['order'] = '4';
$config['graph_types']['device']['siklu_rfinterfaceOtherPkts']['descr'] = 'Other Packets';

$config['graph_types']['device']['siklu_rfinterfaceOctets']['section'] = 'wireless';
$config['graph_types']['device']['siklu_rfinterfaceOctets']['order'] = '5';
$config['graph_types']['device']['siklu_rfinterfaceOctets']['descr'] = 'Traffic';

$config['graph_types']['device']['siklu_rfinterfaceOtherOctets']['section'] = 'wireless';
$config['graph_types']['device']['siklu_rfinterfaceOtherOctets']['order'] = '6';
$config['graph_types']['device']['siklu_rfinterfaceOtherOctets']['descr'] = 'Other Octets';

// Barracuda Firewall support
$config['graph_types']['device']['barracuda_firewall_sessions']['section'] = 'firewall';
$config['graph_types']['device']['barracuda_firewall_sessions']['order'] = 0;
$config['graph_types']['device']['barracuda_firewall_sessions']['descr'] = 'Active Sessions';


// Ceragon Ceraos support
$config['graph_types']['device']['ceraos_RxLevel']['section'] = 'wireless';
$config['graph_types']['device']['ceraos_RxLevel']['order'] = '0';
$config['graph_types']['device']['ceraos_RxLevel']['descr'] = 'RX Level';

$config['graph_types']['device']['ceraos_TxPower']['section'] = 'wireless';
$config['graph_types']['device']['ceraos_TxPower']['order'] = '1';
$config['graph_types']['device']['ceraos_TxPower']['descr'] = 'TX Power';

$config['graph_types']['device']['ceraos_MSE']['section'] = 'wireless';
$config['graph_types']['device']['ceraos_MSE']['order'] = '2';
$config['graph_types']['device']['ceraos_MSE']['descr'] = 'Radial MSE';

$config['graph_types']['device']['ceraos_XPI']['section'] = 'wireless';
$config['graph_types']['device']['ceraos_XPI']['order'] = '3';
$config['graph_types']['device']['ceraos_XPI']['descr'] = 'Cross Polarisation Interference';

$config['graph_types']['device']['ceraos_DefectedBlocks']['section'] = 'wireless';
$config['graph_types']['device']['ceraos_DefectedBlocks']['order'] = '4';
$config['graph_types']['device']['ceraos_DefectedBlocks']['descr'] = 'DefectedBlocks';

$config['graph_types']['device']['ceraos_TxBitrate']['section'] = 'wireless';
$config['graph_types']['device']['ceraos_TxBitrate']['order'] = '5';
$config['graph_types']['device']['ceraos_TxBitrate']['descr'] = 'TxBitrate';

$config['graph_types']['device']['ceraos_RxBitrate']['section'] = 'wireless';
$config['graph_types']['device']['ceraos_RxBitrate']['order'] = '6';
$config['graph_types']['device']['ceraos_RxBitrate']['descr'] = 'RxBitrate';

// Sub10 support
$config['graph_types']['device']['sub10_sub10RadioLclTxPower']['section'] = 'wireless';
$config['graph_types']['device']['sub10_sub10RadioLclTxPower']['order'] = '0';
$config['graph_types']['device']['sub10_sub10RadioLclTxPower']['descr'] = 'Radio Transmit Power';

$config['graph_types']['device']['sub10_sub10RadioLclRxPower']['section'] = 'wireless';
$config['graph_types']['device']['sub10_sub10RadioLclRxPower']['order'] = '1';
$config['graph_types']['device']['sub10_sub10RadioLclRxPower']['descr'] = 'Radio Receive Power';

$config['graph_types']['device']['sub10_sub10RadioLclVectErr']['section'] = 'wireless';
$config['graph_types']['device']['sub10_sub10RadioLclVectErr']['order'] = '3';
$config['graph_types']['device']['sub10_sub10RadioLclVectErr']['descr'] = 'Radio Vector Error';

$config['graph_types']['device']['sub10_sub10RadioLclLnkLoss']['section'] = 'wireless';
$config['graph_types']['device']['sub10_sub10RadioLclLnkLoss']['order'] = '3';
$config['graph_types']['device']['sub10_sub10RadioLclLnkLoss']['descr'] = 'Radio Link Loss';

$config['graph_types']['device']['sub10_sub10RadioLclAFER']['section'] = 'wireless';
$config['graph_types']['device']['sub10_sub10RadioLclAFER']['order'] = '4';
$config['graph_types']['device']['sub10_sub10RadioLclAFER']['descr'] = 'Radio Air Frame Error Rate';

$config['graph_types']['device']['sub10_sub10RadioLclDataRate']['section'] = 'wireless';
$config['graph_types']['device']['sub10_sub10RadioLclDataRate']['order'] = '4';
$config['graph_types']['device']['sub10_sub10RadioLclDataRate']['descr'] = 'Data Rate on the Airside interface';

//cambium graphs
$config['graph_types']['device']['canopy_generic_gpsStats']['section'] = 'wireless';
$config['graph_types']['device']['canopy_generic_gpsStats']['order']   = '0';
$config['graph_types']['device']['canopy_generic_gpsStats']['descr']   = 'GPS Stats';
$config['graph_types']['device']['canopy_generic_jitter']['section'] = 'wireless';
$config['graph_types']['device']['canopy_generic_jitter']['order']   = '2';
$config['graph_types']['device']['canopy_generic_jitter']['descr']   = 'Jitter';
$config['graph_types']['device']['canopy_generic_signalHV']['section'] = 'wireless';
$config['graph_types']['device']['canopy_generic_signalHV']['order']   = '3';
$config['graph_types']['device']['canopy_generic_signalHV']['descr']   = 'Signal';
$config['graph_types']['device']['canopy_generic_450_powerlevel']['section'] = 'wireless';
$config['graph_types']['device']['canopy_generic_450_powerlevel']['order']   = '4';
$config['graph_types']['device']['canopy_generic_450_powerlevel']['descr']   = 'Power Level of Registered SM';
$config['graph_types']['device']['canopy_generic_450_linkRadioDbm']['section'] = 'wireless';
$config['graph_types']['device']['canopy_generic_450_linkRadioDbm']['order']   = '5';
$config['graph_types']['device']['canopy_generic_450_linkRadioDbm']['descr']   = 'Radio Link H/V';
$config['graph_types']['device']['canopy_generic_450_ptpSNR']['section'] = 'wireless';
$config['graph_types']['device']['canopy_generic_450_ptpSNR']['order']   = '6';
$config['graph_types']['device']['canopy_generic_450_ptpSNR']['descr']   = 'Master SNR';
$config['graph_types']['device']['canopy_generic_450_slaveHV']['section'] = 'wireless';
$config['graph_types']['device']['canopy_generic_450_slaveHV']['order']   = '7';
$config['graph_types']['device']['canopy_generic_450_slaveHV']['descr']   = 'Dbm H/V';
$config['graph_types']['device']['canopy_generic_regCount']['section'] = 'wireless';
$config['graph_types']['device']['canopy_generic_regCount']['order']   = '11';
$config['graph_types']['device']['canopy_generic_regCount']['descr']   = 'Registered SM';
$config['graph_types']['device']['canopy_generic_radioDbm']['section'] = 'wireless';
$config['graph_types']['device']['canopy_generic_radioDbm']['order']   = '13';
$config['graph_types']['device']['canopy_generic_radioDbm']['descr']   = 'Radio Dbm';
$config['graph_types']['device']['canopy_generic_errorCount']['section'] = 'wireless';
$config['graph_types']['device']['canopy_generic_errorCount']['order']   = '14';
$config['graph_types']['device']['canopy_generic_errorCount']['descr']   = 'Error Count (Migrated to Wireless Sensor)';
$config['graph_types']['device']['canopy_generic_crcErrors']['section'] = 'wireless';
$config['graph_types']['device']['canopy_generic_crcErrors']['order']   = '15';
$config['graph_types']['device']['canopy_generic_crcErrors']['descr']   = 'CRC Errors (Migrated to Wireless Sensor)';

$config['graph_types']['device']['cambium_epmp_RFStatus']['section'] = 'wireless';
$config['graph_types']['device']['cambium_epmp_RFStatus']['order']   = '0';
$config['graph_types']['device']['cambium_epmp_RFStatus']['descr']   = 'RF Status';
$config['graph_types']['device']['cambium_epmp_modulation']['section'] = 'wireless';
$config['graph_types']['device']['cambium_epmp_modulation']['order']   = '2';
$config['graph_types']['device']['cambium_epmp_modulation']['descr']   = 'ePMP Modulation';
$config['graph_types']['device']['cambium_epmp_registeredSM']['section'] = 'wireless';
$config['graph_types']['device']['cambium_epmp_registeredSM']['order']   = '3';
$config['graph_types']['device']['cambium_epmp_registeredSM']['descr']   = 'ePMP Registered SM';
$config['graph_types']['device']['cambium_epmp_access']['section'] = 'wireless';
$config['graph_types']['device']['cambium_epmp_access']['order']   = '4';
$config['graph_types']['device']['cambium_epmp_access']['descr']   = 'Access Info';
$config['graph_types']['device']['cambium_epmp_gpsSync']['section'] = 'wireless';
$config['graph_types']['device']['cambium_epmp_gpsSync']['order']   = '5';
$config['graph_types']['device']['cambium_epmp_gpsSync']['descr']   = 'GPS Sync Status';
$config['graph_types']['device']['cambium_epmp_freq']['section'] = 'wireless';
$config['graph_types']['device']['cambium_epmp_freq']['order']   = '6';
$config['graph_types']['device']['cambium_epmp_freq']['descr']   = 'Frequency';
$config['graph_types']['device']['cambium-epmp-frameUtilization']['section'] = 'wireless';
$config['graph_types']['device']['cambium-epmp-frameUtilization']['order']   = '7';
$config['graph_types']['device']['cambium-epmp-frameUtilization']['descr']   = 'Frame Utilization';

$config['graph_types']['device']['agent']['section'] = 'poller';
$config['graph_types']['device']['agent']['order']   = '0';
$config['graph_types']['device']['agent']['descr']   = 'Agent Execution Time';

$config['graph_types']['device']['cipsec_flow_bits']['section']    = 'firewall';
$config['graph_types']['device']['cipsec_flow_bits']['order']      = '0';
$config['graph_types']['device']['cipsec_flow_bits']['descr']      = 'IPSec Tunnel Traffic Volume';
$config['graph_types']['device']['cipsec_flow_pkts']['section']    = 'firewall';
$config['graph_types']['device']['cipsec_flow_pkts']['order']      = '0';
$config['graph_types']['device']['cipsec_flow_pkts']['descr']      = 'IPSec Tunnel Traffic Packets';
$config['graph_types']['device']['cipsec_flow_stats']['section']   = 'firewall';
$config['graph_types']['device']['cipsec_flow_stats']['order']     = '0';
$config['graph_types']['device']['cipsec_flow_stats']['descr']     = 'IPSec Tunnel Statistics';
$config['graph_types']['device']['cipsec_flow_tunnels']['section'] = 'firewall';
$config['graph_types']['device']['cipsec_flow_tunnels']['order']   = '0';
$config['graph_types']['device']['cipsec_flow_tunnels']['descr']   = 'IPSec Active Tunnels';
$config['graph_types']['device']['cras_sessions']['section']       = 'firewall';
$config['graph_types']['device']['cras_sessions']['order']         = '0';
$config['graph_types']['device']['cras_sessions']['descr']         = 'Remote Access Sessions';
$config['graph_types']['device']['fortigate_sessions']['section']  = 'firewall';
$config['graph_types']['device']['fortigate_sessions']['order']    = '0';
$config['graph_types']['device']['fortigate_sessions']['descr']    = 'Active Sessions';
$config['graph_types']['device']['fortigate_cpu']['section']       = 'system';
$config['graph_types']['device']['fortigate_cpu']['order']         = '0';
$config['graph_types']['device']['fortigate_cpu']['descr']         = 'CPU';
$config['graph_types']['device']['screenos_sessions']['section']   = 'firewall';
$config['graph_types']['device']['screenos_sessions']['order']     = '0';
$config['graph_types']['device']['screenos_sessions']['descr']     = 'Active Sessions';

//FortiOS Graphs
$config['graph_types']['device']['fortios_lograte'] = ['section' => 'analyzer', 'order' => 0, 'descr' => 'Log Rate'];

//PAN OS Graphs
$config['graph_types']['device']['panos_sessions']['section']           = 'firewall';
$config['graph_types']['device']['panos_sessions']['order']             = '0';
$config['graph_types']['device']['panos_sessions']['descr']             = 'Active Sessions';
$config['graph_types']['device']['panos_sessions_tcp']['section']       = 'firewall';
$config['graph_types']['device']['panos_sessions_tcp']['order']         = '0';
$config['graph_types']['device']['panos_sessions_tcp']['descr']         = 'Active TCP Sessions';
$config['graph_types']['device']['panos_sessions_udp']['section']       = 'firewall';
$config['graph_types']['device']['panos_sessions_udp']['order']         = '0';
$config['graph_types']['device']['panos_sessions_udp']['descr']         = 'Active UDP Sessions';
$config['graph_types']['device']['panos_sessions_icmp']['section']      = 'firewall';
$config['graph_types']['device']['panos_sessions_icmp']['order']        = '0';
$config['graph_types']['device']['panos_sessions_icmp']['descr']        = 'Active ICMP Sessions';
$config['graph_types']['device']['panos_sessions_ssl']['section']       = 'firewall';
$config['graph_types']['device']['panos_sessions_ssl']['order']         = '0';
$config['graph_types']['device']['panos_sessions_ssl']['descr']         = 'Active SSL Proxy Sessions';
$config['graph_types']['device']['panos_sessions_sslutil']['section']   = 'firewall';
$config['graph_types']['device']['panos_sessions_sslutil']['order']     = '0';
$config['graph_types']['device']['panos_sessions_sslutil']['descr']     = 'Active SSL Proxy Utilization';
$config['graph_types']['device']['panos_activetunnels'] = ['section' => 'firewall', 'order' => 0, 'descr' => 'Active GlobalProtect Tunnels'];

//PF Graphs
$config['graph_types']['device']['pf_states']['section']           = 'firewall';
$config['graph_types']['device']['pf_states']['order']             = '1';
$config['graph_types']['device']['pf_states']['descr']             = 'States';
$config['graph_types']['device']['pf_searches']['section']           = 'firewall';
$config['graph_types']['device']['pf_searches']['order']             = '2';
$config['graph_types']['device']['pf_searches']['descr']             = 'Searches';
$config['graph_types']['device']['pf_inserts']['section']           = 'firewall';
$config['graph_types']['device']['pf_inserts']['order']             = '3';
$config['graph_types']['device']['pf_inserts']['descr']             = 'Inserts';
$config['graph_types']['device']['pf_removals']['section']           = 'firewall';
$config['graph_types']['device']['pf_removals']['order']             = '4';
$config['graph_types']['device']['pf_removals']['descr']             = 'Removals';
$config['graph_types']['device']['pf_matches']['section']            = 'firewall';
$config['graph_types']['device']['pf_matches']['order']              = '5';
$config['graph_types']['device']['pf_matches']['descr']              = 'Matches';
$config['graph_types']['device']['pf_badoffset']['section']          = 'firewall';
$config['graph_types']['device']['pf_badoffset']['order']            = '6';
$config['graph_types']['device']['pf_badoffset']['descr']            = 'BadOffset';
$config['graph_types']['device']['pf_fragmented']['section']         = 'firewall';
$config['graph_types']['device']['pf_fragmented']['order']           = '7';
$config['graph_types']['device']['pf_fragmented']['descr']           = 'Fragmented';
$config['graph_types']['device']['pf_short']['section']              = 'firewall';
$config['graph_types']['device']['pf_short']['order']                = '8';
$config['graph_types']['device']['pf_short']['descr']                = 'Short';
$config['graph_types']['device']['pf_normalized']['section']         = 'firewall';
$config['graph_types']['device']['pf_normalized']['order']           = '9';
$config['graph_types']['device']['pf_normalized']['descr']           = 'Normalized';
$config['graph_types']['device']['pf_memdropped']['section']         = 'firewall';
$config['graph_types']['device']['pf_memdropped']['order']           = '10';
$config['graph_types']['device']['pf_memdropped']['descr']           = 'MemDropped';

//Pulse Secure Graphs
$config['graph_types']['device']['pulse_sessions'] = ['section' => 'firewall', 'order' => 0, 'descr' => 'Active Sessions'];
$config['graph_types']['device']['pulse_users'] = ['section' => 'firewall', 'order' => 0, 'descr' => 'Active Users'];

// Infoblox dns/dhcp Graphs
$config['graph_types']['device']['ib_dns_dyn_updates']['section']             = 'dns';
$config['graph_types']['device']['ib_dns_dyn_updates']['order']               = '0';
$config['graph_types']['device']['ib_dns_dyn_updates']['descr']               = 'DNS dynamic updates';
$config['graph_types']['device']['ib_dns_request_return_codes']['section']    = 'dns';
$config['graph_types']['device']['ib_dns_request_return_codes']['order']      = '0';
$config['graph_types']['device']['ib_dns_request_return_codes']['descr']      = 'DNS request return codes';
$config['graph_types']['device']['ib_dns_performance']['section']             = 'dns';
$config['graph_types']['device']['ib_dns_performance']['order']               = '0';
$config['graph_types']['device']['ib_dns_performance']['descr']               = 'DNS performance';
$config['graph_types']['device']['ib_dhcp_messages']['section']               = 'dhcp';
$config['graph_types']['device']['ib_dhcp_messages']['order']                 = '0';
$config['graph_types']['device']['ib_dhcp_messages']['descr']                 = 'DHCP messages';

// Cisco WAAS Optimized TCP Connections
$config['graph_types']['device']['waas_cwotfostatsactiveoptconn'] = ['section' => 'graphs', 'order' => 0, 'descr' => 'Optimized TCP Connections'];

// SonicWALL Sessions
$config['graph_types']['device']['sonicwall_sessions'] = ['section' => 'firewall', 'order' => 0, 'descr' => 'Active Sessions'];

$config['graph_types']['device']['bits']['section']               = 'netstats';
$config['graph_types']['device']['bits']['order']                 = '0';
$config['graph_types']['device']['bits']['descr']                 = 'Total Traffic';
$config['graph_types']['device']['ipsystemstats_ipv4']['section'] = 'netstats';
$config['graph_types']['device']['ipsystemstats_ipv4']['order']   = '0';
$config['graph_types']['device']['ipsystemstats_ipv4']['descr']   = 'IPv4 Packet Statistics';
$config['graph_types']['device']['ipsystemstats_ipv4_frag']['section'] = 'netstats';
$config['graph_types']['device']['ipsystemstats_ipv4_frag']['order']   = '0';
$config['graph_types']['device']['ipsystemstats_ipv4_frag']['descr']   = 'IPv4 Fragmentation Statistics';
$config['graph_types']['device']['ipsystemstats_ipv6']['section']      = 'netstats';
$config['graph_types']['device']['ipsystemstats_ipv6']['order']        = '0';
$config['graph_types']['device']['ipsystemstats_ipv6']['descr']        = 'IPv6 Packet Statistics';
$config['graph_types']['device']['ipsystemstats_ipv6_frag']['section'] = 'netstats';
$config['graph_types']['device']['ipsystemstats_ipv6_frag']['order']   = '0';
$config['graph_types']['device']['ipsystemstats_ipv6_frag']['descr']   = 'IPv6 Fragmentation Statistics';
$config['graph_types']['device']['netstat_icmp_info']['section']       = 'netstats';
$config['graph_types']['device']['netstat_icmp_info']['order']         = '0';
$config['graph_types']['device']['netstat_icmp_info']['descr']         = 'ICMP Informational Statistics';
$config['graph_types']['device']['netstat_icmp']['section']            = 'netstats';
$config['graph_types']['device']['netstat_icmp']['order']              = '0';
$config['graph_types']['device']['netstat_icmp']['descr']              = 'ICMP Statistics';
$config['graph_types']['device']['netstat_ip']['section']              = 'netstats';
$config['graph_types']['device']['netstat_ip']['order']                = '0';
$config['graph_types']['device']['netstat_ip']['descr']                = 'IP Statistics';
$config['graph_types']['device']['netstat_ip_frag']['section']         = 'netstats';
$config['graph_types']['device']['netstat_ip_frag']['order']           = '0';
$config['graph_types']['device']['netstat_ip_frag']['descr']           = 'IP Fragmentation Statistics';
$config['graph_types']['device']['netstat_snmp']['section']            = 'netstats';
$config['graph_types']['device']['netstat_snmp']['order']              = '0';
$config['graph_types']['device']['netstat_snmp']['descr']              = 'SNMP Statistics';
$config['graph_types']['device']['netstat_snmp_pkt']['section']        = 'netstats';
$config['graph_types']['device']['netstat_snmp_pkt']['order']          = '0';
$config['graph_types']['device']['netstat_snmp_pkt']['descr']          = 'SNMP Packet Type Statistics';

$config['graph_types']['device']['netstat_ip_forward']['section'] = 'netstats';
$config['graph_types']['device']['netstat_ip_forward']['order']   = '0';
$config['graph_types']['device']['netstat_ip_forward']['descr']   = 'IP Forwarding Statistics';

$config['graph_types']['device']['netstat_tcp']['section'] = 'netstats';
$config['graph_types']['device']['netstat_tcp']['order']   = '0';
$config['graph_types']['device']['netstat_tcp']['descr']   = 'TCP Statistics';
$config['graph_types']['device']['netstat_udp']['section'] = 'netstats';
$config['graph_types']['device']['netstat_udp']['order']   = '0';
$config['graph_types']['device']['netstat_udp']['descr']   = 'UDP Statistics';

$config['graph_types']['device']['fdb_count']['section']      = 'system';
$config['graph_types']['device']['fdb_count']['order']        = '0';
$config['graph_types']['device']['fdb_count']['descr']        = 'MAC Addresses Learnt';
$config['graph_types']['device']['hr_processes']['section']   = 'system';
$config['graph_types']['device']['hr_processes']['order']     = '0';
$config['graph_types']['device']['hr_processes']['descr']     = 'Running Processes';
$config['graph_types']['device']['hr_users']['section']       = 'system';
$config['graph_types']['device']['hr_users']['order']         = '0';
$config['graph_types']['device']['hr_users']['descr']         = 'Users Logged In';
$config['graph_types']['device']['mempool']['section']        = 'system';
$config['graph_types']['device']['mempool']['order']          = '0';
$config['graph_types']['device']['mempool']['descr']          = 'Memory Pool Usage';
$config['graph_types']['device']['processor']['section']      = 'system';
$config['graph_types']['device']['processor']['order']        = '0';
$config['graph_types']['device']['processor']['descr']        = 'Processor Usage';
$config['graph_types']['device']['storage']['section']        = 'system';
$config['graph_types']['device']['storage']['order']          = '0';
$config['graph_types']['device']['storage']['descr']          = 'Filesystem Usage';
$config['graph_types']['device']['temperature']['section']    = 'system';
$config['graph_types']['device']['temperature']['order']      = '0';
$config['graph_types']['device']['temperature']['descr']      = 'temperature';
$config['graph_types']['device']['charge']['section']         = 'system';
$config['graph_types']['device']['charge']['order']           = '0';
$config['graph_types']['device']['charge']['descr']           = 'Battery Charge';
$config['graph_types']['device']['ucd_cpu']['section']        = 'system';
$config['graph_types']['device']['ucd_cpu']['order']          = '0';
$config['graph_types']['device']['ucd_cpu']['descr']          = 'Detailed Processor Usage';
$config['graph_types']['device']['ucd_load']['section']       = 'system';
$config['graph_types']['device']['ucd_load']['order']         = '0';
$config['graph_types']['device']['ucd_load']['descr']         = 'Load Averages';
$config['graph_types']['device']['ucd_memory']['section']     = 'system';
$config['graph_types']['device']['ucd_memory']['order']       = '0';
$config['graph_types']['device']['ucd_memory']['descr']       = 'Detailed Memory Usage';
$config['graph_types']['device']['ucd_swap_io']['section']    = 'system';
$config['graph_types']['device']['ucd_swap_io']['order']      = '0';
$config['graph_types']['device']['ucd_swap_io']['descr']      = 'Swap I/O Activity';
$config['graph_types']['device']['ucd_io']['section']         = 'system';
$config['graph_types']['device']['ucd_io']['order']           = '0';
$config['graph_types']['device']['ucd_io']['descr']           = 'System I/O Activity';
$config['graph_types']['device']['ucd_contexts']['section']   = 'system';
$config['graph_types']['device']['ucd_contexts']['order']     = '0';
$config['graph_types']['device']['ucd_contexts']['descr']     = 'Context Switches';
$config['graph_types']['device']['ucd_interrupts']['section'] = 'system';
$config['graph_types']['device']['ucd_interrupts']['order']   = '0';
$config['graph_types']['device']['ucd_interrupts']['descr']   = 'Interrupts';
$config['graph_types']['device']['uptime']['section']         = 'system';
$config['graph_types']['device']['uptime']['order']           = '0';
$config['graph_types']['device']['uptime']['descr']           = 'System Uptime';
$config['graph_types']['device']['poller_perf']['section']    = 'poller';
$config['graph_types']['device']['poller_perf']['order']      = '0';
$config['graph_types']['device']['poller_perf']['descr']      = 'Poller Time';
$config['graph_types']['device']['ping_perf']['section']      = 'poller';
$config['graph_types']['device']['ping_perf']['order']        = '0';
$config['graph_types']['device']['ping_perf']['descr']        = 'Ping Response';
$config['graph_types']['device']['poller_modules_perf']['section']    = 'poller';
$config['graph_types']['device']['poller_modules_perf']['order']      = '0';
$config['graph_types']['device']['poller_modules_perf']['descr']      = 'Poller Modules Performance';

$config['graph_types']['device']['vpdn_sessions_l2tp']['section'] = 'vpdn';
$config['graph_types']['device']['vpdn_sessions_l2tp']['order']   = '0';
$config['graph_types']['device']['vpdn_sessions_l2tp']['descr']   = 'VPDN L2TP Sessions';

$config['graph_types']['device']['vpdn_tunnels_l2tp']['section'] = 'vpdn';
$config['graph_types']['device']['vpdn_tunnels_l2tp']['order']   = '0';
$config['graph_types']['device']['vpdn_tunnels_l2tp']['descr']   = 'VPDN L2TP Tunnels';

$config['graph_types']['device']['netscaler_tcp_conn']['section'] = 'load balancer';
$config['graph_types']['device']['netscaler_tcp_conn']['order']   = '0';
$config['graph_types']['device']['netscaler_tcp_conn']['descr']   = 'TCP Connections';

$config['graph_types']['device']['netscaler_tcp_bits']['section'] = 'load balancer';
$config['graph_types']['device']['netscaler_tcp_bits']['order']   = '0';
$config['graph_types']['device']['netscaler_tcp_bits']['descr']   = 'TCP Traffic';

$config['graph_types']['device']['netscaler_tcp_pkts']['section'] = 'load balancer';
$config['graph_types']['device']['netscaler_tcp_pkts']['order']   = '0';
$config['graph_types']['device']['netscaler_tcp_pkts']['descr']   = 'TCP Packets';

$config['graph_types']['device']['asa_conns'] = ['section' => 'firewall', 'order' => 0, 'descr' => 'Current connections'];

$config['graph_types']['device']['cisco-iospri']['section']  = 'voice';
$config['graph_types']['device']['cisco-iospri']['order']    = '0';
$config['graph_types']['device']['cisco-iospri']['descr']    = 'PRI Utilisation';

$config['graph_types']['device']['cisco-voice-ip']['section']  = 'voice';
$config['graph_types']['device']['cisco-voice-ip']['order']    = '0';
$config['graph_types']['device']['cisco-voice-ip']['descr']    = 'IP Real Time Calls';

$config['graph_types']['device']['cisco-iosdsp']['section']  = 'voice';
$config['graph_types']['device']['cisco-iosdsp']['order']    = '0';
$config['graph_types']['device']['cisco-iosdsp']['descr']    = 'DSP Utilisation';

$config['graph_types']['device']['cisco-iosmtp']['section']  = 'voice';
$config['graph_types']['device']['cisco-iosmtp']['order']    = '0';
$config['graph_types']['device']['cisco-iosmtp']['descr']    = 'Hardware MTP Utilisation';

$config['graph_types']['device']['cisco-iosxcode']['section']  = 'voice';
$config['graph_types']['device']['cisco-iosxcode']['order']    = '0';
$config['graph_types']['device']['cisco-iosxcode']['descr']    = 'Transcoder Utilisation';

$config['graph_descr']['device_smokeping_in_all'] = 'This is an aggregate graph of the incoming smokeping tests to this host. The line corresponds to the average RTT. The shaded area around each line denotes the standard deviation.';
$config['graph_descr']['device_processor']        = 'This is an aggregate graph of all processors in the system.';

$config['graph_types']['device']['cisco_wwan_rssi']['section'] = 'wireless';
$config['graph_types']['device']['cisco_wwan_rssi']['order']   = '0';
$config['graph_types']['device']['cisco_wwan_rssi']['descr']   = 'Signal Rssi';
$config['graph_types']['device']['cisco_wwan_mnc']['section']  = 'wireless';
$config['graph_types']['device']['cisco_wwan_mnc']['order']    = '1';
$config['graph_types']['device']['cisco_wwan_mnc']['descr']    = 'MNC';

$config['graph_types']['device']['xirrus_stations']['section'] = 'wireless';
$config['graph_types']['device']['xirrus_stations']['order']   = '0';
$config['graph_types']['device']['xirrus_stations']['descr']   = 'Associated Stations';

$config['graph_types']['device']['sgos_average_requests']['section']  = 'network';
$config['graph_types']['device']['sgos_average_requests']['order']    = '0';
$config['graph_types']['device']['sgos_average_requests']['descr']    = 'Average HTTP Requests';

// SRX Flow Sessions
$config['graph_types']['device']['junos_jsrx_spu_flows'] = ['section' => 'network', 'order' => 0, 'descr' => 'SPU Flows'];
$config['graph_types']['device']['junos_jsrx_spu_sessions'] = ['section' => 'network', 'order' => 1, 'descr' => 'Flow Sessions'];

// Blue Coat SGOS
// Client Connections
$config['graph_types']['device']['bluecoat_http_client_connections']['section'] = 'network';
$config['graph_types']['device']['bluecoat_http_client_connections']['order']    = '0';
$config['graph_types']['device']['bluecoat_http_client_connections']['descr']    = 'HTTP Client Connections';
// Server Connections
$config['graph_types']['device']['bluecoat_http_server_connections']['section'] = 'network';
$config['graph_types']['device']['bluecoat_http_server_connections']['order']    = '0';
$config['graph_types']['device']['bluecoat_http_server_connections']['descr']    = 'HTTP Server Connections';

// Client Connections Active
$config['graph_types']['device']['bluecoat_http_client_connections_active']['section']  = 'network';
$config['graph_types']['device']['bluecoat_http_client_connections_active']['order']    = '0';
$config['graph_types']['device']['bluecoat_http_client_connections_active']['descr']    = 'HTTP Client Connections Active';
// Server Connections Active
$config['graph_types']['device']['bluecoat_http_server_connections_active']['section'] = 'network';
$config['graph_types']['device']['bluecoat_http_server_connections_active']['order']    = '0';
$config['graph_types']['device']['bluecoat_http_server_connections_active']['descr']    = 'HTTP Server Connections Active';

// Client Connections Idle
$config['graph_types']['device']['bluecoat_http_client_connections_idle']['section']  = 'network';
$config['graph_types']['device']['bluecoat_http_client_connections_idle']['order']    = '0';
$config['graph_types']['device']['bluecoat_http_client_connections_idle']['descr']    = 'HTTP Client Connections Idle';

// Server Connections Idle
$config['graph_types']['device']['bluecoat_http_server_connections_idle']['section']  = 'network';
$config['graph_types']['device']['bluecoat_http_server_connections_idle']['order']    = '0';
$config['graph_types']['device']['bluecoat_http_server_connections_idle']['descr']    = 'HTTP Server Connections Idle';


//riverbed specific graphs
$config['graph_types']['device']['riverbed_connections'] = ['section' => 'network', 'order' => 0, 'descr' => 'Connections'];
$config['graph_types']['device']['riverbed_optimization'] = ['section' => 'network', 'order' => 1, 'descr' => 'Optimization'];
$config['graph_types']['device']['riverbed_datastore'] = ['section' => 'network', 'order' => 2, 'descr' => 'Datastore productivity'];
$config['graph_types']['device']['riverbed_passthrough'] = ['section' => 'network', 'order' => 3, 'descr' => 'Bandwidth passthrough'];

//mikrotik specific graphs
$config['graph_types']['device']['routeros_leases']['section'] = 'network';
$config['graph_types']['device']['routeros_leases']['order'] = 0;
$config['graph_types']['device']['routeros_leases']['descr'] = 'DHCP Lease Count';

$config['graph_types']['device']['routeros_pppoe_sessions']['section'] = 'network';
$config['graph_types']['device']['routeros_pppoe_sessions']['order'] = 0;
$config['graph_types']['device']['routeros_pppoe_sessions']['descr'] = 'PPPoE Session Count';


//CheckPoint SPLAT specific graphs
$config['graph_types']['device']['secureplatform_sessions']['section'] = 'firewall';
$config['graph_types']['device']['secureplatform_sessions']['order'] = 0;
$config['graph_types']['device']['secureplatform_sessions']['descr'] = 'Active connections';

//arbos specific graphs
$config['graph_types']['device']['arbos_flows'] = ['section' => 'graphs', 'order' => 0, 'descr' => 'Accumulative flow count per SP device'];

//F5 specific graphs
$config['graph_types']['device']['bigip_apm_sessions'] = ['section' => 'apm', 'order' => 0, 'descr' => 'Active Sessions'];
$config['graph_types']['device']['bigip_system_tps'] = ['section' => 'ltm', 'order' => 0, 'descr' => 'SSL Transactions'];
$config['graph_types']['device']['bigip_system_server_concurrent_connections'] = ['section' => 'ltm', 'order' => 1, 'descr' => 'Global Server Concurrent Connections'];
$config['graph_types']['device']['bigip_system_client_concurrent_connections'] = ['section' => 'ltm', 'order' => 2, 'descr' => 'Global Client Concurrent Connections'];
$config['graph_types']['device']['bigip_system_server_connection_rate'] = ['section' => 'ltm', 'order' => 3, 'descr' => 'Global Server Connection Rate'];
$config['graph_types']['device']['bigip_system_client_connection_rate'] = ['section' => 'ltm', 'order' => 4, 'descr' => 'Global Client Connection Rate'];

// Bluecoat ProxySG Graphs
$config['graph_types']['device']['sgos_average_requests'] = ['section' => 'network', 'order' => 0, 'descr' => 'Average HTTP Requests'];
$config['graph_types']['device']['sgos_client_connections'] = ['section' => 'network', 'order' => 1, 'descr' => 'HTTP Client Connections'];
$config['graph_types']['device']['sgos_client_connections_active'] = ['section' => 'network', 'order' => 2, 'descr' => 'HTTP Client Connections Active'];
$config['graph_types']['device']['sgos_client_connections_idle'] = ['section' => 'network', 'order' => 3, 'descr' => 'HTTP Client Connections Idle'];
$config['graph_types']['device']['sgos_server_connections'] = ['section' => 'network', 'order' => 4, 'descr' => 'HTTP Server Connections'];
$config['graph_types']['device']['sgos_server_connections_active'] = ['section' => 'network', 'order' => 5, 'descr' => 'HTTP Server Connections Active'];
$config['graph_types']['device']['sgos_server_connections_idle'] = ['section' => 'network', 'order' => 6, 'descr' => 'HTTP Server Connections Idle'];

// Cisco AsyncOS Graphs
$config['graph_types']['device']['asyncos_conns'] = ['section' => 'proxy', 'order' => 0, 'descr' => 'Current connections'];

// Zywall Graphs
$config['graph_types']['device']['zywall_sessions'] = ['section' => 'firewall', 'order' => 0, 'descr' => 'Sessions'];

// TopVision Graphs
$config['graph_types']['device']['topvision_cmtotal'] = ['section' => 'cmts', 'order' => 0, 'descr' => 'Cable Modem Total'];
$config['graph_types']['device']['topvision_cmreg'] = ['section' => 'cmts', 'order' => 1, 'descr' => 'Cable Modem Registered'];
$config['graph_types']['device']['topvision_cmoffline'] = ['section' => 'cmts', 'order' => 2, 'descr' => 'Cable Modem Offline'];

// Teltonika RUT2XX Graph
$config['graph_types']['device']['rutos_2xx_mobileDataUsage'] = ['section' => 'network', 'order' => 0, 'descr' => 'Mobile Data Usage'];

// Device Types
$i = 0;
$config['device_types'][$i]['text'] = 'Servers';
$config['device_types'][$i]['type'] = 'server';
$config['device_types'][$i]['icon'] = 'server.png';

$i++;
$config['device_types'][$i]['text'] = 'Network';
$config['device_types'][$i]['type'] = 'network';
$config['device_types'][$i]['icon'] = 'network.png';

$i++;
$config['device_types'][$i]['text'] = 'Wireless';
$config['device_types'][$i]['type'] = 'wireless';
$config['device_types'][$i]['icon'] = 'wireless.png';

$i++;
$config['device_types'][$i]['text'] = 'Firewalls';
$config['device_types'][$i]['type'] = 'firewall';
$config['device_types'][$i]['icon'] = 'firewall.png';

$i++;
$config['device_types'][$i]['text'] = 'Power';
$config['device_types'][$i]['type'] = 'power';
$config['device_types'][$i]['icon'] = 'power.png';

$i++;
$config['device_types'][$i]['text'] = 'Environment';
$config['device_types'][$i]['type'] = 'environment';
$config['device_types'][$i]['icon'] = 'environment.png';

$i++;
$config['device_types'][$i]['text'] = 'Load Balancers';
$config['device_types'][$i]['type'] = 'loadbalancer';
$config['device_types'][$i]['icon'] = 'loadbalancer.png';

$i++;
$config['device_types'][$i]['text'] = 'Storage';
$config['device_types'][$i]['type'] = 'storage';
$config['device_types'][$i]['icon'] = 'storage.png';

$i++;
$config['device_types'][$i]['text'] = 'Printers';
$config['device_types'][$i]['type'] = 'printer';
$config['device_types'][$i]['icon'] = 'printer.png';

$i++;
$config['device_types'][$i]['text'] = 'Appliance';
$config['device_types'][$i]['type'] = 'appliance';
$config['device_types'][$i]['icon'] = 'appliance.png';

$i++;
$config['device_types'][$i]['text'] = 'Collaboration';
$config['device_types'][$i]['type'] = 'collaboration';
$config['device_types'][$i]['icon'] = 'collaboration.png';

$i++;
$config['device_types'][$i]['text'] = 'Workstation';
$config['device_types'][$i]['type'] = 'workstation';
$config['device_types'][$i]['icon'] = 'workstation.png';

//
// No changes below this line #
//
$config['project_name_version'] = $config['project_name'];

// Set some times needed by loads of scripts (it's dynamic, so we do it here!)
$config['time']['now']      = time();
$config['time']['now']     -= ($config['time']['now'] % 300);
$config['time']['onehour'] = ($config['time']['now'] - 3600);
// time() - (1 * 60 * 60);
$config['time']['fourhour'] = ($config['time']['now'] - 14400);
// time() - (4 * 60 * 60);
$config['time']['sixhour'] = ($config['time']['now'] - 21600);
// time() - (6 * 60 * 60);
$config['time']['twelvehour'] = ($config['time']['now'] - 43200);
// time() - (12 * 60 * 60);
$config['time']['day'] = ($config['time']['now'] - 86400);
// time() - (24 * 60 * 60);
$config['time']['twoday'] = ($config['time']['now'] - 172800);
// time() - (2 * 24 * 60 * 60);
$config['time']['week'] = ($config['time']['now'] - 604800);
// time() - (7 * 24 * 60 * 60);
$config['time']['twoweek'] = ($config['time']['now'] - 1209600);
// time() - (2 * 7 * 24 * 60 * 60);
$config['time']['month'] = ($config['time']['now'] - 2678400);
// time() - (31 * 24 * 60 * 60);
$config['time']['twomonth'] = ($config['time']['now'] - 5356800);
// time() - (2 * 31 * 24 * 60 * 60);
$config['time']['threemonth'] = ($config['time']['now'] - 8035200);
// time() - (3 * 31 * 24 * 60 * 60);
$config['time']['sixmonth'] = ($config['time']['now'] - 16070400);
// time() - (6 * 31 * 24 * 60 * 60);
$config['time']['year'] = ($config['time']['now'] - 31536000);
// time() - (365 * 24 * 60 * 60);
$config['time']['twoyear'] = ($config['time']['now'] - 63072000);
// time() - (2 * 365 * 24 * 60 * 60);
// IPMI sensor type mappings
$config['ipmi_unit']['Volts']     = 'voltage';
$config['ipmi_unit']['degrees C'] = 'temperature';
$config['ipmi_unit']['RPM']       = 'fanspeed';
$config['ipmi_unit']['Watts']     = 'power';
$config['ipmi_unit']['Amps']      = 'current';
$config['ipmi_unit']['percent']   = 'load';
$config['ipmi_unit']['discrete']  = '';
