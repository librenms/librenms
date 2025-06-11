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

$rrd_list = [
[
        'filename' => $rrd_filename,
        'descr' => 'DCERPC TCP',
        'ds' => 'at_dcerpc_tcp',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'DCERPC UDP',
        'ds' => 'at_dcerpc_udp',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'DHCP',
        'ds' => 'at_dhcp',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'DNS TCP',
        'ds' => 'at_dns_tcp',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'DNS UDP',
        'ds' => 'at_dns_udp',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'FTP',
        'ds' => 'at_ftp',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'FTP-DATA',
        'ds' => 'at_ftp_data',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'HTTP',
        'ds' => 'at_http',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'IKEv2',
        'ds' => 'at_ikev2',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'IMAP',
        'ds' => 'at_imap',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'Krb5 TCP',
        'ds' => 'at_krb5_tcp',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'Krb5 UDP',
        'ds' => 'at_krb5_udp',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'MQTT',
        'ds' => 'at_mqtt',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'NFS TCP',
        'ds' => 'at_nfs_tcp',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'NFS UDP',
        'ds' => 'at_nfs_udp',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'RDP',
        'ds' => 'at_rdp',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'RFB',
        'ds' => 'at_rfb',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'SIP',
        'ds' => 'at_sip',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'SMB',
        'ds' => 'at_smb',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'SMTP',
        'ds' => 'at_smtp',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'SNMP',
        'ds' => 'at_snmp',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'ssh',
        'ds' => 'at_ssh',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'TFTP',
        'ds' => 'at_tftp',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'TLS',
        'ds' => 'at_tls',
    ],
];


require 'includes/html/graphs/generic_multi_line.inc.php';
