<?php

// Parser should populate $port_ifAlias array with type, descr, circuit, speed and notes
unset($port_ifAlias);

echo $this_port['ifAlias'];

$split = preg_split('/[:\[\]{}()]/', $this_port['ifAlias']);
$type = $split[0] ?? null;
$descr = trim($split[1] ?? null);
$circuit = preg_split('/[{}]/', $this_port['ifAlias'])[1] ?? null;
$notes = preg_split('/[()]/', $this_port['ifAlias'])[1] ?? null;
$speed = preg_split('/[\[\]]/', $this_port['ifAlias'])[1] ?? null;

if ($type && $descr) {
    $type = strtolower($type);
    $port_ifAlias['type'] = $type;
    $port_ifAlias['descr'] = $descr;
    $port_ifAlias['circuit'] = $circuit;
    $port_ifAlias['speed'] = substr($speed, 0, 32);
    $port_ifAlias['notes'] = $notes;

    d_echo($port_ifAlias);
}

unset($port_type, $port_descr, $port_circuit, $port_notes, $port_speed, $split);
