<?php

use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\RRD\RrdDefinition;

$name = 'suricata';
try {
    $suricata = json_app_get($device, 'suricata-stats');
} catch (JsonAppException $e) {
    echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
    update_application($app, $e->getCode() . ':' . $e->getMessage(), []); // Set empty metrics and error message

    return;
}

// grab  the alert here as it is the global one
$metrics = ['alert' => $suricata['alert']];

$rrd_def = RrdDefinition::make()
    ->addDataset('af_dcerpc_tcp', 'DERIVE', 0)
    ->addDataset('af_dcerpc_udp', 'DERIVE', 0)
    ->addDataset('af_dhcp', 'DERIVE', 0)
    ->addDataset('af_dns_tcp', 'DERIVE', 0)
    ->addDataset('af_dns_udp', 'DERIVE', 0)
    ->addDataset('af_failed_tcp', 'DERIVE', 0)
    ->addDataset('af_failed_udp', 'DERIVE', 0)
    ->addDataset('af_ftp', 'DERIVE', 0)
    ->addDataset('af_ftp_data', 'DERIVE', 0)
    ->addDataset('af_http', 'DERIVE', 0)
    ->addDataset('af_ikev2', 'DERIVE', 0)
    ->addDataset('af_imap', 'DERIVE', 0)
    ->addDataset('af_krb5_tcp', 'DERIVE', 0)
    ->addDataset('af_krb5_udp', 'DERIVE', 0)
    ->addDataset('af_mqtt', 'DERIVE', 0)
    ->addDataset('af_nfs_tcp', 'DERIVE', 0)
    ->addDataset('af_nfs_udp', 'DERIVE', 0)
    ->addDataset('af_ntp', 'DERIVE', 0)
    ->addDataset('af_rdp', 'DERIVE', 0)
    ->addDataset('af_rfb', 'DERIVE', 0)
    ->addDataset('af_sip', 'DERIVE', 0)
    ->addDataset('af_smb', 'DERIVE', 0)
    ->addDataset('af_smtp', 'DERIVE', 0)
    ->addDataset('af_snmp', 'DERIVE', 0)
    ->addDataset('af_ssh', 'DERIVE', 0)
    ->addDataset('af_tftp', 'DERIVE', 0)
    ->addDataset('af_tls', 'DERIVE', 0)
    ->addDataset('alert', 'GAUGE', 0)
    ->addDataset('at_dcerpc_tcp', 'DERIVE', 0)
    ->addDataset('at_dcerpc_udp', 'DERIVE', 0)
    ->addDataset('at_dhcp', 'DERIVE', 0)
    ->addDataset('at_dns_tcp', 'DERIVE', 0)
    ->addDataset('at_dns_udp', 'DERIVE', 0)
    ->addDataset('at_ftp', 'DERIVE', 0)
    ->addDataset('at_ftp_data', 'DERIVE', 0)
    ->addDataset('at_http', 'DERIVE', 0)
    ->addDataset('at_ikev2', 'DERIVE', 0)
    ->addDataset('at_imap', 'DERIVE', 0)
    ->addDataset('at_krb5_tcp', 'DERIVE', 0)
    ->addDataset('at_krb5_udp', 'DERIVE', 0)
    ->addDataset('at_mqtt', 'DERIVE', 0)
    ->addDataset('at_nfs_tcp', 'DERIVE', 0)
    ->addDataset('at_nfs_udp', 'DERIVE', 0)
    ->addDataset('at_ntp', 'DERIVE', 0)
    ->addDataset('at_rdp', 'DERIVE', 0)
    ->addDataset('at_rfb', 'DERIVE', 0)
    ->addDataset('at_sip', 'DERIVE', 0)
    ->addDataset('at_smb', 'DERIVE', 0)
    ->addDataset('at_smtp', 'DERIVE', 0)
    ->addDataset('at_snmp', 'DERIVE', 0)
    ->addDataset('at_ssh', 'DERIVE', 0)
    ->addDataset('at_tftp', 'DERIVE', 0)
    ->addDataset('at_tls', 'DERIVE', 0)
    ->addDataset('bytes', 'DERIVE', 0)
    ->addDataset('dec_avg_pkt_size', 'DERIVE', 0)
    ->addDataset('dec_chdlc', 'DERIVE', 0)
    ->addDataset('dec_ethernet', 'DERIVE', 0)
    ->addDataset('dec_geneve', 'DERIVE', 0)
    ->addDataset('dec_ieee8021ah', 'DERIVE', 0)
    ->addDataset('dec_invalid', 'DERIVE', 0)
    ->addDataset('dec_ipv4', 'DERIVE', 0)
    ->addDataset('dec_ipv4_in_ipv6', 'DERIVE', 0)
    ->addDataset('dec_ipv6', 'DERIVE', 0)
    ->addDataset('dec_max_pkt_size', 'DERIVE', 0)
    ->addDataset('dec_mpls', 'DERIVE', 0)
    ->addDataset('dec_mx_mac_addrs_d', 'DERIVE', 0)
    ->addDataset('dec_mx_mac_addrs_s', 'DERIVE', 0)
    ->addDataset('dec_packets', 'DERIVE', 0)
    ->addDataset('dec_ppp', 'DERIVE', 0)
    ->addDataset('dec_pppoe', 'DERIVE', 0)
    ->addDataset('dec_raw', 'DERIVE', 0)
    ->addDataset('dec_sctp', 'DERIVE', 0)
    ->addDataset('dec_sll', 'DERIVE', 0)
    ->addDataset('dec_tcp', 'DERIVE', 0)
    ->addDataset('dec_teredo', 'DERIVE', 0)
    ->addDataset('dec_too_many_layer', 'DERIVE', 0)
    ->addDataset('dec_udp', 'DERIVE', 0)
    ->addDataset('dec_vlan', 'DERIVE', 0)
    ->addDataset('dec_vlan_qinq', 'DERIVE', 0)
    ->addDataset('dec_vntag', 'DERIVE', 0)
    ->addDataset('dec_vxlan', 'DERIVE', 0)
    ->addDataset('drop_percent', 'GAUGE', 0)
    ->addDataset('dropped', 'DERIVE', 0)
    ->addDataset('error_percent', 'GAUGE', 0)
    ->addDataset('errors', 'DERIVE', 0)
    ->addDataset('f_icmpv4', 'DERIVE', 0)
    ->addDataset('f_icmpv6', 'DERIVE', 0)
    ->addDataset('f_memuse', 'GAUGE', 0)
    ->addDataset('f_tcp', 'DERIVE', 0)
    ->addDataset('f_udp', 'DERIVE', 0)
    ->addDataset('ftp_memuse', 'GAUGE', 0)
    ->addDataset('http_memuse', 'GAUGE', 0)
    ->addDataset('ifdrop_percent', 'GAUGE', 0)
    ->addDataset('ifdropped', 'DERIVE', 0)
    ->addDataset('packets', 'DERIVE', 0)
    ->addDataset('tcp_memuse', 'GAUGE', 0)
    ->addDataset('tcp_reass_memuse', 'GAUGE', 0)
    ->addDataset('uptime', 'GAUGE', 0);

// keys that need to by migrated from the instance to the
$instance_keys = [
    'af_dcerpc_tcp', 'af_dcerpc_udp', 'af_dhcp', 'af_dns_tcp', 'af_dns_udp', 'af_failed_tcp', 'af_failed_udp', 'af_ftp',
    'af_ftp_data', 'af_http', 'af_ikev2', 'af_imap', 'af_krb5_tcp', 'af_krb5_udp', 'af_mqtt', 'af_nfs_tcp', 'af_nfs_udp',
    'af_ntp', 'af_rdp', 'af_rfb', 'af_sip', 'af_smb', 'af_smtp', 'af_snmp', 'af_ssh', 'af_tftp', 'af_tls', 'alert',
    'at_dcerpc_tcp', 'at_dcerpc_udp', 'at_dhcp', 'at_dns_tcp', 'at_dns_udp', 'at_ftp', 'at_ftp_data', 'at_http', 'at_ikev2',
    'at_imap', 'at_krb5_tcp', 'at_krb5_udp', 'at_mqtt', 'at_nfs_tcp', 'at_nfs_udp', 'at_ntp', 'at_rdp', 'at_rfb', 'at_sip',
    'at_smb', 'at_smtp', 'at_snmp', 'at_ssh', 'at_tftp', 'at_tls', 'bytes', 'dec_avg_pkt_size', 'dec_chdlc', 'dec_ethernet',
    'dec_geneve', 'dec_ieee8021ah', 'dec_invalid', 'dec_ipv4', 'dec_ipv4_in_ipv6', 'dec_ipv6', 'dec_max_pkt_size', 'dec_mpls',
    'dec_mx_mac_addrs_d', 'dec_mx_mac_addrs_s', 'dec_packets', 'dec_ppp', 'dec_pppoe', 'dec_raw', 'dec_sctp', 'dec_sll',
    'dec_tcp', 'dec_teredo', 'dec_too_many_layer', 'dec_udp', 'dec_vlan', 'dec_vlan_qinq', 'dec_vntag', 'dec_vxlan',
    'drop_delta', 'drop_percent', 'dropped', 'error_delta', 'error_percent', 'errors', 'f_icmpv4', 'f_icmpv6', 'f_memuse',
    'f_tcp', 'f_udp', 'ftp_memuse', 'http_memuse', 'ifdrop_delta', 'ifdrop_percent', 'ifdropped', 'packet_delta', 'packets',
    'tcp_memuse', 'tcp_reass_memuse', 'uptime',
];

// keys to add to the RRD field
$field_keys = [
    'af_dcerpc_tcp', 'af_dcerpc_udp', 'af_dhcp', 'af_dns_tcp', 'af_dns_udp', 'af_failed_tcp', 'af_failed_udp', 'af_ftp',
    'af_ftp_data', 'af_http', 'af_ikev2', 'af_imap', 'af_krb5_tcp', 'af_krb5_udp', 'af_mqtt', 'af_nfs_tcp', 'af_nfs_udp',
    'af_ntp', 'af_rdp', 'af_rfb', 'af_sip', 'af_smb', 'af_smtp', 'af_snmp', 'af_ssh', 'af_tftp', 'af_tls', 'alert',
    'at_dcerpc_tcp', 'at_dcerpc_udp', 'at_dhcp', 'at_dns_tcp', 'at_dns_udp', 'at_ftp', 'at_ftp_data', 'at_http', 'at_ikev2',
    'at_imap', 'at_krb5_tcp', 'at_krb5_udp', 'at_mqtt', 'at_nfs_tcp', 'at_nfs_udp', 'at_ntp', 'at_rdp', 'at_rfb', 'at_sip',
    'at_smb', 'at_smtp', 'at_snmp', 'at_ssh', 'at_tftp', 'at_tls', 'bytes', 'dec_avg_pkt_size', 'dec_chdlc', 'dec_ethernet',
    'dec_geneve', 'dec_ieee8021ah', 'dec_invalid', 'dec_ipv4', 'dec_ipv4_in_ipv6', 'dec_ipv6', 'dec_max_pkt_size', 'dec_mpls',
    'dec_mx_mac_addrs_d', 'dec_mx_mac_addrs_s', 'dec_packets', 'dec_ppp', 'dec_pppoe', 'dec_raw', 'dec_sctp', 'dec_sll',
    'dec_tcp', 'dec_teredo', 'dec_too_many_layer', 'dec_udp', 'dec_vlan', 'dec_vlan_qinq', 'dec_vntag', 'dec_vxlan',
    'drop_percent', 'dropped', 'error_percent', 'errors', 'f_icmpv4', 'f_icmpv6', 'f_memuse',
    'f_tcp', 'f_udp', 'ftp_memuse', 'http_memuse', 'ifdrop_percent', 'ifdropped', 'packets',
    'tcp_memuse', 'tcp_reass_memuse', 'uptime',
];

// process each instance
$instances = [];
foreach ($suricata['data'] as $instance => $stats) {
    if ($instance == '.total') {
        $rrd_name = ['app', $name, $app->app_id];
    } else {
        $rrd_name = ['app', $name, $app->app_id, $instance];
        $instances[] = $instance;
    }

    foreach ($instance_keys as $metric_key) {
        $metrics[$instance . '_' . $metric_key] = $stats[$metric_key];
    }

    $fields = [];
    foreach ($field_keys as $field_key) {
        $fields[$field_key] = $stats[$field_key];
    }

    $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
    data_update($device, 'app', $tags, $fields);
}

// check for added or removed instances
$old_instances = $app->data['instances'] ?? [];
$added_instances = array_diff($instances, $old_instances);
$removed_instances = array_diff($old_instances, $instances);

// if we have any source instances, save and log
if (count($added_instances) > 0 || count($removed_instances) > 0) {
    $app->data = ['instances' => $instances];
    $log_message = 'Suricata Instance Change:';
    $log_message .= count($added_instances) > 0 ? ' Added ' . implode(',', $added_instances) : '';
    $log_message .= count($removed_instances) > 0 ? ' Removed ' . implode(',', $added_instances) : '';
    log_event($log_message, $device, 'application');
}

//
// all done so update the app metrics
//
update_application($app, 'OK', $metrics);
