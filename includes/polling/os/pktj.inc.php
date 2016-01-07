<?php

echo ' Packet Journey ';


$pktj_graphs = array(
	'pktjTable'	=> 'GANDI-MIB',

);

poll_mibs($pktj_graphs, $device, $graphs);
