<?php

$name = 'suricata';
$unit_text = 'alloc errs/sec';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;

if (isset($vars['sinstance'])) {
    $app_layer__error__bittorrent_dht__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__bittorrent-dht__alloc']);
    $app_layer__error__dcerpc_tcp__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__dcerpc_tcp__alloc']);
    $app_layer__error__dcerpc_udp__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__dcerpc_udp__alloc']);
    $app_layer__error__dhcp__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__dhcp__alloc']);
    $app_layer__error__dnp3__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__dnp3__alloc']);
    $app_layer__error__dns_tcp__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__dns_tcp__alloc']);
    $app_layer__error__dns_udp__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__dns_udp__alloc']);
    $app_layer__error__enip_tcp__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__enip_tcp__alloc']);
    $app_layer__error__enip_udp__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__enip_udp__alloc']);
    $app_layer__error__failed_tcp__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__failed_tcp__alloc']);
    $app_layer__error__failed_udp__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__failed_udp__alloc']);
    $app_layer__error__ftp_data__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__ftp-data__alloc']);
    $app_layer__error__ftp__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__ftp__alloc']);
    $app_layer__error__http__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__http__alloc']);
    $app_layer__error__http2__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__http2__alloc']);
    $app_layer__error__ike__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__ike__alloc']);
    $app_layer__error__imap__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__imap__alloc']);
    $app_layer__error__krb5_tcp__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__krb5_tcp__alloc']);
    $app_layer__error__krb5_udp__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__krb5_udp__alloc']);
    $app_layer__error__modbus__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__modbus__alloc']);
    $app_layer__error__mqtt__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__mqtt__alloc']);
    $app_layer__error__nfs_tcp__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__nfs_tcp__alloc']);
    $app_layer__error__nfs_udp__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__nfs_udp__alloc']);
    $app_layer__error__ntp__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__ntp__alloc']);
    $app_layer__error__pgsql__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__pgsql__alloc']);
    $app_layer__error__quic__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__quic__alloc']);
    $app_layer__error__rdp__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__rdp__alloc']);
    $app_layer__error__rfb__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__rfb__alloc']);
    $app_layer__error__sip__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__sip__alloc']);
    $app_layer__error__smb__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__smb__alloc']);
    $app_layer__error__smtp__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__smtp__alloc']);
    $app_layer__error__snmp__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__snmp__alloc']);
    $app_layer__error__ssh__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__ssh__alloc']);
    $app_layer__error__telnet__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__telnet__alloc']);
    $app_layer__error__tftp__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__tftp__alloc']);
    $app_layer__error__tls__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__tls__alloc']);
} else {
    $app_layer__error__bittorrent_dht__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__bittorrent-dht__alloc']);
    $app_layer__error__dcerpc_tcp__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__dcerpc_tcp__alloc']);
    $app_layer__error__dcerpc_udp__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__dcerpc_udp__alloc']);
    $app_layer__error__dhcp__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__dhcp__alloc']);
    $app_layer__error__dnp3__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__dnp3__alloc']);
    $app_layer__error__dns_tcp__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__dns_tcp__alloc']);
    $app_layer__error__dns_udp__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__dns_udp__alloc']);
    $app_layer__error__enip_tcp__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__enip_tcp__alloc']);
    $app_layer__error__enip_udp__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__enip_udp__alloc']);
    $app_layer__error__failed_tcp__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__failed_tcp__alloc']);
    $app_layer__error__failed_udp__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__failed_udp__alloc']);
    $app_layer__error__ftp_data__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__ftp-data__alloc']);
    $app_layer__error__ftp__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__ftp__alloc']);
    $app_layer__error__http__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__http__alloc']);
    $app_layer__error__http2__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__http2__alloc']);
    $app_layer__error__ike__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__ike__alloc']);
    $app_layer__error__imap__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__imap__alloc']);
    $app_layer__error__krb5_tcp__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__krb5_tcp__alloc']);
    $app_layer__error__krb5_udp__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__krb5_udp__alloc']);
    $app_layer__error__modbus__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__modbus__alloc']);
    $app_layer__error__mqtt__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__mqtt__alloc']);
    $app_layer__error__nfs_tcp__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__nfs_tcp__alloc']);
    $app_layer__error__nfs_udp__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__nfs_udp__alloc']);
    $app_layer__error__ntp__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__ntp__alloc']);
    $app_layer__error__pgsql__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__pgsql__alloc']);
    $app_layer__error__quic__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__quic__alloc']);
    $app_layer__error__rdp__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__rdp__alloc']);
    $app_layer__error__rfb__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__rfb__alloc']);
    $app_layer__error__sip__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__sip__alloc']);
    $app_layer__error__smb__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__smb__alloc']);
    $app_layer__error__smtp__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__smtp__alloc']);
    $app_layer__error__snmp__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__snmp__alloc']);
    $app_layer__error__ssh__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__ssh__alloc']);
    $app_layer__error__telnet__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__telnet__alloc']);
    $app_layer__error__tftp__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__tftp__alloc']);
    $app_layer__error__tls__alloc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__tls__alloc']);
}

$rrd_list = [];
if (Rrd::checkRrdExists($app_layer__error__bittorrent_dht__alloc_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__bittorrent_dht__alloc_rrd_filename,
        'descr' => 'BT DHT',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__bittorrent_dht__alloc_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__dcerpc_tcp__alloc_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__dcerpc_tcp__alloc_rrd_filename,
        'descr' => 'DCERPC, TCP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__dcerpc_tcp__alloc_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__dcerpc_udp__alloc_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__dcerpc_udp__alloc_rrd_filename,
        'descr' => 'DCERPC, UDP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__dcerpc_udp__alloc_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__dhcp__alloc_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__dhcp__alloc_rrd_filename,
        'descr' => 'DHCP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__dhcp__alloc_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__dnp3__alloc_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__dnp3__alloc_rrd_filename,
        'descr' => 'DNP3',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__dnp3__alloc_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__dns_tcp__alloc_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__dns_tcp__alloc_rrd_filename,
        'descr' => 'DNS, TCP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__dns_tcp__alloc_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__dns_udp__alloc_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__dns_udp__alloc_rrd_filename,
        'descr' => 'DNS, UDP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__dns_udp__alloc_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__enip_tcp__alloc_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__enip_tcp__alloc_rrd_filename,
        'descr' => 'ENIP, TCP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__enip_tcp__alloc_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__enip_udp__alloc_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__enip_udp__alloc_rrd_filename,
        'descr' => 'ENIP, UDP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__enip_udp__alloc_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__failed_tcp__alloc_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__failed_tcp__alloc_rrd_filename,
        'descr' => 'Failed TCP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__failed_tcp__alloc_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__failed_udp__alloc_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__failed_udp__alloc_rrd_filename,
        'descr' => 'Failed UDP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__failed_udp__alloc_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__ftp_data__alloc_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__ftp_data__alloc_rrd_filename,
        'descr' => 'FTP-Data',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__ftp_data__alloc_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__ftp__alloc_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__ftp__alloc_rrd_filename,
        'descr' => 'FTP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__ftp__alloc_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__http__alloc_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__http__alloc_rrd_filename,
        'descr' => 'HTTP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__http__alloc_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__http2__alloc_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__http2__alloc_rrd_filename,
        'descr' => 'HTTP2',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__http2__alloc_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__ike__alloc_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__ike__alloc_rrd_filename,
        'descr' => 'IKE',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__ike__alloc_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__imap__alloc_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__imap__alloc_rrd_filename,
        'descr' => 'IMAP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__imap__alloc_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__krb5_tcp__alloc_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__krb5_tcp__alloc_rrd_filename,
        'descr' => 'KRB5, TCP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__krb5_tcp__alloc_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__krb5_udp__alloc_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__krb5_udp__alloc_rrd_filename,
        'descr' => 'KRB5, UDP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__krb5_udp__alloc_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__modbus__alloc_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__modbus__alloc_rrd_filename,
        'descr' => 'Modbus',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__modbus__alloc_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__mqtt__alloc_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__mqtt__alloc_rrd_filename,
        'descr' => 'MQTT',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__mqtt__alloc_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__nfs_tcp__alloc_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__nfs_tcp__alloc_rrd_filename,
        'descr' => 'NFS, TCP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__nfs_tcp__alloc_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__nfs_udp__alloc_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__nfs_udp__alloc_rrd_filename,
        'descr' => 'NFS, UDP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__nfs_udp__alloc_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__ntp__alloc_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__ntp__alloc_rrd_filename,
        'descr' => 'NTP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__ntp__alloc_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__pgsql__alloc_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__pgsql__alloc_rrd_filename,
        'descr' => 'PostgreSQL',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__pgsql__alloc_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__quic__alloc_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__quic__alloc_rrd_filename,
        'descr' => 'QUIC',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__quic__alloc_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__rdp__alloc_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__rdp__alloc_rrd_filename,
        'descr' => 'RDP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__rdp__alloc_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__rfb__alloc_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__rfb__alloc_rrd_filename,
        'descr' => 'RFB',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__rfb__alloc_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__sip__alloc_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__sip__alloc_rrd_filename,
        'descr' => 'SIP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__sip__alloc_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__smb__alloc_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__smb__alloc_rrd_filename,
        'descr' => 'SMB',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__smb__alloc_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__smtp__alloc_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__smtp__alloc_rrd_filename,
        'descr' => 'SMTP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__smtp__alloc_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__snmp__alloc_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__snmp__alloc_rrd_filename,
        'descr' => 'SNMP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__snmp__alloc_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__ssh__alloc_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__ssh__alloc_rrd_filename,
        'descr' => 'SSH',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__ssh__alloc_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__telnet__alloc_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__telnet__alloc_rrd_filename,
        'descr' => 'Telnet',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__telnet__alloc_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__tftp__alloc_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__tftp__alloc_rrd_filename,
        'descr' => 'TFTP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__tftp__alloc_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__tls__alloc_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__tls__alloc_rrd_filename,
        'descr' => 'TLS',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__tls__alloc_rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
