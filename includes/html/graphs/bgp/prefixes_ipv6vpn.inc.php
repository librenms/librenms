<?php

$rrd_filename = Rrd::name($device['hostname'], ['cbgp', $data['bgpPeerIdentifier'] . '.ipv6.vpn.']);

require 'includes/html/graphs/bgp/prefixes.inc.php';
