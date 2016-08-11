<?php

$rrd_filename = rrd_name($device['hostname'], array('cbgp', $data['bgpPeerIdentifier'].'.ipv4.unicast'));

require 'includes/graphs/bgp/prefixes.inc.php';
