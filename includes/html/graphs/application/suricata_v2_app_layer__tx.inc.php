<?php

$name = 'suricata';
$unit_text = 'packets/sec';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;

if (isset($vars['sinstance'])) {
    $app_layer__tx__bittorrent_dht_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__tx__bittorrent-dht']);
} else {
    $app_layer__tx__bittorrent_dht_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__bittorrent-dht']);
}
if (isset($vars['sinstance'])) {
    $app_layer__tx__dcerpc_tcp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__tx__dcerpc_tcp']);
} else {
    $app_layer__tx__dcerpc_tcp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__dcerpc_tcp']);
}
if (isset($vars['sinstance'])) {
    $app_layer__tx__dcerpc_udp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__tx__dcerpc_udp']);
} else {
    $app_layer__tx__dcerpc_udp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__dcerpc_udp']);
}
if (isset($vars['sinstance'])) {
    $app_layer__tx__dhcp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__tx__dhcp']);
} else {
    $app_layer__tx__dhcp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__dhcp']);
}
if (isset($vars['sinstance'])) {
    $app_layer__tx__dnp3_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__tx__dnp3']);
} else {
    $app_layer__tx__dnp3_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__dnp3']);
}
if (isset($vars['sinstance'])) {
    $app_layer__tx__dns_tcp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__tx__dns_tcp']);
} else {
    $app_layer__tx__dns_tcp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__dns_tcp']);
}
if (isset($vars['sinstance'])) {
    $app_layer__tx__dns_udp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__tx__dns_udp']);
} else {
    $app_layer__tx__dns_udp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__dns_udp']);
}
if (isset($vars['sinstance'])) {
    $app_layer__tx__enip_tcp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__tx__enip_tcp']);
} else {
    $app_layer__tx__enip_tcp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__enip_tcp']);
}
if (isset($vars['sinstance'])) {
    $app_layer__tx__enip_udp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__tx__enip_udp']);
} else {
    $app_layer__tx__enip_udp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__enip_udp']);
}
if (isset($vars['sinstance'])) {
    $app_layer__tx__failed_tcp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__tx__failed_tcp']);
} else {
    $app_layer__tx__failed_tcp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__failed_tcp']);
}
if (isset($vars['sinstance'])) {
    $app_layer__tx__failed_udp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__tx__failed_udp']);
} else {
    $app_layer__tx__failed_udp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__failed_udp']);
}
if (isset($vars['sinstance'])) {
    $app_layer__tx__ftp_data_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__tx__ftp-data']);
} else {
    $app_layer__tx__ftp_data_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__ftp-data']);
}
if (isset($vars['sinstance'])) {
    $app_layer__tx__ftp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__tx__ftp']);
} else {
    $app_layer__tx__ftp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__ftp']);
}
if (isset($vars['sinstance'])) {
    $app_layer__tx__http_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__tx__http']);
} else {
    $app_layer__tx__http_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__http']);
}
if (isset($vars['sinstance'])) {
    $app_layer__tx__http2_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__tx__http2']);
} else {
    $app_layer__tx__http2_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__http2']);
}
if (isset($vars['sinstance'])) {
    $app_layer__tx__ike_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__tx__ike']);
} else {
    $app_layer__tx__ike_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__ike']);
}
if (isset($vars['sinstance'])) {
    $app_layer__tx__imap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__tx__imap']);
} else {
    $app_layer__tx__imap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__imap']);
}
if (isset($vars['sinstance'])) {
    $app_layer__tx__krb5_tcp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__tx__krb5_tcp']);
} else {
    $app_layer__tx__krb5_tcp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__krb5_tcp']);
}
if (isset($vars['sinstance'])) {
    $app_layer__tx__krb5_udp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__tx__krb5_udp']);
} else {
    $app_layer__tx__krb5_udp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__krb5_udp']);
}
if (isset($vars['sinstance'])) {
    $app_layer__tx__modbus_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__tx__modbus']);
} else {
    $app_layer__tx__modbus_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__modbus']);
}
if (isset($vars['sinstance'])) {
    $app_layer__tx__mqtt_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__tx__mqtt']);
} else {
    $app_layer__tx__mqtt_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__mqtt']);
}
if (isset($vars['sinstance'])) {
    $app_layer__tx__nfs_tcp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__tx__nfs_tcp']);
} else {
    $app_layer__tx__nfs_tcp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__nfs_tcp']);
}
if (isset($vars['sinstance'])) {
    $app_layer__tx__nfs_udp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__tx__nfs_udp']);
} else {
    $app_layer__tx__nfs_udp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__nfs_udp']);
}
if (isset($vars['sinstance'])) {
    $app_layer__tx__ntp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__tx__ntp']);
} else {
    $app_layer__tx__ntp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__ntp']);
}
if (isset($vars['sinstance'])) {
    $app_layer__tx__pgsql_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__tx__pgsql']);
} else {
    $app_layer__tx__pgsql_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__pgsql']);
}
if (isset($vars['sinstance'])) {
    $app_layer__tx__quic_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__tx__quic']);
} else {
    $app_layer__tx__quic_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__quic']);
}
if (isset($vars['sinstance'])) {
    $app_layer__tx__rdp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__tx__rdp']);
} else {
    $app_layer__tx__rdp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__rdp']);
}
if (isset($vars['sinstance'])) {
    $app_layer__tx__rfb_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__tx__rfb']);
} else {
    $app_layer__tx__rfb_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__rfb']);
}
if (isset($vars['sinstance'])) {
    $app_layer__tx__sip_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__tx__sip']);
} else {
    $app_layer__tx__sip_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__sip']);
}
if (isset($vars['sinstance'])) {
    $app_layer__tx__smb_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__tx__smb']);
} else {
    $app_layer__tx__smb_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__smb']);
}
if (isset($vars['sinstance'])) {
    $app_layer__tx__smtp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__tx__smtp']);
} else {
    $app_layer__tx__smtp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__smtp']);
}
if (isset($vars['sinstance'])) {
    $app_layer__tx__snmp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__tx__snmp']);
} else {
    $app_layer__tx__snmp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__snmp']);
}
if (isset($vars['sinstance'])) {
    $app_layer__tx__ssh_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__tx__ssh']);
} else {
    $app_layer__tx__ssh_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__ssh']);
}
if (isset($vars['sinstance'])) {
    $app_layer__tx__telnet_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__tx__telnet']);
} else {
    $app_layer__tx__telnet_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__telnet']);
}
if (isset($vars['sinstance'])) {
    $app_layer__tx__tftp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__tx__tftp']);
} else {
    $app_layer__tx__tftp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__tftp']);
}
if (isset($vars['sinstance'])) {
    $app_layer__tx__tls_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__tx__tls']);
} else {
    $app_layer__tx__tls_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__tls']);
}

$rrd_list = [];
if (Rrd::checkRrdExists($app_layer__tx__bittorrent_dht_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__bittorrent_dht_rrd_filename,
        'descr' => 'BT DHT',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__tx__bittorrent_dht_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__tx__dcerpc_tcp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__dcerpc_tcp_rrd_filename,
        'descr' => 'DCERPC, TCP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__tx__dcerpc_tcp_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__tx__dcerpc_udp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__dcerpc_udp_rrd_filename,
        'descr' => 'DCERPC, UDP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__tx__dcerpc_udp_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__tx__dhcp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__dhcp_rrd_filename,
        'descr' => 'DHCP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__tx__dhcp_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__tx__dnp3_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__dnp3_rrd_filename,
        'descr' => 'DNP3',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__tx__dnp3_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__tx__dns_tcp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__dns_tcp_rrd_filename,
        'descr' => 'DNS, TCP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__tx__dns_tcp_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__tx__dns_udp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__dns_udp_rrd_filename,
        'descr' => 'DNS, UDP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__tx__dns_udp_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__tx__enip_tcp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__enip_tcp_rrd_filename,
        'descr' => 'ENIP, TCP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__tx__enip_tcp_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__tx__enip_udp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__enip_udp_rrd_filename,
        'descr' => 'ENIP, UDP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__tx__enip_udp_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__tx__failed_tcp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__failed_tcp_rrd_filename,
        'descr' => 'Failed TCP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__tx__failed_tcp_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__tx__failed_udp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__failed_udp_rrd_filename,
        'descr' => 'Failed UDP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__tx__failed_udp_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__tx__ftp_data_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__ftp_data_rrd_filename,
        'descr' => 'FTP-Data',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__tx__ftp_data_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__tx__ftp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__ftp_rrd_filename,
        'descr' => 'FTP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__tx__ftp_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__tx__http_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__http_rrd_filename,
        'descr' => 'HTTP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__tx__http_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__tx__http2_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__http2_rrd_filename,
        'descr' => 'HTTP2',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__tx__http2_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__tx__ike_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__ike_rrd_filename,
        'descr' => 'IKE',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__tx__ike_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__tx__imap_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__imap_rrd_filename,
        'descr' => 'IMAP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__tx__imap_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__tx__krb5_tcp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__krb5_tcp_rrd_filename,
        'descr' => 'KRB5, TCP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__tx__krb5_tcp_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__tx__krb5_udp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__krb5_udp_rrd_filename,
        'descr' => 'KRB5, UDP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__tx__krb5_udp_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__tx__modbus_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__modbus_rrd_filename,
        'descr' => 'Modbus',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__tx__modbus_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__tx__mqtt_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__mqtt_rrd_filename,
        'descr' => 'MQTT',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__tx__mqtt_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__tx__nfs_tcp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__nfs_tcp_rrd_filename,
        'descr' => 'NFS, TCP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__tx__nfs_tcp_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__tx__nfs_udp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__nfs_udp_rrd_filename,
        'descr' => 'NFS, UDP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__tx__nfs_udp_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__tx__ntp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__ntp_rrd_filename,
        'descr' => 'NTP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__tx__ntp_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__tx__pgsql_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__pgsql_rrd_filename,
        'descr' => 'PostgreSQL',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__tx__pgsql_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__tx__quic_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__quic_rrd_filename,
        'descr' => 'QUIC',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__tx__quic_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__tx__rdp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__rdp_rrd_filename,
        'descr' => 'RDP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__tx__rdp_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__tx__rfb_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__rfb_rrd_filename,
        'descr' => 'RFB',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__tx__rfb_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__tx__sip_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__sip_rrd_filename,
        'descr' => 'SIP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__tx__sip_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__tx__smb_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__smb_rrd_filename,
        'descr' => 'SMB',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__tx__smb_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__tx__smtp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__smtp_rrd_filename,
        'descr' => 'SMTP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__tx__smtp_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__tx__snmp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__snmp_rrd_filename,
        'descr' => 'SNMP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__tx__snmp_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__tx__ssh_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__ssh_rrd_filename,
        'descr' => 'SSH',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__tx__ssh_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__tx__telnet_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__telnet_rrd_filename,
        'descr' => 'Telnet',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__tx__telnet_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__tx__tftp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__tftp_rrd_filename,
        'descr' => 'TFTP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__tx__tftp_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__tx__tls_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__tx__tls_rrd_filename,
        'descr' => 'TLS',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__tx__tls_rrd_filename . '" not found');
}


require 'includes/html/graphs/generic_multi_line.inc.php';
