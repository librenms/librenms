<?php

$name = 'suricata';
$unit_text = 'gap errs/sec';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;

if (isset($vars['sinstance'])) {
    $app_layer__error__bittorrent_dht__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__bittorrent-dht__gap']);
    $app_layer__error__dcerpc_tcp__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__dcerpc_tcp__gap']);
    $app_layer__error__dcerpc_udp__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__dcerpc_udp__gap']);
    $app_layer__error__dhcp__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__dhcp__gap']);
    $app_layer__error__dnp3__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__dnp3__gap']);
    $app_layer__error__dns_tcp__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__dns_tcp__gap']);
    $app_layer__error__dns_udp__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__dns_udp__gap']);
    $app_layer__error__enip_tcp__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__enip_tcp__gap']);
    $app_layer__error__enip_udp__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__enip_udp__gap']);
    $app_layer__error__failed_tcp__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__failed_tcp__gap']);
    $app_layer__error__failed_udp__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__failed_udp__gap']);
    $app_layer__error__ftp_data__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__ftp-data__gap']);
    $app_layer__error__ftp__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__ftp__gap']);
    $app_layer__error__http__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__http__gap']);
    $app_layer__error__http2__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__http2__gap']);
    $app_layer__error__ike__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__ike__gap']);
    $app_layer__error__imap__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__imap__gap']);
    $app_layer__error__krb5_tcp__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__krb5_tcp__gap']);
    $app_layer__error__krb5_udp__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__krb5_udp__gap']);
    $app_layer__error__modbus__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__modbus__gap']);
    $app_layer__error__mqtt__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__mqtt__gap']);
    $app_layer__error__nfs_tcp__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__nfs_tcp__gap']);
    $app_layer__error__nfs_udp__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__nfs_udp__gap']);
    $app_layer__error__ntp__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__ntp__gap']);
    $app_layer__error__pgsql__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__pgsql__gap']);
    $app_layer__error__quic__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__quic__gap']);
    $app_layer__error__rdp__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__rdp__gap']);
    $app_layer__error__rfb__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__rfb__gap']);
    $app_layer__error__sip__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__sip__gap']);
    $app_layer__error__smb__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__smb__gap']);
    $app_layer__error__smtp__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__smtp__gap']);
    $app_layer__error__snmp__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__snmp__gap']);
    $app_layer__error__ssh__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__ssh__gap']);
    $app_layer__error__telnet__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__telnet__gap']);
    $app_layer__error__tftp__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__tftp__gap']);
    $app_layer__error__tls__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__tls__gap']);
} else {
    $app_layer__error__bittorrent_dht__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__bittorrent-dht__gap']);
    $app_layer__error__dcerpc_tcp__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__dcerpc_tcp__gap']);
    $app_layer__error__dcerpc_udp__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__dcerpc_udp__gap']);
    $app_layer__error__dhcp__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__dhcp__gap']);
    $app_layer__error__dnp3__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__dnp3__gap']);
    $app_layer__error__dns_tcp__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__dns_tcp__gap']);
    $app_layer__error__dns_udp__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__dns_udp__gap']);
    $app_layer__error__enip_tcp__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__enip_tcp__gap']);
    $app_layer__error__enip_udp__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__enip_udp__gap']);
    $app_layer__error__failed_tcp__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__failed_tcp__gap']);
    $app_layer__error__failed_udp__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__failed_udp__gap']);
    $app_layer__error__ftp_data__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__ftp-data__gap']);
    $app_layer__error__ftp__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__ftp__gap']);
    $app_layer__error__http__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__http__gap']);
    $app_layer__error__http2__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__http2__gap']);
    $app_layer__error__ike__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__ike__gap']);
    $app_layer__error__imap__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__imap__gap']);
    $app_layer__error__krb5_tcp__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__krb5_tcp__gap']);
    $app_layer__error__krb5_udp__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__krb5_udp__gap']);
    $app_layer__error__modbus__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__modbus__gap']);
    $app_layer__error__mqtt__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__mqtt__gap']);
    $app_layer__error__nfs_tcp__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__nfs_tcp__gap']);
    $app_layer__error__nfs_udp__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__nfs_udp__gap']);
    $app_layer__error__ntp__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__ntp__gap']);
    $app_layer__error__pgsql__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__pgsql__gap']);
    $app_layer__error__quic__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__quic__gap']);
    $app_layer__error__rdp__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__rdp__gap']);
    $app_layer__error__rfb__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__rfb__gap']);
    $app_layer__error__sip__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__sip__gap']);
    $app_layer__error__smb__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__smb__gap']);
    $app_layer__error__smtp__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__smtp__gap']);
    $app_layer__error__snmp__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__snmp__gap']);
    $app_layer__error__ssh__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__ssh__gap']);
    $app_layer__error__telnet__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__telnet__gap']);
    $app_layer__error__tftp__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__tftp__gap']);
    $app_layer__error__tls__gap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__tls__gap']);
}

$rrd_list = [];
if (Rrd::checkRrdExists($app_layer__error__bittorrent_dht__gap_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__bittorrent_dht__gap_rrd_filename,
        'descr' => 'BT DHT',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__bittorrent_dht__gap_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__dcerpc_tcp__gap_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__dcerpc_tcp__gap_rrd_filename,
        'descr' => 'DCERPC, TCP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__dcerpc_tcp__gap_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__dcerpc_udp__gap_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__dcerpc_udp__gap_rrd_filename,
        'descr' => 'DCERPC, UDP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__dcerpc_udp__gap_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__dhcp__gap_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__dhcp__gap_rrd_filename,
        'descr' => 'DHCP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__dhcp__gap_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__dnp3__gap_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__dnp3__gap_rrd_filename,
        'descr' => 'DNP3',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__dnp3__gap_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__dns_tcp__gap_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__dns_tcp__gap_rrd_filename,
        'descr' => 'DNS, TCP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__dns_tcp__gap_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__dns_udp__gap_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__dns_udp__gap_rrd_filename,
        'descr' => 'DNS, UDP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__dns_udp__gap_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__enip_tcp__gap_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__enip_tcp__gap_rrd_filename,
        'descr' => 'ENIP, TCP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__enip_tcp__gap_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__enip_udp__gap_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__enip_udp__gap_rrd_filename,
        'descr' => 'ENIP, UDP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__enip_udp__gap_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__failed_tcp__gap_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__failed_tcp__gap_rrd_filename,
        'descr' => 'Failed TCP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__failed_tcp__gap_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__failed_udp__gap_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__failed_udp__gap_rrd_filename,
        'descr' => 'Failed UDP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__failed_udp__gap_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__ftp_data__gap_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__ftp_data__gap_rrd_filename,
        'descr' => 'FTP-Data',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__ftp_data__gap_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__ftp__gap_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__ftp__gap_rrd_filename,
        'descr' => 'FTP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__ftp__gap_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__http__gap_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__http__gap_rrd_filename,
        'descr' => 'HTTP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__http__gap_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__http2__gap_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__http2__gap_rrd_filename,
        'descr' => 'HTTP2',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__http2__gap_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__ike__gap_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__ike__gap_rrd_filename,
        'descr' => 'IKE',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__ike__gap_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__imap__gap_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__imap__gap_rrd_filename,
        'descr' => 'IMAP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__imap__gap_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__krb5_tcp__gap_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__krb5_tcp__gap_rrd_filename,
        'descr' => 'KRB5, TCP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__krb5_tcp__gap_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__krb5_udp__gap_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__krb5_udp__gap_rrd_filename,
        'descr' => 'KRB5, UDP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__krb5_udp__gap_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__modbus__gap_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__modbus__gap_rrd_filename,
        'descr' => 'Modbus',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__modbus__gap_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__mqtt__gap_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__mqtt__gap_rrd_filename,
        'descr' => 'MQTT',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__mqtt__gap_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__nfs_tcp__gap_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__nfs_tcp__gap_rrd_filename,
        'descr' => 'NFS, TCP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__nfs_tcp__gap_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__nfs_udp__gap_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__nfs_udp__gap_rrd_filename,
        'descr' => 'NFS, UDP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__nfs_udp__gap_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__ntp__gap_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__ntp__gap_rrd_filename,
        'descr' => 'NTP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__ntp__gap_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__pgsql__gap_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__pgsql__gap_rrd_filename,
        'descr' => 'PostgreSQL',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__pgsql__gap_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__quic__gap_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__quic__gap_rrd_filename,
        'descr' => 'QUIC',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__quic__gap_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__rdp__gap_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__rdp__gap_rrd_filename,
        'descr' => 'RDP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__rdp__gap_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__rfb__gap_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__rfb__gap_rrd_filename,
        'descr' => 'RFB',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__rfb__gap_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__sip__gap_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__sip__gap_rrd_filename,
        'descr' => 'SIP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__sip__gap_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__smb__gap_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__smb__gap_rrd_filename,
        'descr' => 'SMB',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__smb__gap_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__smtp__gap_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__smtp__gap_rrd_filename,
        'descr' => 'SMTP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__smtp__gap_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__snmp__gap_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__snmp__gap_rrd_filename,
        'descr' => 'SNMP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__snmp__gap_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__ssh__gap_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__ssh__gap_rrd_filename,
        'descr' => 'SSH',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__ssh__gap_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__telnet__gap_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__telnet__gap_rrd_filename,
        'descr' => 'Telnet',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__telnet__gap_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__tftp__gap_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__tftp__gap_rrd_filename,
        'descr' => 'TFTP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__tftp__gap_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__tls__gap_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__tls__gap_rrd_filename,
        'descr' => 'TLS',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__tls__gap_rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
