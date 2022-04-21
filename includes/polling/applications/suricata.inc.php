<?php

use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\Exceptions\JsonAppParsingFailedException;
use LibreNMS\RRD\RrdDefinition;

$name = 'suricata';
$app_id = $app['app_id'];

try {
    $suricata = json_app_get($device, 'suricata-stats');
} catch (JsonAppException $e) {
    echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
    update_application($app, $e->getCode() . ':' . $e->getMessage(), []); // Set empty metrics and error message

    return;
}

// grab  the alert here as it is the global one
$metrics=[ 'alert'=$suricata['alert'] ];

$rrd_def = RrdDefinition::make()
    ->addDataset('alert', 'GAUGE', 0)
    ->addDataset{'af_dcerpc_tcp', 'COUNTER', 0)
    ->addDataset{'af_dcerpc_udp', 'COUNTER', 0)
    ->addDataset{'af_dhcp', 'COUNTER', 0)
    ->addDataset{'af_dns_tcp', 'COUNTER', 0)
    ->addDataset{'af_dns_udp', 'COUNTER', 0)
    ->addDataset{'af_failed_tcp', 'COUNTER', 0)
    ->addDataset{'af_failed_udp', 'COUNTER', 0)
    ->addDataset{'af_ftp', 'COUNTER', 0)
    ->addDataset{'af_ftp-data', 'COUNTER', 0)
    ->addDataset{'af_http', 'COUNTER', 0)
    ->addDataset{'af_ikev2', 'COUNTER', 0)
    ->addDataset{'af_imap', 'COUNTER', 0)
    ->addDataset{'af_krb5_tcp', 'COUNTER', 0)
    ->addDataset{'af_krb5_udp', 'COUNTER', 0)
    ->addDataset{'af_mqtt', 'COUNTER', 0)
    ->addDataset{'af_nfs_tcp', 'COUNTER', 0)
    ->addDataset{'af_nfs_udp', 'COUNTER', 0)
    ->addDataset{'af_ntp', 'COUNTER', 0)
    ->addDataset{'af_rdp', 'COUNTER', 0)
    ->addDataset{'af_rfb', 'COUNTER', 0)
    ->addDataset{'af_sip', 'COUNTER', 0)
    ->addDataset{'af_smb', 'COUNTER', 0)
    ->addDataset{'af_smtp', 'COUNTER', 0)
    ->addDataset{'af_snmp', 'COUNTER', 0)
    ->addDataset{'af_ssh', 'COUNTER', 0)
    ->addDataset{'af_tftp', 'COUNTER', 0)
    ->addDataset{'af_tls', 'COUNTER', 0)
    ->addDataset('at_dcerpc_tcp', 'COUNTER', 0)
    ->addDataset('at_dcerpc_udp', 'COUNTER', 0)
    ->addDataset('at_dhcp', 'COUNTER', 0)
    ->addDataset('at_dns_tcp', 'COUNTER', 0)
    ->addDataset('at_dns_udp', 'COUNTER', 0)
    ->addDataset('at_ftp', 'COUNTER', 0)
    ->addDataset('at_ftp-data', 'COUNTER', 0)
    ->addDataset('at_http', 'COUNTER', 0)
    ->addDataset('at_ikev2', 'COUNTER', 0)
    ->addDataset('at_imap', 'COUNTER', 0)
    ->addDataset('at_krb5_tcp', 'COUNTER', 0)
    ->addDataset('at_krb5_udp', 'COUNTER', 0)
    ->addDataset('at_mqtt', 'COUNTER', 0)
    ->addDataset('at_nfs_tcp', 'COUNTER', 0)
    ->addDataset('at_nfs_udp', 'COUNTER', 0)
    ->addDataset('at_ntp', 'COUNTER', 0)
    ->addDataset('at_rdp', 'COUNTER', 0)
    ->addDataset('at_rfb', 'COUNTER', 0)
    ->addDataset('at_sip', 'COUNTER', 0)
    ->addDataset('at_smb', 'COUNTER', 0)
    ->addDataset('at_smtp', 'COUNTER', 0)
    ->addDataset('at_snmp', 'COUNTER', 0)
    ->addDataset('at_ssh', 'COUNTER', 0)
    ->addDataset('at_tftp', 'COUNTER', 0)
    ->addDataset('at_tls', 'COUNTER', 0)
    ->addDataset('bytes', 'COUNTER', 0)
    ->addDataset('dec_avg_pkt_size', 'COUNTER', 0)
    ->addDataset('dec_invalid', 'COUNTER', 0)
    ->addDataset('dec_ipv4', 'COUNTER', 0)
    ->addDataset('dec_ipv6', 'COUNTER', 0)
    ->addDataset('dec_max_pkt_size', 'COUNTER', 0)
    ->addDataset('dec_packets', 'COUNTER', 0)
    ->addDataset('dec_tcp', 'COUNTER', 0)
    ->addDataset('dec_udp', 'COUNTER', 0)
    ->addDataset('drop_percent', 'GAUGE', 0)
    ->addDataset('dropped', 'COUNTER', 0)
    ->addDataset('error_percent', 'GAUGE', 0)
    ->addDataset('errors', 'COUNTER', 0)
    ->addDataset('f_icmpv4', 'COUNTER', 0)
    ->addDataset('f_icmpv6', 'COUNTER', 0)
    ->addDataset('f_memuse', 'COUNTER', 0)
    ->addDataset('f_tcp', 'COUNTER', 0)
    ->addDataset('f_udp', 'COUNTER', 0)
    ->addDataset('ftp_memuse', 'COUNTER', 0)
    ->addDataset('http_memuse', 'COUNTER', 0)
    ->addDataset('ifdrop_percent', 'GAUGE', 0)
    ->addDataset('ifdropped', 'COUNTER', 0)
    ->addDataset('packets', 'COUNTER', 0)
    ->addDataset('tcp_memuse', 'COUNTER', 0)
    ->addDataset('tcp_reass_memuse', 'COUNTER', 0)
    ->addDataset('uptime', 'COUNTER', 0);

// keys that need to by migrated from the instance to the
$instance_keys=[ 'alert', 'at_dcerpc_tcp', 'at_dcerpc_udp', 'at_dhcp', 'at_dns_tcp', 'at_dns_udp', 'at_ftp', 'at_ftp-data',
    'at_http', 'at_ikev2', 'at_imap', 'at_krb5_tcp', 'at_krb5_udp', 'at_mqtt', 'at_nfs_tcp', 'at_nfs_udp', 'at_ntp', 'at_rdp',
    'at_rfb', 'at_sip', 'at_smb', 'at_smtp', 'at_snmp', 'at_ssh', 'at_tftp', 'at_tls', 'bytes', 'dec_avg_pkt_size',
    'dec_invalid', 'dec_ipv4', 'dec_ipv6', 'dec_max_pkt_size', 'dec_packets', 'dec_tcp', 'dec_udp', 'drop_delta',
    'drop_percent', 'dropped', 'error_delta', 'error_percent', 'errors', 'f_icmpv4', 'f_icmpv6', 'f_memuse', 'f_tcp', 'f_udp',
    'ftp_memuse', 'http_memuse', 'ifdrop_delta', 'ifdrop_percent', 'ifdropped', 'packet_delta', 'packets', 'tcp_memuse',
    'tcp_reass_memuse', 'uptime' ];

// process each instance
$instance_list=[];
foreach ($suricata['data'] as $instance => $stats) {
    $rrd_name = ['app', $name, $app_id, $instance];
    $instance_list[]=$instance;

    $fields=[];
    foreach ($instance_keys as $metric_key) {
        $metrics[$instance . '_' . $metric_key]=>$stats[$intance_key];
        if ( ! preg_match('/_delta$/', $instance_key ) {
            $fields[$metric_key]=$stats[$intance_key];
        }
    }

    $tags = ['name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
    data_update($device, 'app', $tags, $fields);

}

//
// component processing for ZFS
//
$device_id = $device['device_id'];
$options = [
    'filter' => [
        'device_id' => ['=', $device_id],
        'type' => ['=', 'suricata'],
    ],
];

$component = new LibreNMS\Component();
$components = $component->getComponents($device_id, $options);

// if no instances, delete the components
if (empty($instance_list)) {
    if (isset($components[$device_id])) {
        foreach ($components[$device_id] as $component_id => $_unused) {
            $component->deleteComponent($component_id);
        }
    }
} else {
    if (isset($components[$device_id])) {
        $ourc = $components[$device_id];
    } else {
        $ourc = $component->createComponent($device_id, 'suricata');
    }

    // Make sure we don't readd it, just in a different order.
    sort($instance_list);

    $id = $component->getFirstComponentID($ourc);
    $ourc[$id]['label'] = 'ZFS';
    $ourc[$id]['instances'] = json_encode($instance_list);
    $ourc[$id]['alert'] = $suricata['alert'];
    $ourc[$id]['alertString'] = $suricata['alertString'];

    $component->setComponentPrefs($device_id, $ourc);
}

//
// all done so update the app metrics
//
update_application($app, 'OK', $metrics);
