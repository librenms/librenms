<?php

$rrd_filename = Rrd::name($device['hostname'], ['cbgp', $data['bgpPeerIdentifier'] . '.ipv4.vpn']);

require 'includes/html/graphs/bgp/prefixes.inc.php';
