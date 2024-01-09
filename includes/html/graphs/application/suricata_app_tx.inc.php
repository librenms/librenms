<?php

$name = 'suricata';
$unit_text = 'tx/sec';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 0;
$transparency = 15;

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $vars['sinstance']]);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id]);
}

$rrd_list = [];
if (Rrd::checkRrdExists($rrd_filename)) {
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
} else {
    d_echo('RRD "' . $rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
