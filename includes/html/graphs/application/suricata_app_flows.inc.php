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

$rrd_list = [
    [
        'filename' => $rrd_filename,
        'descr' => 'DCERPC TCP',
        'ds' => 'af_dcerpc_tcp',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'DCERPC UDP',
        'ds' => 'af_dcerpc_udp',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'DHCP',
        'ds' => 'af_dhcp',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'DNS TCP',
        'ds' => 'af_dns_tcp',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'DNS UDP',
        'ds' => 'af_dns_udp',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'Failed TCP',
        'ds' => 'af_failed_tcp',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'Failed UDP',
        'ds' => 'af_failed_udp',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'FTP',
        'ds' => 'af_ftp',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'FTP-DATA',
        'ds' => 'af_ftp_data',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'HTTP',
        'ds' => 'af_http',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'IKEv2',
        'ds' => 'af_ikev2',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'IMAP',
        'ds' => 'af_imap',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'Krb5 TCP',
        'ds' => 'af_krb5_tcp',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'Krb5 UDP',
        'ds' => 'af_krb5_udp',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'MQTT',
        'ds' => 'af_mqtt',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'NFS TCP',
        'ds' => 'af_nfs_tcp',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'NFS UDP',
        'ds' => 'af_nfs_udp',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'RDP',
        'ds' => 'af_rdp',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'RFB',
        'ds' => 'af_rfb',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'SIP',
        'ds' => 'af_sip',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'SMB',
        'ds' => 'af_smb',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'SMTP',
        'ds' => 'af_smtp',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'SNMP',
        'ds' => 'af_snmp',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'ssh',
        'ds' => 'af_ssh',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'TFTP',
        'ds' => 'af_tftp',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'TLS',
        'ds' => 'af_tls',
    ],
];

require 'includes/html/graphs/generic_multi_line.inc.php';
