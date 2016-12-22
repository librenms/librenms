<?php

$serial   = trim(snmp_get($device, "ibSerialNumber.0", "-OQv", "IB-PLATFORMONE-MIB"));
$version  = trim(snmp_get($device, "ibNiosVersion.0", "-OQv", "IB-PLATFORMONE-MIB"));
$hardware = trim(snmp_get($device, "ibHardwareType.0", "-OQv", "IB-PLATFORMONE-MIB"));

##############
# Create ddns update rrd
##############
$mibs = 'IB-DNSONE-MIB';
$oids =
    'ibDDNSUpdateSuccess.0 ' .
    'ibDDNSUpdateFailure.0 ' .
    'ibDDNSUpdatePrerequisiteReject.0 ' .
    'ibDDNSUpdateReject.0';

$data = snmp_get_multi($device, $oids, '-OQUs', $mibs);

$rrd_def = array(
    'DS:success:DERIVE:600:0:U',
    'DS:failure:DERIVE:600:0:U',
    'DS:reject:DERIVE:600:0:U',
    'DS:prereq_reject:DERIVE:600:0:U'
);

$fields = array(
    'success'       => $data[0]['ibDDNSUpdateSuccess'],
    'failure'       => $data[0]['ibDDNSUpdateFailure'],
    'reject'        => $data[0]['ibDDNSUpdateReject'],
    'prereq_reject' => $data[0]['ibDDNSUpdatePrerequisiteReject'],
);


$tags = compact('rrd_def');
data_update($device, 'ib_dns_dyn_updates', $tags, $fields);
$graphs['ib_dns_dyn_updates'] = true;


##################
# Create dns performance graph (latency)
##################
$mibs = 'IB-PLATFORMONE-MIB';
$oids =
    'ibNetworkMonitorDNSNonAAT1AvgLatency.0 ' .
    'ibNetworkMonitorDNSAAT1AvgLatency.0';

$data = snmp_get_multi($device, $oids, '-OQUs', $mibs);

$rrd_def = array(
        'DS:PerfAA:GAUGE:600:0:U',
        'DS:PerfnonAA:GAUGE:600:0:U'
);

$fields = array(
    'PerfAA'    => $data[0]['ibNetworkMonitorDNSAAT1AvgLatency'],
    'PerfnonAA' => $data[0]['ibNetworkMonitorDNSNonAAT1AvgLatency'],
);

$tags = compact('rrd_def');
data_update($device, 'ib_dns_performance', $tags, $fields);
$graphs['ib_dns_performance'] = true;

##################
# Create dns request return code graph
##################
$mibs = 'IB-DNSONE-MIB';
$oids =
    'ibBindZoneFailure.\"summary\" ' .
    'ibBindZoneNxDomain.\"summary\" ' .
    'ibBindZoneNxRRset.\"summary\" ' .
    'ibBindZoneSuccess.\"summary\"';

$data = snmp_get_multi($device, $oids, '-OQUs', $mibs);

$rrd_def = array(
    'DS:success:DERIVE:600:0:U',
    'DS:failure:DERIVE:600:0:U',
    'DS:nxdomain:DERIVE:600:0:U',
    'DS:nxrrset:DERIVE:600:0:U'
);

$fields = array(
    'success'       => $data['"summary"']['ibBindZoneSuccess'],
    'failure'       => $data['"summary"']['ibBindZoneFailure'],
    'nxdomain'      => $data['"summary"']['ibBindZoneNxDomain'],
    'nxrrset'       => $data['"summary"']['ibBindZoneNxRRset'],
);

$tags = compact('rrd_def');
data_update($device, 'ib_dns_request_return_codes', $tags, $fields);
$graphs['ib_dns_request_return_codes'] = true;


##################
# Create dhcp messages graph
##################
$mibs = 'IB-DHCPONE-MIB';
$oids =
    'ibDhcpTotalNoOfAcks.0 ' .
    'ibDhcpTotalNoOfDeclines.0 ' .
    'ibDhcpTotalNoOfDiscovers.0 ' .
    'ibDhcpTotalNoOfInforms.0 ' .
    'ibDhcpTotalNoOfNacks.0 ' .
    'ibDhcpTotalNoOfOffers.0 ' .
    'ibDhcpTotalNoOfOthers.0 ' .
    'ibDhcpTotalNoOfReleases.0 ' .
    'ibDhcpTotalNoOfRequests.0';

$data = snmp_get_multi($device, $oids, '-OQUs', $mibs);

$rrd_def = array(
    'DS:ack:DERIVE:600:0:U',
    'DS:decline:DERIVE:600:0:U',
    'DS:discover:DERIVE:600:0:U',
    'DS:inform:DERIVE:600:0:U',
    'DS:nack:DERIVE:600:0:U',
    'DS:offer:DERIVE:600:0:U',
    'DS:other:DERIVE:600:0:U',
    'DS:release:DERIVE:600:0:U',
    'DS:request:DERIVE:600:0:U'
);

$fields = array(
    'ack'      => $data[0]['ibDhcpTotalNoOfAcks'],
    'decline'  => $data[0]['ibDhcpTotalNoOfDeclines'],
    'discover' => $data[0]['ibDhcpTotalNoOfDiscovers'],
    'inform'   => $data[0]['ibDhcpTotalNoOfInforms'],
    'nack'     => $data[0]['ibDhcpTotalNoOfNacks'],
    'offer'    => $data[0]['ibDhcpTotalNoOfOffers'],
    'other'    => $data[0]['ibDhcpTotalNoOfOthers'],
    'release'  => $data[0]['ibDhcpTotalNoOfReleases'],
    'request'  => $data[0]['ibDhcpTotalNoOfRequests'],
);

$tags = compact('rrd_def');
data_update($device, 'ib_dhcp_messages', $tags, $fields);
$graphs['ib_dhcp_messages'] = true;
