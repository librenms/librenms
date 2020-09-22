<?php

$rrd_filename = rrd_name($device['hostname'], ['cbgp', $data['bgpPeerIdentifier'] . '.ipv4.multicast']);

require 'includes/html/graphs/bgp/prefixes.inc.php';
