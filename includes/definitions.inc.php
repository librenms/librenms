<?php
//
// NO CHANGES TO THIS FILE, IT IS NOT USER-EDITABLE   #
//
// YES, THAT MEANS YOU                   #
//

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

// Device - Wireless - AirMAX
$config['graph_types']['device']['ubnt_airmax_WlStatStaCount']['section'] = 'wireless';
$config['graph_types']['device']['ubnt_airmax_WlStatStaCount']['order'] = '0';
$config['graph_types']['device']['ubnt_airmax_WlStatStaCount']['descr'] = 'Wireless Clients';

$config['graph_types']['device']['ubnt_airmax_RadioDistance']['section'] = 'wireless';
$config['graph_types']['device']['ubnt_airmax_RadioDistance']['order'] = '1';
$config['graph_types']['device']['ubnt_airmax_RadioDistance']['descr'] = 'Radio Distance';

$config['graph_types']['device']['ubnt_airmax_RadioFreq']['section'] = 'wireless';
$config['graph_types']['device']['ubnt_airmax_RadioFreq']['order'] = '2';
$config['graph_types']['device']['ubnt_airmax_RadioFreq']['descr'] = 'Radio Frequency';

$config['graph_types']['device']['ubnt_airmax_RadioTxPower']['section'] = 'wireless';
$config['graph_types']['device']['ubnt_airmax_RadioTxPower']['order'] = '3';
$config['graph_types']['device']['ubnt_airmax_RadioTxPower']['descr'] = 'Radio Tx Power';

$config['graph_types']['device']['ubnt_airmax_RadioRssi_0']['section'] = 'wireless';
$config['graph_types']['device']['ubnt_airmax_RadioRssi_0']['order'] = '4';
$config['graph_types']['device']['ubnt_airmax_RadioRssi_0']['descr'] = 'Radio Rssi Chain 0';

$config['graph_types']['device']['ubnt_airmax_RadioRssi_1']['section'] = 'wireless';
$config['graph_types']['device']['ubnt_airmax_RadioRssi_1']['order'] = '5';
$config['graph_types']['device']['ubnt_airmax_RadioRssi_1']['descr'] = 'Radio Rssi Chain 1';

$config['graph_types']['device']['ubnt_airmax_WlStatSignal']['section'] = 'wireless';
$config['graph_types']['device']['ubnt_airmax_WlStatSignal']['order'] = '6';
$config['graph_types']['device']['ubnt_airmax_WlStatSignal']['descr'] = 'Radio Signal';

$config['graph_types']['device']['ubnt_airmax_WlStatRssi']['section'] = 'wireless';
$config['graph_types']['device']['ubnt_airmax_WlStatRssi']['order'] = '7';
$config['graph_types']['device']['ubnt_airmax_WlStatRssi']['descr'] = 'Radio Overall RSSI';

$config['graph_types']['device']['ubnt_airmax_WlStatCcq']['section'] = 'wireless';
$config['graph_types']['device']['ubnt_airmax_WlStatCcq']['order'] = '8';
$config['graph_types']['device']['ubnt_airmax_WlStatCcq']['descr'] = 'Radio CCQ';

$config['graph_types']['device']['ubnt_airmax_WlStatNoiseFloor']['section'] = 'wireless';
$config['graph_types']['device']['ubnt_airmax_WlStatNoiseFloor']['order'] = '10';
$config['graph_types']['device']['ubnt_airmax_WlStatNoiseFloor']['descr'] = 'Radio Noise Floor';

$config['graph_types']['device']['ubnt_airmax_WlStatTxRate']['section'] = 'wireless';
$config['graph_types']['device']['ubnt_airmax_WlStatTxRate']['order'] = '11';
$config['graph_types']['device']['ubnt_airmax_WlStatTxRate']['descr'] = 'Radio Tx Rate';

$config['graph_types']['device']['ubnt_airmax_WlStatRxRate']['section'] = 'wireless';
$config['graph_types']['device']['ubnt_airmax_WlStatRxRate']['order'] = '12';
$config['graph_types']['device']['ubnt_airmax_WlStatRxRate']['descr'] = 'Radio Rx Rate';

$config['graph_types']['device']['ubnt_airmax_AirMaxQuality']['section'] = 'wireless';
$config['graph_types']['device']['ubnt_airmax_AirMaxQuality']['order'] = '13';
$config['graph_types']['device']['ubnt_airmax_AirMaxQuality']['descr'] = 'AirMax Quality';

$config['graph_types']['device']['ubnt_airmax_AirMaxCapacity']['section'] = 'wireless';
$config['graph_types']['device']['ubnt_airmax_AirMaxCapacity']['order'] = '14';
$config['graph_types']['device']['ubnt_airmax_AirMaxCapacity']['descr'] = 'AirMax Capacity';

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

// Unifi Support
$config['graph_types']['device']['ubnt_unifi_RadioCu_0']['section'] = 'wireless';
$config['graph_types']['device']['ubnt_unifi_RadioCu_0']['order'] = '0';
$config['graph_types']['device']['ubnt_unifi_RadioCu_0']['descr'] = 'Radio0 Capacity Used';

$config['graph_types']['device']['ubnt_unifi_RadioCu_1']['section'] = 'wireless';
$config['graph_types']['device']['ubnt_unifi_RadioCu_1']['order'] = '1';
$config['graph_types']['device']['ubnt_unifi_RadioCu_1']['descr'] = 'Radio1 Capacity Used';

// Siklu support
$config['graph_types']['device']['siklu_rfAverageRssi']['section'] = 'wireless';
$config['graph_types']['device']['siklu_rfAverageRssi']['order'] = '0';
$config['graph_types']['device']['siklu_rfAverageRssi']['descr'] = 'Radio Average RSSI';

$config['graph_types']['device']['siklu_rfAverageCinr']['section'] = 'wireless';
$config['graph_types']['device']['siklu_rfAverageCinr']['order'] = '1';
$config['graph_types']['device']['siklu_rfAverageCinr']['descr'] = 'Radio Average CINR';

$config['graph_types']['device']['siklu_rfOperationalFrequency']['section'] = 'wireless';
$config['graph_types']['device']['siklu_rfOperationalFrequency']['order'] = '2';
$config['graph_types']['device']['siklu_rfOperationalFrequency']['descr'] = 'Operational Frequency';

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

// SAF support
$config['graph_types']['device']['saf_radioRxLevel']['section'] = 'wireless';
$config['graph_types']['device']['saf_radioRxLevel']['order'] = '0';
$config['graph_types']['device']['saf_radioRxLevel']['descr'] = 'RX Level';

$config['graph_types']['device']['saf_radioTxPower']['section'] = 'wireless';
$config['graph_types']['device']['saf_radioTxPower']['order'] = '1';
$config['graph_types']['device']['saf_radioTxPower']['descr'] = 'TX Power';

$config['graph_types']['device']['saf_modemRadialMSE']['section'] = 'wireless';
$config['graph_types']['device']['saf_modemRadialMSE']['order'] = '2';
$config['graph_types']['device']['saf_modemRadialMSE']['descr'] = 'Radial MSE';

$config['graph_types']['device']['saf_modemCapacity']['section'] = 'wireless';
$config['graph_types']['device']['saf_modemCapacity']['order'] = '3';
$config['graph_types']['device']['saf_modemCapacity']['descr'] = 'Capacity';

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
$config['graph_types']['device']['cambium_650_rawReceivePower']['section'] = 'wireless';
$config['graph_types']['device']['cambium_650_rawReceivePower']['order']   = '0';
$config['graph_types']['device']['cambium_650_rawReceivePower']['descr']   = 'Raw Receive Power';
$config['graph_types']['device']['cambium_650_transmitPower']['section'] = 'wireless';
$config['graph_types']['device']['cambium_650_transmitPower']['order']   = '1';
$config['graph_types']['device']['cambium_650_transmitPower']['descr']   = 'Transmit Power';
$config['graph_types']['device']['cambium_650_modulationMode']['section'] = 'wireless';
$config['graph_types']['device']['cambium_650_modulationMode']['order']   = '2';
$config['graph_types']['device']['cambium_650_modulationMode']['descr']   = 'Moduation Mode';
$config['graph_types']['device']['cambium_650_dataRate']['section'] = 'wireless';
$config['graph_types']['device']['cambium_650_dataRate']['order']   = '3';
$config['graph_types']['device']['cambium_650_dataRate']['descr']   = 'Data Rate';
$config['graph_types']['device']['cambium_650_ssr']['section'] = 'wireless';
$config['graph_types']['device']['cambium_650_ssr']['order']   = '4';
$config['graph_types']['device']['cambium_650_ssr']['descr']   = 'Signal Strength Ratio';
$config['graph_types']['device']['cambium_650_gps']['section'] = 'wireless';
$config['graph_types']['device']['cambium_650_gps']['order']   = '5';
$config['graph_types']['device']['cambium_650_gps']['descr']   = 'GPS Status';

$config['graph_types']['device']['cambium_250_receivePower']['section'] = 'wireless';
$config['graph_types']['device']['cambium_250_receivePower']['order']   = '0';
$config['graph_types']['device']['cambium_250_receivePower']['descr']   = 'Raw Receive Power';
$config['graph_types']['device']['cambium_250_transmitPower']['section'] = 'wireless';
$config['graph_types']['device']['cambium_250_transmitPower']['order']   = '1';
$config['graph_types']['device']['cambium_250_transmitPower']['descr']   = 'Transmit Power';
$config['graph_types']['device']['cambium_250_modulationMode']['section'] = 'wireless';
$config['graph_types']['device']['cambium_250_modulationMode']['order']   = '2';
$config['graph_types']['device']['cambium_250_modulationMode']['descr']   = 'Moduation Mode';
$config['graph_types']['device']['cambium_250_dataRate']['section'] = 'wireless';
$config['graph_types']['device']['cambium_250_dataRate']['order']   = '3';
$config['graph_types']['device']['cambium_250_dataRate']['descr']   = 'Data Rate';
$config['graph_types']['device']['cambium_250_ssr']['section'] = 'wireless';
$config['graph_types']['device']['cambium_250_ssr']['order']   = '4';
$config['graph_types']['device']['cambium_250_ssr']['descr']   = 'Signal Strength Ratio';

$config['graph_types']['device']['canopy_generic_whispGPSStats']['section'] = 'wireless';
$config['graph_types']['device']['canopy_generic_whispGPSStats']['order']   = '0';
$config['graph_types']['device']['canopy_generic_whispGPSStats']['descr']   = 'GPS Status';
$config['graph_types']['device']['canopy_generic_gpsStats']['section'] = 'wireless';
$config['graph_types']['device']['canopy_generic_gpsStats']['order']   = '0';
$config['graph_types']['device']['canopy_generic_gpsStats']['descr']   = 'GPS Stats';
$config['graph_types']['device']['canopy_generic_rssi']['section'] = 'wireless';
$config['graph_types']['device']['canopy_generic_rssi']['order']   = '1';
$config['graph_types']['device']['canopy_generic_rssi']['descr']   = 'Signal Rssi';
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
$config['graph_types']['device']['canopy_generic_450_slaveSNR']['section'] = 'wireless';
$config['graph_types']['device']['canopy_generic_450_slaveSNR']['order']   = '8';
$config['graph_types']['device']['canopy_generic_450_slaveSNR']['descr']   = 'SNR';
$config['graph_types']['device']['canopy_generic_450_slaveSSR']['section'] = 'wireless';
$config['graph_types']['device']['canopy_generic_450_slaveSSR']['order']   = '9';
$config['graph_types']['device']['canopy_generic_450_slaveSSR']['descr']   = 'SSR';
$config['graph_types']['device']['canopy_generic_450_masterSSR']['section'] = 'wireless';
$config['graph_types']['device']['canopy_generic_450_masterSSR']['order']   = '10';
$config['graph_types']['device']['canopy_generic_450_masterSSR']['descr']   = 'Master SSR';
$config['graph_types']['device']['canopy_generic_regCount']['section'] = 'wireless';
$config['graph_types']['device']['canopy_generic_regCount']['order']   = '11';
$config['graph_types']['device']['canopy_generic_regCount']['descr']   = 'Registered SM';
$config['graph_types']['device']['canopy_generic_freq']['section'] = 'wireless';
$config['graph_types']['device']['canopy_generic_freq']['order']   = '12';
$config['graph_types']['device']['canopy_generic_freq']['descr']   = 'Radio Frequency';
$config['graph_types']['device']['canopy_generic_radioDbm']['section'] = 'wireless';
$config['graph_types']['device']['canopy_generic_radioDbm']['order']   = '13';
$config['graph_types']['device']['canopy_generic_radioDbm']['descr']   = 'Radio Dbm';
$config['graph_types']['device']['canopy_generic_errorCount']['section'] = 'wireless';
$config['graph_types']['device']['canopy_generic_errorCount']['order']   = '14';
$config['graph_types']['device']['canopy_generic_errorCount']['descr']   = 'Error Count';
$config['graph_types']['device']['canopy_generic_crcErrors']['section'] = 'wireless';
$config['graph_types']['device']['canopy_generic_crcErrors']['order']   = '15';
$config['graph_types']['device']['canopy_generic_crcErrors']['descr']   = 'CRC Errors';

$config['graph_types']['device']['cambium_epmp_RFStatus']['section'] = 'wireless';
$config['graph_types']['device']['cambium_epmp_RFStatus']['order']   = '0';
$config['graph_types']['device']['cambium_epmp_RFStatus']['descr']   = 'RF Status';
$config['graph_types']['device']['cambium_epmp_gps']['section'] = 'wireless';
$config['graph_types']['device']['cambium_epmp_gps']['order']   = '1';
$config['graph_types']['device']['cambium_epmp_gps']['descr']   = 'GPS Info';
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

$config['graph_types']['device']['wifi_clients']['section'] = 'wireless';
$config['graph_types']['device']['wifi_clients']['order']   = '0';
$config['graph_types']['device']['wifi_clients']['descr']   = 'Wireless Clients';

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

//PAN OS Graphs
$config['graph_types']['device']['panos_sessions']['section']      = 'firewall';
$config['graph_types']['device']['panos_sessions']['order']        = '0';
$config['graph_types']['device']['panos_sessions']['descr']        = 'Active Sessions';
$config['graph_types']['device']['panos_activetunnels']['section'] = 'firewall';
$config['graph_types']['device']['panos_activetunnels']['order']   = '1';
$config['graph_types']['device']['panos_activetunnels']['descr']   = 'Active GlobalProtect Tunnels';

//Pulse Secure Graphs
$config['graph_types']['device']['pulse_users']['section']         = 'firewall';
$config['graph_types']['device']['pulse_users']['order']           = '0';
$config['graph_types']['device']['pulse_users']['descr']           = 'Active Users';
$config['graph_types']['device']['pulse_sessions']['section']      = 'firewall';
$config['graph_types']['device']['pulse_sessions']['order']        = '0';
$config['graph_types']['device']['pulse_sessions']['descr']        = 'Active Sessions';

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
$config['graph_types']['device']['waas_cwotfostatsactiveoptconn']['section']      = 'graphs';
$config['graph_types']['device']['waas_cwotfostatsactiveoptconn']['order']        = '0';
$config['graph_types']['device']['waas_cwotfostatsactiveoptconn']['descr']        = 'Optimized TCP Connections';

// SonicWALL Sessions
$config['graph_types']['device']['sonicwall_sessions']['section']      = 'firewall';
$config['graph_types']['device']['sonicwall_sessions']['order']        = '0';
$config['graph_types']['device']['sonicwall_sessions']['descr']        = 'Active Sessions';

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

$config['graph_types']['device']['asa_conns']['section'] = 'firewall';
$config['graph_types']['device']['asa_conns']['order']   = '0';
$config['graph_types']['device']['asa_conns']['descr']   = 'Current connections';

$config['graph_types']['device']['cisco-iospri']['section']  = 'voice';
$config['graph_types']['device']['cisco-iospri']['order']    = '0';
$config['graph_types']['device']['cisco-iospri']['descr']    = 'PRI Utilisation';

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

$config['graph_types']['device']['xirrus_rssi']['section'] = 'wireless';
$config['graph_types']['device']['xirrus_rssi']['order']   = '0';
$config['graph_types']['device']['xirrus_rssi']['descr']   = 'Signal Rssi';
$config['graph_types']['device']['xirrus_dataRates']['section'] = 'wireless';
$config['graph_types']['device']['xirrus_dataRates']['order']   = '0';
$config['graph_types']['device']['xirrus_dataRates']['descr']   = 'Average DataRates';
$config['graph_types']['device']['xirrus_noiseFloor']['section'] = 'wireless';
$config['graph_types']['device']['xirrus_noiseFloor']['order']   = '0';
$config['graph_types']['device']['xirrus_noiseFloor']['descr']   = 'Noise Floor';
$config['graph_types']['device']['xirrus_stations']['section'] = 'wireless';
$config['graph_types']['device']['xirrus_stations']['order']   = '0';
$config['graph_types']['device']['xirrus_stations']['descr']   = 'Associated Stations';

$config['graph_types']['device']['sgos_average_requests']['section']  = 'network';
$config['graph_types']['device']['sgos_average_requests']['order']    = '0';
$config['graph_types']['device']['sgos_average_requests']['descr']    = 'Average HTTP Requests';

//riverbed specific graphs
$config['graph_types']['device']['riverbed_connections']['section'] = 'network';
$config['graph_types']['device']['riverbed_connections']['order'] = 0;
$config['graph_types']['device']['riverbed_connections']['descr'] = 'Connections';
$config['graph_types']['device']['riverbed_optimization']['section'] = 'network';
$config['graph_types']['device']['riverbed_optimization']['order'] = 1;
$config['graph_types']['device']['riverbed_optimization']['descr'] = 'Optimization';
$config['graph_types']['device']['riverbed_datastore']['section'] = 'network';
$config['graph_types']['device']['riverbed_datastore']['order'] = 2;
$config['graph_types']['device']['riverbed_datastore']['descr'] = 'Data store productivity';
$config['graph_types']['device']['riverbed_passthrough']['section'] = 'network';
$config['graph_types']['device']['riverbed_passthrough']['order'] = 3;
$config['graph_types']['device']['riverbed_passthrough']['descr'] = 'Bandwidth Passthrough';

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

//
// No changes below this line #
//
$config['project_name_version'] = $config['project_name'];

if (isset($config['rrdgraph_def_text'])) {
    $config['rrdgraph_def_text'] = str_replace('  ', ' ', $config['rrdgraph_def_text']);
    $config['rrd_opts_array']    = explode(' ', trim($config['rrdgraph_def_text']));
}

if (isset($config['cdp_autocreate'])) {
    $config['dp_autocreate'] = $config['cdp_autocreate'];
}

if (!isset($config['mibdir'])) {
    $config['mibdir'] = $config['install_dir'].'/mibs';
}

$config['mib_dir'] = $config['mibdir'];

// If we're on SSL, let's properly detect it
if (isset($_SERVER['HTTPS'])) {
    $config['base_url'] = preg_replace('/^http:/', 'https:', $config['base_url']);
}

// Set some times needed by loads of scripts (it's dynamic, so we do it here!)
$config['time']['now']      = time();
$config['time']['now']     -= ($config['time']['now'] % 300);
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
$config['ipmi_unit']['discrete']  = '';

// Define some variables if they aren't set by user definition in config.php
if (!isset($config['html_dir'])) {
    $config['html_dir'] = $config['install_dir'].'/html';
}

if (!isset($config['rrd_dir'])) {
    $config['rrd_dir'] = $config['install_dir'].'/rrd';
}

if (!isset($config['log_dir'])) {
    $config['log_dir'] = $config['install_dir'].'/logs';
}

if (!isset($config['log_file'])) {
    $config['log_file'] = $config['log_dir'].'/'.$config['project_id'].'.log';
}

if (!isset($config['plugin_dir'])) {
    $config['plugin_dir'] = $config['html_dir'].'/plugins';
}
