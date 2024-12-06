<?php

$name = 'suricata';
$unit_text = 'flows/sec';
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
        'ds' => 'af_dcerpc_tcp',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'DCERPC UDP',
        'ds' => 'af_dcerpc_udp',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'DHCP',
        'ds' => 'af_dhcp',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'DNS TCP',
        'ds' => 'af_dns_tcp',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'DNS UDP',
        'ds' => 'af_dns_udp',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'Failed TCP',
        'ds' => 'af_failed_tcp',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'Failed UDP',
        'ds' => 'af_failed_udp',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'FTP',
        'ds' => 'af_ftp',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'FTP-DATA',
        'ds' => 'af_ftp_data',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'HTTP',
        'ds' => 'af_http',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'IKEv2',
        'ds' => 'af_ikev2',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'IMAP',
        'ds' => 'af_imap',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'Krb5 TCP',
        'ds' => 'af_krb5_tcp',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'Krb5 UDP',
        'ds' => 'af_krb5_udp',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'MQTT',
        'ds' => 'af_mqtt',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'NFS TCP',
        'ds' => 'af_nfs_tcp',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'NFS UDP',
        'ds' => 'af_nfs_udp',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'RDP',
        'ds' => 'af_rdp',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'RFB',
        'ds' => 'af_rfb',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'SIP',
        'ds' => 'af_sip',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'SMB',
        'ds' => 'af_smb',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'SMTP',
        'ds' => 'af_smtp',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'SNMP',
        'ds' => 'af_snmp',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'ssh',
        'ds' => 'af_ssh',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'TFTP',
        'ds' => 'af_tftp',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'TLS',
        'ds' => 'af_tls',
    ];
} else {
    d_echo('RRD "' . $rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
