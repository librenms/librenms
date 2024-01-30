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
} else {
    $app_layer__flow__bittorrent_dht_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__bittorrent-dht']);
}
if (isset($vars['sinstance'])) {
    $app_layer__flow__dcerpc_tcp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__dcerpc_tcp']);
} else {
    $app_layer__flow__dcerpc_tcp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__dcerpc_tcp']);
}
if (isset($vars['sinstance'])) {
    $app_layer__flow__dcerpc_udp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__dcerpc_udp']);
} else {
    $app_layer__flow__dcerpc_udp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__dcerpc_udp']);
}
if (isset($vars['sinstance'])) {
    $app_layer__flow__dhcp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__dhcp']);
} else {
    $app_layer__flow__dhcp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__dhcp']);
}
if (isset($vars['sinstance'])) {
    $app_layer__flow__dnp3_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__dnp3']);
} else {
    $app_layer__flow__dnp3_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__dnp3']);
}
if (isset($vars['sinstance'])) {
    $app_layer__flow__dns_tcp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__dns_tcp']);
} else {
    $app_layer__flow__dns_tcp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__dns_tcp']);
}
if (isset($vars['sinstance'])) {
    $app_layer__flow__dns_udp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__dns_udp']);
} else {
    $app_layer__flow__dns_udp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__dns_udp']);
}
if (isset($vars['sinstance'])) {
    $app_layer__flow__enip_tcp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__enip_tcp']);
} else {
    $app_layer__flow__enip_tcp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__enip_tcp']);
}
if (isset($vars['sinstance'])) {
    $app_layer__flow__enip_udp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__enip_udp']);
} else {
    $app_layer__flow__enip_udp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__enip_udp']);
}
if (isset($vars['sinstance'])) {
    $app_layer__flow__failed_tcp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__failed_tcp']);
} else {
    $app_layer__flow__failed_tcp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__failed_tcp']);
}
if (isset($vars['sinstance'])) {
    $app_layer__flow__failed_udp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__failed_udp']);
} else {
    $app_layer__flow__failed_udp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__failed_udp']);
}
if (isset($vars['sinstance'])) {
    $app_layer__flow__ftp_data_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__ftp-data']);
} else {
    $app_layer__flow__ftp_data_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__ftp-data']);
}
if (isset($vars['sinstance'])) {
    $app_layer__flow__ftp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__ftp']);
} else {
    $app_layer__flow__ftp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__ftp']);
}
if (isset($vars['sinstance'])) {
    $app_layer__flow__http_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__http']);
} else {
    $app_layer__flow__http_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__http']);
}
if (isset($vars['sinstance'])) {
    $app_layer__flow__http2_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__http2']);
} else {
    $app_layer__flow__http2_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__http2']);
}
if (isset($vars['sinstance'])) {
    $app_layer__flow__ike_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__ike']);
} else {
    $app_layer__flow__ike_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__ike']);
}
if (isset($vars['sinstance'])) {
    $app_layer__flow__imap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__imap']);
} else {
    $app_layer__flow__imap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__imap']);
}
if (isset($vars['sinstance'])) {
    $app_layer__flow__krb5_tcp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__krb5_tcp']);
} else {
    $app_layer__flow__krb5_tcp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__krb5_tcp']);
}
if (isset($vars['sinstance'])) {
    $app_layer__flow__krb5_udp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__krb5_udp']);
} else {
    $app_layer__flow__krb5_udp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__krb5_udp']);
}
if (isset($vars['sinstance'])) {
    $app_layer__flow__modbus_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__modbus']);
} else {
    $app_layer__flow__modbus_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__modbus']);
}
if (isset($vars['sinstance'])) {
    $app_layer__flow__mqtt_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__mqtt']);
} else {
    $app_layer__flow__mqtt_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__mqtt']);
}
if (isset($vars['sinstance'])) {
    $app_layer__flow__nfs_tcp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__nfs_tcp']);
} else {
    $app_layer__flow__nfs_tcp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__nfs_tcp']);
}
if (isset($vars['sinstance'])) {
    $app_layer__flow__nfs_udp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__nfs_udp']);
} else {
    $app_layer__flow__nfs_udp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__nfs_udp']);
}
if (isset($vars['sinstance'])) {
    $app_layer__flow__ntp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__ntp']);
} else {
    $app_layer__flow__ntp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__ntp']);
}
if (isset($vars['sinstance'])) {
    $app_layer__flow__pgsql_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__pgsql']);
} else {
    $app_layer__flow__pgsql_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__pgsql']);
}
if (isset($vars['sinstance'])) {
    $app_layer__flow__quic_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__quic']);
} else {
    $app_layer__flow__quic_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__quic']);
}
if (isset($vars['sinstance'])) {
    $app_layer__flow__rdp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__rdp']);
} else {
    $app_layer__flow__rdp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__rdp']);
}
if (isset($vars['sinstance'])) {
    $app_layer__flow__rfb_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__rfb']);
} else {
    $app_layer__flow__rfb_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__rfb']);
}
if (isset($vars['sinstance'])) {
    $app_layer__flow__sip_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__sip']);
} else {
    $app_layer__flow__sip_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__sip']);
}
if (isset($vars['sinstance'])) {
    $app_layer__flow__smb_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__smb']);
} else {
    $app_layer__flow__smb_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__smb']);
}
if (isset($vars['sinstance'])) {
    $app_layer__flow__smtp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__smtp']);
} else {
    $app_layer__flow__smtp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__smtp']);
}
if (isset($vars['sinstance'])) {
    $app_layer__flow__snmp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__snmp']);
} else {
    $app_layer__flow__snmp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__snmp']);
}
if (isset($vars['sinstance'])) {
    $app_layer__flow__ssh_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__ssh']);
} else {
    $app_layer__flow__ssh_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__ssh']);
}
if (isset($vars['sinstance'])) {
    $app_layer__flow__telnet_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__telnet']);
} else {
    $app_layer__flow__telnet_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__telnet']);
}
if (isset($vars['sinstance'])) {
    $app_layer__flow__tftp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__tftp']);
} else {
    $app_layer__flow__tftp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__tftp']);
}
if (isset($vars['sinstance'])) {
    $app_layer__flow__tls_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__flow__tls']);
} else {
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
