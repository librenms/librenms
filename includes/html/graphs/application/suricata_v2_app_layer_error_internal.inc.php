<?php

$name = 'suricata';
$unit_text = 'int errs/sec';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;

if (isset($vars['sinstance'])) {
    $app_layer__error__bittorrent_dht__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__bittorrent-dht__internal']);
    $app_layer__error__dcerpc_tcp__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__dcerpc_tcp__internal']);
    $app_layer__error__dcerpc_udp__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__dcerpc_udp__internal']);
    $app_layer__error__dhcp__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__dhcp__internal']);
    $app_layer__error__dnp3__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__dnp3__internal']);
    $app_layer__error__dns_tcp__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__dns_tcp__internal']);
    $app_layer__error__dns_udp__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__dns_udp__internal']);
    $app_layer__error__enip_tcp__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__enip_tcp__internal']);
    $app_layer__error__enip_udp__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__enip_udp__internal']);
    $app_layer__error__failed_tcp__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__failed_tcp__internal']);
    $app_layer__error__failed_udp__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__failed_udp__internal']);
    $app_layer__error__ftp_data__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__ftp-data__internal']);
    $app_layer__error__ftp__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__ftp__internal']);
    $app_layer__error__http__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__http__internal']);
    $app_layer__error__http2__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__http2__internal']);
    $app_layer__error__ike__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__ike__internal']);
    $app_layer__error__imap__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__imap__internal']);
    $app_layer__error__krb5_tcp__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__krb5_tcp__internal']);
    $app_layer__error__krb5_udp__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__krb5_udp__internal']);
    $app_layer__error__modbus__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__modbus__internal']);
    $app_layer__error__mqtt__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__mqtt__internal']);
    $app_layer__error__nfs_tcp__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__nfs_tcp__internal']);
    $app_layer__error__nfs_udp__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__nfs_udp__internal']);
    $app_layer__error__ntp__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__ntp__internal']);
    $app_layer__error__pgsql__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__pgsql__internal']);
    $app_layer__error__quic__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__quic__internal']);
    $app_layer__error__rdp__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__rdp__internal']);
    $app_layer__error__rfb__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__rfb__internal']);
    $app_layer__error__sip__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__sip__internal']);
    $app_layer__error__smb__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__smb__internal']);
    $app_layer__error__smtp__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__smtp__internal']);
    $app_layer__error__snmp__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__snmp__internal']);
    $app_layer__error__ssh__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__ssh__internal']);
    $app_layer__error__telnet__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__telnet__internal']);
    $app_layer__error__tftp__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__tftp__internal']);
    $app_layer__error__tls__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___app_layer__error__tls__internal']);
} else {
    $app_layer__error__bittorrent_dht__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__bittorrent-dht__internal']);
    $app_layer__error__dcerpc_tcp__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__dcerpc_tcp__internal']);
    $app_layer__error__dcerpc_udp__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__dcerpc_udp__internal']);
    $app_layer__error__dhcp__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__dhcp__internal']);
    $app_layer__error__dnp3__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__dnp3__internal']);
    $app_layer__error__dns_tcp__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__dns_tcp__internal']);
    $app_layer__error__dns_udp__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__dns_udp__internal']);
    $app_layer__error__enip_tcp__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__enip_tcp__internal']);
    $app_layer__error__enip_udp__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__enip_udp__internal']);
    $app_layer__error__failed_tcp__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__failed_tcp__internal']);
    $app_layer__error__failed_udp__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__failed_udp__internal']);
    $app_layer__error__ftp_data__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__ftp-data__internal']);
    $app_layer__error__ftp__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__ftp__internal']);
    $app_layer__error__http__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__http__internal']);
    $app_layer__error__http2__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__http2__internal']);
    $app_layer__error__ike__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__ike__internal']);
    $app_layer__error__imap__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__imap__internal']);
    $app_layer__error__krb5_tcp__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__krb5_tcp__internal']);
    $app_layer__error__krb5_udp__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__krb5_udp__internal']);
    $app_layer__error__modbus__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__modbus__internal']);
    $app_layer__error__mqtt__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__mqtt__internal']);
    $app_layer__error__nfs_tcp__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__nfs_tcp__internal']);
    $app_layer__error__nfs_udp__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__nfs_udp__internal']);
    $app_layer__error__ntp__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__ntp__internal']);
    $app_layer__error__pgsql__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__pgsql__internal']);
    $app_layer__error__quic__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__quic__internal']);
    $app_layer__error__rdp__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__rdp__internal']);
    $app_layer__error__rfb__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__rfb__internal']);
    $app_layer__error__sip__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__sip__internal']);
    $app_layer__error__smb__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__smb__internal']);
    $app_layer__error__smtp__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__smtp__internal']);
    $app_layer__error__snmp__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__snmp__internal']);
    $app_layer__error__ssh__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__ssh__internal']);
    $app_layer__error__telnet__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__telnet__internal']);
    $app_layer__error__tls__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__tls__internal']);
    $app_layer__error__tftp__internal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__tftp__internal']);
}

$rrd_list = [];
if (Rrd::checkRrdExists($app_layer__error__bittorrent_dht__internal_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__bittorrent_dht__internal_rrd_filename,
        'descr' => 'BT DHT',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__bittorrent_dht__internal_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__dcerpc_tcp__internal_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__dcerpc_tcp__internal_rrd_filename,
        'descr' => 'DCERPC, TCP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__dcerpc_tcp__internal_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__dcerpc_udp__internal_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__dcerpc_udp__internal_rrd_filename,
        'descr' => 'DCERPC, UDP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__dcerpc_udp__internal_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__dhcp__internal_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__dhcp__internal_rrd_filename,
        'descr' => 'DHCP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__dhcp__internal_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__dnp3__internal_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__dnp3__internal_rrd_filename,
        'descr' => 'DNP3',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__dnp3__internal_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__dns_tcp__internal_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__dns_tcp__internal_rrd_filename,
        'descr' => 'DNS, TCP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__dns_tcp__internal_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__dns_udp__internal_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__dns_udp__internal_rrd_filename,
        'descr' => 'DNS, UDP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__dns_udp__internal_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__enip_tcp__internal_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__enip_tcp__internal_rrd_filename,
        'descr' => 'ENIP, TCP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__enip_tcp__internal_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__enip_udp__internal_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__enip_udp__internal_rrd_filename,
        'descr' => 'ENIP, UDP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__enip_udp__internal_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__failed_tcp__internal_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__failed_tcp__internal_rrd_filename,
        'descr' => 'Failed TCP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__failed_tcp__internal_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__failed_udp__internal_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__failed_udp__internal_rrd_filename,
        'descr' => 'Failed UDP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__failed_udp__internal_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__ftp_data__internal_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__ftp_data__internal_rrd_filename,
        'descr' => 'FTP-Data',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__ftp_data__internal_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__ftp__internal_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__ftp__internal_rrd_filename,
        'descr' => 'FTP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__ftp__internal_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__http__internal_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__http__internal_rrd_filename,
        'descr' => 'HTTP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__http__internal_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__http2__internal_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__http2__internal_rrd_filename,
        'descr' => 'HTTP2',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__http2__internal_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__ike__internal_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__ike__internal_rrd_filename,
        'descr' => 'IKE',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__ike__internal_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__imap__internal_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__imap__internal_rrd_filename,
        'descr' => 'IMAP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__imap__internal_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__krb5_tcp__internal_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__krb5_tcp__internal_rrd_filename,
        'descr' => 'KRB5, TCP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__krb5_tcp__internal_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__krb5_udp__internal_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__krb5_udp__internal_rrd_filename,
        'descr' => 'KRB5, UDP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__krb5_udp__internal_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__modbus__internal_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__modbus__internal_rrd_filename,
        'descr' => 'Modbus',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__modbus__internal_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__mqtt__internal_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__mqtt__internal_rrd_filename,
        'descr' => 'MQTT',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__mqtt__internal_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__nfs_tcp__internal_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__nfs_tcp__internal_rrd_filename,
        'descr' => 'NFS, TCP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__nfs_tcp__internal_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__nfs_udp__internal_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__nfs_udp__internal_rrd_filename,
        'descr' => 'NFS, UDP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__nfs_udp__internal_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__ntp__internal_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__ntp__internal_rrd_filename,
        'descr' => 'NTP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__ntp__internal_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__pgsql__internal_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__pgsql__internal_rrd_filename,
        'descr' => 'PostgreSQL',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__pgsql__internal_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__quic__internal_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__quic__internal_rrd_filename,
        'descr' => 'QUIC',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__quic__internal_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__rdp__internal_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__rdp__internal_rrd_filename,
        'descr' => 'RDP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__rdp__internal_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__rfb__internal_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__rfb__internal_rrd_filename,
        'descr' => 'RFB',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__rfb__internal_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__sip__internal_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__sip__internal_rrd_filename,
        'descr' => 'SIP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__sip__internal_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__smb__internal_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__smb__internal_rrd_filename,
        'descr' => 'SMB',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__smb__internal_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__smtp__internal_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__smtp__internal_rrd_filename,
        'descr' => 'SMTP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__smtp__internal_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__snmp__internal_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__snmp__internal_rrd_filename,
        'descr' => 'SNMP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__snmp__internal_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__ssh__internal_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__ssh__internal_rrd_filename,
        'descr' => 'SSH',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__ssh__internal_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__telnet__internal_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__telnet__internal_rrd_filename,
        'descr' => 'Telnet',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__telnet__internal_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__tftp__internal_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__tftp__internal_rrd_filename,
        'descr' => 'TFTP',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__tftp__internal_rrd_filename . '" not found');
}
if (Rrd::checkRrdExists($app_layer__error__tls__internal_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $app_layer__error__tls__internal_rrd_filename,
        'descr' => 'TLS',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $app_layer__error__tls__internal_rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
