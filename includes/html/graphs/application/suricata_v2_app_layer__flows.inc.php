<?php

$name = 'suricata';
$unit_text = 'flows/sec';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;

if (isset($vars['sinstance'])) {
    $app_layer__flow__bittorrent_dht_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__bittorrent-dht']);
    $app_layer__flow__dcerpc_tcp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__dcerpc_tcp']);
    $app_layer__flow__dcerpc_udp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__dcerpc_udp']);
    $app_layer__flow__dhcp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__dhcp']);
    $app_layer__flow__dnp3_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__dnp3']);
    $app_layer__flow__dns_tcp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__dns_tcp']);
    $app_layer__flow__dns_udp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__dns_udp']);
    $app_layer__flow__enip_tcp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__enip_tcp']);
    $app_layer__flow__enip_udp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__enip_udp']);
    $app_layer__flow__failed_tcp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__failed_tcp']);
    $app_layer__flow__failed_udp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__failed_udp']);
    $app_layer__flow__ftp_data_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__ftp-data']);
    $app_layer__flow__ftp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__ftp']);
    $app_layer__flow__http_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__http']);
    $app_layer__flow__http2_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__http2']);
    $app_layer__flow__ike_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__ike']);
    $app_layer__flow__imap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__imap']);
    $app_layer__flow__krb5_tcp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__krb5_tcp']);
    $app_layer__flow__krb5_udp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__krb5_udp']);
    $app_layer__flow__modbus_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__modbus']);
    $app_layer__flow__mqtt_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__mqtt']);
    $app_layer__flow__nfs_tcp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__nfs_tcp']);
    $app_layer__flow__nfs_udp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__nfs_udp']);
    $app_layer__flow__ntp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__ntp']);
    $app_layer__flow__pgsql_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__pgsql']);
    $app_layer__flow__quic_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__quic']);
    $app_layer__flow__rdp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__rdp']);
    $app_layer__flow__rfb_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__rfb']);
    $app_layer__flow__sip_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__sip']);
    $app_layer__flow__smb_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__smb']);
    $app_layer__flow__smtp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__smtp']);
    $app_layer__flow__snmp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__snmp']);
    $app_layer__flow__ssh_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__ssh']);
    $app_layer__flow__telnet_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__telnet']);
    $app_layer__flow__tftp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__tftp']);
    $app_layer__flow__tls_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__tls']);
} else {
    $app_layer__flow__bittorrent_dht_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__bittorrent-dht']);
    $app_layer__flow__dcerpc_tcp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__dcerpc_tcp']);
    $app_layer__flow__dcerpc_udp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__dcerpc_udp']);
    $app_layer__flow__dhcp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__dhcp']);
    $app_layer__flow__dnp3_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__dnp3']);
    $app_layer__flow__dns_tcp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__dns_tcp']);
    $app_layer__flow__dns_udp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__dns_udp']);
    $app_layer__flow__enip_tcp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__enip_tcp']);
    $app_layer__flow__enip_udp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__enip_udp']);
    $app_layer__flow__failed_tcp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__failed_tcp']);
    $app_layer__flow__failed_udp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__failed_udp']);
    $app_layer__flow__ftp_data_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__ftp-data']);
    $app_layer__flow__ftp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__ftp']);
    $app_layer__flow__http_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__http']);
    $app_layer__flow__http2_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__http2']);
    $app_layer__flow__ike_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__ike']);
    $app_layer__flow__imap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__imap']);
    $app_layer__flow__krb5_tcp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__krb5_tcp']);
    $app_layer__flow__krb5_udp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__krb5_udp']);
    $app_layer__flow__modbus_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__modbus']);
    $app_layer__flow__mqtt_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__mqtt']);
    $app_layer__flow__nfs_tcp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__nfs_tcp']);
    $app_layer__flow__nfs_udp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__nfs_udp']);
    $app_layer__flow__ntp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__ntp']);
    $app_layer__flow__pgsql_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__pgsql']);
    $app_layer__flow__quic_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__quic']);
    $app_layer__flow__rdp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__rdp']);
    $app_layer__flow__rfb_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__rfb']);
    $app_layer__flow__sip_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__sip']);
    $app_layer__flow__smb_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__smb']);
    $app_layer__flow__smtp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__smtp']);
    $app_layer__flow__snmp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__snmp']);
    $app_layer__flow__ssh_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__ssh']);
    $app_layer__flow__telnet_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__telnet']);
    $app_layer__flow__tftp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__tftp']);
    $app_layer__flow__tls_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__tls']);
}

$rrd_list = [];
if (Rrd::checkRrdExists($app_layer__flow__bittorrent_dht_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__flow__bittorrent_dht_rrd_filename,
        'descr' => 'BT DHT',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__flow__bittorrent_dht_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__flow__dcerpc_tcp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__flow__dcerpc_tcp_rrd_filename,
        'descr' => 'DCERPC, TCP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__flow__dcerpc_tcp_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__flow__dcerpc_udp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__flow__dcerpc_udp_rrd_filename,
        'descr' => 'DCERPC, UDP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__flow__dcerpc_udp_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__flow__dhcp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__flow__dhcp_rrd_filename,
        'descr' => 'DHCP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__flow__dhcp_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__flow__dnp3_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__flow__dnp3_rrd_filename,
        'descr' => 'DNP3',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__flow__dnp3_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__flow__dns_tcp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__flow__dns_tcp_rrd_filename,
        'descr' => 'DNS, TCP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__flow__dns_tcp_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__flow__dns_udp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__flow__dns_udp_rrd_filename,
        'descr' => 'DNS, UDP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__flow__dns_udp_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__flow__enip_tcp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__flow__enip_tcp_rrd_filename,
        'descr' => 'ENIP, TCP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__flow__enip_tcp_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__flow__enip_udp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__flow__enip_udp_rrd_filename,
        'descr' => 'ENIP, UDP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__flow__enip_udp_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__flow__failed_tcp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__flow__failed_tcp_rrd_filename,
        'descr' => 'Failed TCP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__flow__failed_tcp_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__flow__failed_udp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__flow__failed_udp_rrd_filename,
        'descr' => 'Failed UDP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__flow__failed_udp_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__flow__ftp_data_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__flow__ftp_data_rrd_filename,
        'descr' => 'FTP-Data',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__flow__ftp_data_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__flow__ftp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__flow__ftp_rrd_filename,
        'descr' => 'FTP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__flow__ftp_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__flow__http_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__flow__http_rrd_filename,
        'descr' => 'HTTP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__flow__http_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__flow__http2_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__flow__http2_rrd_filename,
        'descr' => 'HTTP2',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__flow__http2_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__flow__ike_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__flow__ike_rrd_filename,
        'descr' => 'IKE',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__flow__ike_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__flow__imap_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__flow__imap_rrd_filename,
        'descr' => 'IMAP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__flow__imap_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__flow__krb5_tcp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__flow__krb5_tcp_rrd_filename,
        'descr' => 'KRB5, TCP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__flow__krb5_tcp_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__flow__krb5_udp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__flow__krb5_udp_rrd_filename,
        'descr' => 'KRB5, UDP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__flow__krb5_udp_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__flow__modbus_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__flow__modbus_rrd_filename,
        'descr' => 'Modbus',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__flow__modbus_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__flow__mqtt_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__flow__mqtt_rrd_filename,
        'descr' => 'MQTT',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__flow__mqtt_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__flow__nfs_tcp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__flow__nfs_tcp_rrd_filename,
        'descr' => 'NFS, TCP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__flow__nfs_tcp_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__flow__nfs_udp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__flow__nfs_udp_rrd_filename,
        'descr' => 'NFS, UDP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__flow__nfs_udp_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__flow__ntp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__flow__ntp_rrd_filename,
        'descr' => 'NTP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__flow__ntp_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__flow__pgsql_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__flow__pgsql_rrd_filename,
        'descr' => 'PostgreSQL',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__flow__pgsql_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__flow__quic_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__flow__quic_rrd_filename,
        'descr' => 'QUIC',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__flow__quic_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__flow__rdp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__flow__rdp_rrd_filename,
        'descr' => 'RDP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__flow__rdp_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__flow__rfb_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__flow__rfb_rrd_filename,
        'descr' => 'RFB',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__flow__rfb_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__flow__sip_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__flow__sip_rrd_filename,
        'descr' => 'SIP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__flow__sip_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__flow__smb_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__flow__smb_rrd_filename,
        'descr' => 'SMB',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__flow__smb_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__flow__smtp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__flow__smtp_rrd_filename,
        'descr' => 'SMTP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__flow__smtp_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__flow__snmp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__flow__snmp_rrd_filename,
        'descr' => 'SNMP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__flow__snmp_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__flow__ssh_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__flow__ssh_rrd_filename,
        'descr' => 'SSH',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__flow__ssh_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__flow__telnet_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__flow__telnet_rrd_filename,
        'descr' => 'Telnet',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__flow__telnet_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__flow__tftp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__flow__tftp_rrd_filename,
        'descr' => 'TFTP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__flow__tftp_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__flow__tls_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__flow__tls_rrd_filename,
        'descr' => 'TLS',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__flow__tls_rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
