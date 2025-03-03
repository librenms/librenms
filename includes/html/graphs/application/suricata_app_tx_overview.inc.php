<?php

$name = 'suricata';
$unit_text = 'packets/sec';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id]);
$app_layer__tx__bittorrent_dht_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__bittorrent-dht']);
$app_layer__tx__dcerpc_tcp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__dcerpc_tcp']);
$app_layer__tx__dcerpc_udp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__dcerpc_udp']);
$app_layer__tx__dhcp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__dhcp']);
$app_layer__tx__dnp3_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__dnp3']);
$app_layer__tx__dns_tcp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__dns_tcp']);
$app_layer__tx__dns_udp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__dns_udp']);
$app_layer__tx__enip_tcp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__enip_tcp']);
$app_layer__tx__enip_udp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__enip_udp']);
$app_layer__tx__ftp_data_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__ftp-data']);
$app_layer__tx__ftp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__ftp']);
$app_layer__tx__http_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__http']);
$app_layer__tx__http2_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__http2']);
$app_layer__tx__ike_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__ike']);
$app_layer__tx__imap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__imap']);
$app_layer__tx__krb5_tcp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__krb5_tcp']);
$app_layer__tx__krb5_udp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__krb5_udp']);
$app_layer__tx__modbus_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__modbus']);
$app_layer__tx__mqtt_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__mqtt']);
$app_layer__tx__nfs_tcp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__nfs_tcp']);
$app_layer__tx__nfs_udp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__nfs_udp']);
$app_layer__tx__ntp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__ntp']);
$app_layer__tx__pgsql_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__pgsql']);
$app_layer__tx__quic_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__quic']);
$app_layer__tx__rdp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__rdp']);
$app_layer__tx__rfb_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__rfb']);
$app_layer__tx__sip_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__sip']);
$app_layer__tx__smb_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__smb']);
$app_layer__tx__smtp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__smtp']);
$app_layer__tx__snmp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__snmp']);
$app_layer__tx__ssh_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__ssh']);
$app_layer__tx__telnet_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__telnet']);
$app_layer__tx__tftp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__tftp']);
$app_layer__tx__tls_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__tls']);

$rrd_list = [];
if (Rrd::checkRrdExists($app_layer__tx__bittorrent_dht_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__bittorrent_dht_rrd_filename,
        'descr' => 'BT DHT',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($app_layer__tx__dcerpc_tcp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__dcerpc_tcp_rrd_filename,
        'descr' => 'DCERPC, TCP',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($app_layer__tx__dcerpc_udp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__dcerpc_udp_rrd_filename,
        'descr' => 'DCERPC, UDP',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($app_layer__tx__dhcp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__dhcp_rrd_filename,
        'descr' => 'DHCP',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($app_layer__tx__dnp3_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__dnp3_rrd_filename,
        'descr' => 'DNP3',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($app_layer__tx__dns_tcp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__dns_tcp_rrd_filename,
        'descr' => 'DNS, TCP',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($app_layer__tx__dns_udp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__dns_udp_rrd_filename,
        'descr' => 'DNS, UDP',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($app_layer__tx__enip_tcp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__enip_tcp_rrd_filename,
        'descr' => 'ENIP, TCP',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($app_layer__tx__enip_udp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__enip_udp_rrd_filename,
        'descr' => 'ENIP, UDP',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($app_layer__tx__ftp_data_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__ftp_data_rrd_filename,
        'descr' => 'FTP-Data',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($app_layer__tx__ftp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__ftp_rrd_filename,
        'descr' => 'FTP',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($app_layer__tx__http_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__http_rrd_filename,
        'descr' => 'HTTP',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($app_layer__tx__http2_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__http2_rrd_filename,
        'descr' => 'HTTP2',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($app_layer__tx__ike_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__ike_rrd_filename,
        'descr' => 'IKE',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($app_layer__tx__imap_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__imap_rrd_filename,
        'descr' => 'IMAP',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($app_layer__tx__krb5_tcp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__krb5_tcp_rrd_filename,
        'descr' => 'KRB5, TCP',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($app_layer__tx__krb5_udp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__krb5_udp_rrd_filename,
        'descr' => 'KRB5, UDP',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($app_layer__tx__modbus_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__modbus_rrd_filename,
        'descr' => 'Modbus',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($app_layer__tx__mqtt_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__mqtt_rrd_filename,
        'descr' => 'MQTT',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($app_layer__tx__nfs_tcp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__nfs_tcp_rrd_filename,
        'descr' => 'NFS, TCP',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($app_layer__tx__nfs_udp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__nfs_udp_rrd_filename,
        'descr' => 'NFS, UDP',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($app_layer__tx__ntp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__ntp_rrd_filename,
        'descr' => 'NTP',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($app_layer__tx__pgsql_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__pgsql_rrd_filename,
        'descr' => 'PostgreSQL',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($app_layer__tx__quic_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__quic_rrd_filename,
        'descr' => 'QUIC',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($app_layer__tx__rdp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__rdp_rrd_filename,
        'descr' => 'RDP',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($app_layer__tx__rfb_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__rfb_rrd_filename,
        'descr' => 'RFB',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($app_layer__tx__sip_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__sip_rrd_filename,
        'descr' => 'SIP',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($app_layer__tx__smb_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__smb_rrd_filename,
        'descr' => 'SMB',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($app_layer__tx__smtp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__smtp_rrd_filename,
        'descr' => 'SMTP',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($app_layer__tx__snmp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__snmp_rrd_filename,
        'descr' => 'SNMP',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($app_layer__tx__ssh_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__ssh_rrd_filename,
        'descr' => 'SSH',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($app_layer__tx__telnet_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__telnet_rrd_filename,
        'descr' => 'Telnet',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($app_layer__tx__tftp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__tftp_rrd_filename,
        'descr' => 'TFTP',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($app_layer__tx__tls_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__tls_rrd_filename,
        'descr' => 'TLS',
        'ds' => 'data',
    ];
}
if (! isset($rrd_list[0]) && Rrd::checkRrdExists($rrd_filename)) {
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'DCERPC TCP',
        'ds' => 'at_dcerpc_tcp',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'DCERPC UDP',
        'ds' => 'at_dcerpc_udp',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'DHCP',
        'ds' => 'at_dhcp',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'DNS TCP',
        'ds' => 'at_dns_tcp',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'DNS UDP',
        'ds' => 'at_dns_udp',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'FTP',
        'ds' => 'at_ftp',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'FTP-DATA',
        'ds' => 'at_ftp_data',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'HTTP',
        'ds' => 'at_http',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'IKEv2',
        'ds' => 'at_ikev2',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'IMAP',
        'ds' => 'at_imap',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'Krb5 TCP',
        'ds' => 'at_krb5_tcp',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'Krb5 UDP',
        'ds' => 'at_krb5_udp',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'MQTT',
        'ds' => 'at_mqtt',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'NFS TCP',
        'ds' => 'at_nfs_tcp',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'NFS UDP',
        'ds' => 'at_nfs_udp',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'RDP',
        'ds' => 'at_rdp',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'RFB',
        'ds' => 'at_rfb',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'SIP',
        'ds' => 'at_sip',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'SMB',
        'ds' => 'at_smb',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'SMTP',
        'ds' => 'at_smtp',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'SNMP',
        'ds' => 'at_snmp',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'ssh',
        'ds' => 'at_ssh',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'TFTP',
        'ds' => 'at_tftp',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'TLS',
        'ds' => 'at_tls',
    ];
}
if (! isset($rrd_list[0])) {
    d_echo('RRD "' . $app_layer__tx__tls_rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
