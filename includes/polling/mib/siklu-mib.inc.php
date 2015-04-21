<?php

echo(" Siklu Wireless ");

$mib_oids = array(
	   'rfAverageRssi'              => array('1', 'rfAverageRssi', 'Signal Strength', 'GAUGE'),
	   'rfAverageCinr'              => array('1', 'rfAverageCinr', 'Signal to noise ratio', 'GAUGE'),
	   'rfModulationType'           => array('1', 'rfModulationType', 'Modulation Type', 'GAUGE'),
	);
    
$mib_graphs = array(); 
  
array_push($mib_graphs, 'siklu_rfAverageRssi', 'siklu_rfAverageCinr', 'siklu_rfModulationType');

unset($graph, $oids, $oid);

poll_mib_def($device, 'RADIO-BRIDGE-MIB', 'siklu', $mib_oids, $mib_graphs, $sgraphs);
