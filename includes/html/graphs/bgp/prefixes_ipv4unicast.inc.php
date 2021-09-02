<?php

$rrd_filename = Rrd::name($device['hostname'], ['cbgp', $data['bgpPeerIdentifier'] . '.ipv4.unicast']);

require 'includes/html/graphs/bgp/prefixes.inc.php';
