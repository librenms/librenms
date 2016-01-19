<?php

$serial   = trim(snmp_get($device, "ibSerialNumber.0", "-OQv", "IB-PLATFORMONE-MIB"));
$version  = trim(snmp_get($device, "ibNiosVersion.0", "-OQv", "IB-PLATFORMONE-MIB"));
$hardware = trim(snmp_get($device, "ibHardwareType.0", "-OQv", "IB-PLATFORMONE-MIB"));

##############
# Create ddns update rrd
##############
$rrdfile  = $config['rrd_dir'].'/'.$device['hostname'].'/ib_dns_dyn_updates.rrd';

$mibs = '+IB-DNSONE-MIB';
$oids = 
    'IB-DNSONE-MIB::ibDDNSUpdateSuccess.0 ' .
    'IB-DNSONE-MIB::ibDDNSUpdateFailure.0 ' .
    'IB-DNSONE-MIB::ibDDNSUpdatePrerequisiteReject.0 ' .
    'IB-DNSONE-MIB::ibDDNSUpdateReject.0';

$data = snmp_get_multi($device, $oids, '-OQUs', $mibs);

$ds1 = $data[0]['ibDDNSUpdateSuccess'];
$ds2 = $data[0]['ibDDNSUpdateFailure'];
$ds3 = $data[0]['ibDDNSUpdateReject'];
$ds4 = $data[0]['ibDDNSUpdatePrerequisiteReject'];

if (!is_file($rrdfile)) {
    rrdtool_create(
        $rrdfile, 
        'DS:success:DERIVE:600:0:U
        DS:failure:DERIVE:600:0:U
        DS:reject:DERIVE:600:0:U
        DS:prereq_reject:DERIVE:600:0:U '.$config['rrd_rra']);
}

$fields = array(
    'success'       => $ds1,
    'failure'       => $ds2,
    'reject'        => $ds3,
    'prereq_reject' => $ds4,
);


rrdtool_update($rrdfile, $fields);
$graphs['ib_dns_dyn_updates'] = true;


##################
# Create dns performance graph (latency)
##################
$rrdfile = $config['rrd_dir'].'/'.$device['hostname'].'/ib_dns_performance.rrd';

$mibs = '+IB-PLATFORMONE-MIB';
$oids =
    'IB-PLATFORMONE-MIB::ibNetworkMonitorDNSNonAAT1AvgLatency.0 ' .
    'IB-PLATFORMONE-MIB::ibNetworkMonitorDNSAAT1AvgLatency.0';

$data = snmp_get_multi($device, $oids, '-OQUs', $mibs);

$ds1   = $data[0]['ibNetworkMonitorDNSAAT1AvgLatency'];
$ds2   = $data[0]['ibNetworkMonitorDNSNonAAT1AvgLatency'];

if (!is_file($rrdfile)) {
    rrdtool_create(
        $rrdfile, 
        'DS:PerfAA:GAUGE:600:0:U
        DS:PerfnonAA:GAUGE:600:0:U '.$config['rrd_rra']);
}

$fields = array(
    'PerfAA'    => $ds1,
    'PerfnonAA' => $ds2,
);

rrdtool_update($rrdfile, $fields);
$graphs['ib_dns_performance'] = true;

##################
# Create dns request return code graph
##################
$rrdfile = $config['rrd_dir'].'/'.$device['hostname'].'/ib_dns_request_return_codes.rrd';

$mibs = '+IB-DNSONE-MIB';
$oids = 
    'IB-DNSONE-MIB::ibBindZoneFailure.\"summary\" ' .
    'IB-DNSONE-MIB::ibBindZoneNxDomain.\"summary\" ' .
    'IB-DNSONE-MIB::ibBindZoneNxRRset.\"summary\" ' . 
    'IB-DNSONE-MIB::ibBindZoneSuccess.\"summary\"';

$data = snmp_get_multi($device, $oids, '-OQUs', $mibs);

$ds1 = $data['"summary"']['ibBindZoneSuccess'];
$ds2 = $data['"summary"']['ibBindZoneFailure'];
$ds3 = $data['"summary"']['ibBindZoneNxDomain'];
$ds4 = $data['"summary"']['ibBindZoneNxRRset'];


if (!is_file($rrdfile)) {
    rrdtool_create(
        $rrdfile, 
        'DS:success:DERIVE:600:0:U
        DS:failure:DERIVE:600:0:U
        DS:nxdomain:DERIVE:600:0:U
        DS:nxrrset:DERIVE:600:0:U '.$config['rrd_rra']);
}

$fields = array(
    'success'       => $ds1,
    'failure'       => $ds2,
    'nxdomain'      => $ds3,
    'nxrrset'       => $ds4,
);

rrdtool_update($rrdfile, $fields);
$graphs['ib_dns_request_return_codes'] = true;


##################
# Create dhcp messages graph
##################
$rrdfile  = $config['rrd_dir'].'/'.$device['hostname'].'/ib_dhcp_messages.rrd';

$mibs = '+IB-DHCPONE-MIB';
$oids = 
    'IB-DHCPONE-MIB::ibDhcpTotalNoOfAcks.0 ' . 
    'IB-DHCPONE-MIB::ibDhcpTotalNoOfDeclines.0 ' .
    'IB-DHCPONE-MIB::ibDhcpTotalNoOfDiscovers.0 ' .
    'IB-DHCPONE-MIB::ibDhcpTotalNoOfInforms.0 ' .
    'IB-DHCPONE-MIB::ibDhcpTotalNoOfNacks.0 ' .
    'IB-DHCPONE-MIB::ibDhcpTotalNoOfOffers.0 ' .
    'IB-DHCPONE-MIB::ibDhcpTotalNoOfOthers.0 ' .
    'IB-DHCPONE-MIB::ibDhcpTotalNoOfReleases.0 ' .
    'IB-DHCPONE-MIB::ibDhcpTotalNoOfRequests.0';

$data = snmp_get_multi($device, $oids, '-OQUs', $mibs);

$ds1 = $data[0]['ibDhcpTotalNoOfAcks'];
$ds2 = $data[0]['ibDhcpTotalNoOfDeclines'];
$ds3 = $data[0]['ibDhcpTotalNoOfDiscovers'];
$ds4 = $data[0]['ibDhcpTotalNoOfInforms'];
$ds5 = $data[0]['ibDhcpTotalNoOfNacks'];
$ds6 = $data[0]['ibDhcpTotalNoOfOffers'];
$ds7 = $data[0]['ibDhcpTotalNoOfOthers'];
$ds8 = $data[0]['ibDhcpTotalNoOfReleases'];
$ds9 = $data[0]['ibDhcpTotalNoOfRequests'];

if (!is_file($rrdfile)) {
    rrdtool_create(
        $rrdfile, 
        'DS:ack:DERIVE:600:0:U 
        DS:decline:DERIVE:600:0:U 
        DS:discover:DERIVE:600:0:U 
        DS:inform:DERIVE:600:0:U 
        DS:nack:DERIVE:600:0:U 
        DS:offer:DERIVE:600:0:U 
        DS:other:DERIVE:600:0:U 
        DS:release:DERIVE:600:0:U 
        DS:request:DERIVE:600:0:U '.$config['rrd_rra']);
}

$fields = array(
    'ack'      => $ds1,
    'decline'  => $ds2,
    'discover' => $ds3,
    'inform'   => $ds4,
    'nack'     => $ds5,
    'offer'    => $ds6,
    'other'    => $ds7,
    'release'  => $ds8,
    'request'  => $ds9,
);

rrdtool_update($rrdfile, $fields);
$graphs['ib_dhcp_messages'] = true;

